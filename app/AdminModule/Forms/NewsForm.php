<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Filter\Filter;
use App\Model\Admin\LanguageManager;
use App\Model\Repository\GalleryRepository;
use App\Model\Repository\NewsgalleryRepository;
use App\Model\Repository\NewsRepository;
use App\Model\Service\ImageService;
use Composer\Package\Package;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class NewsForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;


    /**
     * NavigationForm constructor.
     * @param NewsRepository $newsRepository
     * @param GalleryRepository $galleryRepository
     * @param NewsgalleryRepository $newsgalleryRepository
     * @param LanguageManager $languageManager
     * @param ImageService $imageService
     * @param Request $request
     */
    public function __construct(
        private NewsRepository $newsRepository,
        private GalleryRepository $galleryRepository,
        private NewsgalleryRepository $newsgalleryRepository,
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
        $form->addGroup('Translation');
        $dictionaries = $form->addContainer('dictionaries');
        foreach ($languages as $lang){
            $dictionary = $dictionaries->addContainer($lang->id);

            $dictionary->addText('name', 'Name -  ' . $lang->name )
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(FALSE);

            $dictionary->addText('title', 'Title - ' . $lang->name)
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(false);
            $dictionary->addTextArea('perex', 'Short perex text - '  . $lang->name)
                ->setRequired(false);

            $dictionary->addTextArea('content', 'Content - '  . $lang->name)
                ->setHtmlAttribute('class', 'wysiwyg');





            $dictionary->addHidden('language_id', $lang->id);

            if ($this->id) {
                $dictionary->addHidden('id', NULL);
                $dictionary->addHidden('news_id', $this->id);
            }
        }
        $form->addGroup('Additional info');

        $form->addUpload('img_popup', 'Popup image')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Only image JPG, JPEG, PNG or GIF.')
            ->setOption('description', 'The best is portrait photo. The size for the reduction is 400x500');


        if ($this->id) {
            $img_popup = $this->newsRepository->findBy(['id' => $this->id])->limit(1)->fetch();
            if ($img_popup && $img_popup->img_popup) {
                $form->addSubmit('remove_img_popup', 'Remove popup image')
                    ->setHtmlAttribute('class', 'btn btn-sm btn-danger')
                    ->setValidationScope([])
                    ->setOmitted()
                    ->setOption('description', Html::el('img', [
                        'src' => $this->request->getUrl()->getBasePath() . $img_popup->img_popup,
                        'class' => 'img-fluid d-block mt-2 w-25'
                    ]));
            }
        }

        $form->addUpload('img_head', 'Main image')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Only image JPG, JPEG, PNG or GIF.')
            ->setOption('description', 'Best minimal width is 1300px ');

        if ($this->id) {
            $img_head = $this->newsRepository->findBy(['id' => $this->id])->limit(1)->fetch();
            if ($img_head && $img_head->img_head) {
                $form->addSubmit('remove_img_head', 'Remove main image')
                    ->setHtmlAttribute('class', 'btn btn-sm btn-danger')
                    ->setValidationScope([])
                    ->setOmitted()
                    ->setOption('description', Html::el('img', [
                        'src' => $this->request->getUrl()->getBasePath() . $img_head->img_head,
                        'class' => 'img-fluid d-block mt-2 w-25'
                    ]));
            }
        }



        $form->addCheckbox('popup', 'Show as popup window?');
        $form->addCheckbox('is_top', 'Is topped new?');

        $form->addText('showfrom', 'Show popup window from')
            ->setHtmlAttribute('class', 'datepicker');

        $form->addText('showto', 'Show popup window to')
            ->setHtmlAttribute('class', 'datepicker');

        $form->addMultiSelect('newsgallery', 'Set gallery for this new', $galleries)
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
        if(isset($values->newsgallery) && $values->newsgallery){
            $newsgallery = $values->newsgallery;
        }

        if (isset($values->img_popup) && $values->img_popup->isOk()) {
            $img_popup = $values->img_popup;
        }

        if (isset($values->img_head) && $values->img_head->isOk()){
            $img_head = $values->img_head;
        }
        unset($values->img_head);
        unset($values->img_popup);
        unset($values->newsgallery);

        if ($this->id) {
            $row = $this->newsRepository->findById($this->id);
            if ($form->isSubmitted()->getName() == 'remove_img_popup') {
                if ($row->img_popup && is_file(WWW_DIR . '/' . $row->img_popup)) {
                    FileSystem::delete(WWW_DIR . '/' . $row->img_popup);
                }
                $row->update(['img_popup' => null]);
                $form->getPresenter()->flashMessage('Deleted!', 'alert-success');
                $form->getPresenter()->redirect('edit', $this->id);
            }

            if ($form->isSubmitted()->getName() == 'remove_img_head') {
                if ($row->img_head && is_file(WWW_DIR . '/' . $row->img_head)) {
                    FileSystem::delete(WWW_DIR . '/' . $row->img_head);
                }
                $row->update(['img_head' => null]);
                $form->getPresenter()->flashMessage('Deleted!', 'alert-success');
                $form->getPresenter()->redirect('edit', $this->id);
            }

            $this->newsRepository->edit($this->id, $values);


        } else {
            $row = $this->newsRepository->create($values);
        }

        if(isset($newsgallery) && $newsgallery){
            $this->newsgalleryRepository->findBy(['news_id' => $row->id])->delete();
            foreach($newsgallery as $ng){
                $this->newsgalleryRepository->add(['news_id' => $row->id, 'gallery_id' => $ng]);
            }
        }

        if (isset($img_popup) && $img_popup) {
            $img_popup_url = $this->imageService->saveImage(
                $img_popup,
                [
                    'publicPath' => 'data/news/' . $row->id ,
                    'height' => 500,
                ]
            );

            $row->update(['img_popup' => $img_popup_url]);
        }

        if (isset($img_head) && $img_head) {
            $img_head_url = $this->imageService->saveImage(
                $img_head,
                [
                    'publicPath' => 'data/news/' . $row->id ,
                    'width' => 1300,
                ]
            );

            $row->update(['img_head' => $img_head_url]);
        }

        $form->getPresenter()->flashMessage('Saved', 'alert-success');
        $form->getPresenter()->redirect('News:edit', $row->id);

    }

}
