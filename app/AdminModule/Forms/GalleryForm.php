<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Constants\Constants;
use App\Model\Admin\LanguageManager;
use App\Model\Repository\GalleryRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class GalleryForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;

    /**
     * NavigationForm constructor.
     * @param GalleryRepository $galleryRepository
     * @param LanguageManager $languageManager
     */
    public function __construct(
        private GalleryRepository $galleryRepository,
        private ImageService $imageService,
        private LanguageManager $languageManager,
        private Request $request
    ) {
    }


    /**
     * @return Form
     */
    public function create(): Form
    {
        $languages = $this->languageManager->getActiveLanguages();
        $form = new Form();
        $form->addProtection('Form protection error, try again');
        $form->addGroup('Translations');
        $dictionaries = $form->addContainer('dictionaries');
        foreach ($languages as $lang){
            $dictionary = $dictionaries->addContainer($lang->id);

            $dictionary->addText('name', 'Name ' . $lang->name )
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(FALSE);

            $dictionary->addText('title', 'Title - ' . $lang->name)
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(false);
            $dictionary->addTextArea('description', 'Short perex text - '  . $lang->name)
                ->setRequired(false);

            if ($this->id) {

                $dictionary->addHidden('id', NULL);
                $dictionary->addHidden('language_id', $lang->id);
                $dictionary->addHidden('gallery_id', $this->id);
            }
        }
        $form->addSelect('gallerytype', 'Gallery type', ['photo' => 'Photo gallery', 'video' => 'Youtube video']);

        $form->addUpload('image', 'Preview image')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Only image JPG, JPEG, PNG or GIF.')
            ->setOption('description', 'Image was cropped to 1:1');


        if ($this->id) {
            $image = $this->galleryRepository->findBy(['id' => $this->id])->limit(1)->fetch();

            if ($image && $image->image) {
                $form->addSubmit('remove_image', 'Delete image')
                    ->setHtmlAttribute('class', 'btn btn-sm btn-danger')
                    ->setValidationScope([])
                    ->setOmitted()
                    ->setOption('description', Html::el('img', [
                        'src' => $this->request->getUrl()->getBasePath() . $image->image,
                        'class' => 'img-fluid d-block mt-2 w-25'
                    ]));
            }
        }

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
        if (isset($values->image) && $values->image->isOk()) {
            $image = $values->image;
        }
        unset($values->image);


        if ($this->id) {
            $row = $this->galleryRepository->findById($this->id);
            if ($form->isSubmitted()->getName() == 'remove_image') {
                if ($row->image && is_file(WWW_DIR . '/' . $row->image)) {
                    FileSystem::delete(WWW_DIR . '/' . $row->image);
                }
                $row->update(['image' => null]);
                $form->getPresenter()->flashMessage('Deleted!', 'alert-success');
                $form->getPresenter()->redirect('edit', $this->id);
            }
            $this->galleryRepository->edit($this->id, $values);
        } else {
            $row = $this->galleryRepository->create($values);
        }

        if (isset($image) && $image) {
            $image_url = $this->imageService->saveImage($image, [
                'publicPath' => Constants::IMAGE_UPLOAD_GALLERY_PATH . $row->id,
                'width' => Constants::IMAGE_UPLOAD_GALLERY_THUMB_WIDTH,
                'height' => Constants::IMAGE_UPLOAD_GALLERY_THUMB_HEIGHT,
                'method' => 'exact'
            ]);

            $row->update(['image' => $image_url]);
        }
        $form->getPresenter()->flashMessage('Saved', 'alert-success');
        $form->getPresenter()->redirect('Gallery:edit', $row->id);
    }

}
