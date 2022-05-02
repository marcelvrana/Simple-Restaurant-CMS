<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Filter\Filter;
use App\Model\Admin\LanguageManager;
use App\Model\Repository\GalleryRepository;
use App\Model\Repository\SpaceOfferGalleryRepository;
use App\Model\Repository\SpaceOfferRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class SpaceOfferForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;


    /**
     * SpaceOfferForm constructor.
     * @param SpaceOfferRepository $spaceOfferRepository
     * @param SpaceOfferGalleryRepository $spaceOfferGalleryRepository
     * @param GalleryRepository $galleryRepository
     * @param LanguageManager $languageManager
     * @param ImageService $imageService
     * @param Request $request
     */
    public function __construct(
        private SpaceOfferRepository $spaceOfferRepository,
        private SpaceOfferGalleryRepository $spaceOfferGalleryRepository,
        private GalleryRepository $galleryRepository,
        private LanguageManager $languageManager,
        private ImageService $imageService,
        private Request $request
    ) {
    }


    /**
     * @return Form
     */
    public function create(): Form
    {
        $languages = $this->languageManager->getActiveLanguages();
        $galleries = [];
        if($galls = $this->galleryRepository->findAll()){
            foreach ($galls as $gall){
                $galleries[$gall->id] = Filter::dictionaryData($gall, 'gallery', 'name', 1);
            }
        }
        $form = new Form();
        $form->addProtection('Form protection error, try again');
        $form->addGroup('Translations');
        $dictionaries = $form->addContainer('dictionaries');
        foreach ($languages as $lang){
            $dictionary = $dictionaries->addContainer($lang->id);

            $dictionary->addText('name', 'Name ' . $lang->name )
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(FALSE);

            $dictionary->addTextArea('content', 'Text - '  . $lang->name)
                ->setHtmlAttribute('class', 'wysiwyg');





            $dictionary->addHidden('language_id', $lang->id);

            if ($this->id) {
                $dictionary->addHidden('id', NULL);
                $dictionary->addHidden('spaceoffer_id', $this->id);
            }
        }
        $form->addGroup('Additional info');

        $form->addUpload('img', 'Preview image')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Only image JPG, JPEG, PNG or GIF.')
            ->setOption('description', 'Image will be cropped to 1:1 ( e.g. 800x800 ) ');


        if ($this->id) {
            $img = $this->spaceOfferRepository->findById($this->id);
            if ($img && $img->img) {
                $form->addSubmit('remove_img', 'Delete image')
                    ->setHtmlAttribute('class', 'btn btn-sm btn-danger')
                    ->setValidationScope([])
                    ->setOmitted()
                    ->setOption('description', Html::el('img', [
                        'src' => $this->request->getUrl()->getBasePath() . $img->img,
                        'class' => 'img-fluid d-block mt-2 w-25'
                    ]));
            }
        }

        $form->addMultiSelect('spaceoffergallery', 'Gallery for Space offer', $galleries)
        ->setHtmlAttribute('class','select2');

        if ($this->id) {
            $form->addHidden('id', $this->id);
        }

        $form->addSubmit('submit', 'Save');


        $form->onError[] = [$this, 'errorForm'];

        $form->onSuccess[] = [$this, 'successForm'];

        return $this->setBootstrapRender($form);
    }



    /**
     * @param Form $form
     */
    public function errorForm(Form $form): void
    {
        $form->getPresenter()->redrawControl();
    }

    /**
     * @param $form
     * @param $values
     */
    public function successForm($form, $values): void
    {
        if(isset($values->spaceoffergallery) && $values->spaceoffergallery){
            $spaceoffergallery = $values->spaceoffergallery;
        }

        if (isset($values->img) && $values->img->isOk()) {
            $img = $values->img;
        }
        unset($values->img);

        unset($values->spaceoffergallery);

        if ($this->id) {
            $row = $this->spaceOfferRepository->findById($this->id);

            $this->spaceOfferRepository->edit($this->id, $values);
            if ($form->isSubmitted()->getName() == 'remove_img') {
                if ($row->img && is_file(WWW_DIR . '/' . $row->img)) {
                    FileSystem::delete(WWW_DIR . '/' . $row->img);
                }
                $row->update(['img_popup' => null]);
                $form->getPresenter()->flashMessage('Deleted!', 'alert-success');
                $form->getPresenter()->redirect('edit', $this->id);
            }

        } else {
            $last = $this->spaceOfferRepository->findAll()->limit(1)->order('ordered')->fetch();
            $values->ordered = $last ? $last->ordered + 1 : 1;
            $row = $this->spaceOfferRepository->create($values);
        }

        if(isset($spaceoffergallery) && $spaceoffergallery){
            $this->spaceOfferGalleryRepository->findBy(['spaceoffer_id	' => $row->id])->delete();
            foreach($spaceoffergallery as $sg){
                $this->spaceOfferGalleryRepository->add(['spaceoffer_id' => $row->id, 'gallery_id' => $sg]);
            }
        }

        if (isset($img) && $img) {
            $img_url = $this->imageService->saveImage(
                $img,
                [
                    'publicPath' => 'data/spaceoffer/' . $row->id ,
                    'width' => 800,
                    'height' => 800,
                    'method' => 'exact'
                ]
            );

            $row->update(['img' => $img_url]);
        }



        $form->getPresenter()->flashMessage('Saved', 'alert-success');
        $form->getPresenter()->redirect('SpaceOffer:edit', $row->id);

    }

}
