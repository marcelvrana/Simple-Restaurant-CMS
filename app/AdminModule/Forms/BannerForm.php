<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Filter\Filter;
use App\Model\Admin\LanguageManager;
use App\Model\Repository\BannerRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class BannerForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;


    /**
     * NavigationForm constructor.
     * @param LanguageManager $languageManager
     * @param ImageService $imageService
     * @param Request $request
     */
    public function __construct(
        private BannerRepository $bannerRepository,
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

        $form = new Form();
        $form->addProtection('Form protection error, try again');
        $form->addGroup('Translations');
        $dictionaries = $form->addContainer('dictionaries');
        foreach ($languages as $lang){
            $dictionary = $dictionaries->addContainer($lang->id);

            $dictionary->addText('title', 'Title - ' . $lang->name)
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired('Required!');
            $dictionary->addText('text', 'Text - '  . $lang->name)
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(false);

            $dictionary->addText('link', 'Link - '  . $lang->name)
                ->setRequired(false);

            $dictionary->addText('button_text', 'Link text - '  . $lang->name)
                ->setRequired(false);




            $dictionary->addHidden('language_id', $lang->id);

            if ($this->id) {
                $dictionary->addHidden('id', NULL);
                $dictionary->addHidden('banner_id', $this->id);
            }
        }
        $form->addGroup('Additional info');

        $form->addRadioList('content_position', 'Content position', ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'])
        ->setRequired('Choose option!');

        $form->addUpload('image_secondary', 'Small image, like logo')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Only image JPG, JPEG, PNG or GIF.')
            ->setOption('description', 'Best format is  1:1 ( 400x400 or similar ) ');


        if ($this->id) {
            $image_secondary = $this->bannerRepository->findBy(['id' => $this->id])->limit(1)->fetch();
            if ($image_secondary && $image_secondary->image_secondary) {
                $form->addSubmit('remove_image_secondary', 'Remove small image')
                    ->setHtmlAttribute('class', 'btn btn-sm btn-danger')
                    ->setValidationScope([])
                    ->setOmitted()
                    ->setOption('description', Html::el('img', [
                        'src' => $this->request->getUrl()->getBasePath() . $image_secondary->image_secondary,
                        'class' => 'img-fluid bg-dark d-block mt-2 mb-3 w-25'
                    ]));
            }
        }

        $form->addUpload('image', 'Background image of slide')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Only image JPG, JPEG, PNG or GIF.')
            ->setOption('description', 'Minimal width is 1920px (best is 4K) ');

        if ($this->id) {
            $image = $this->bannerRepository->findBy(['id' => $this->id])->limit(1)->fetch();
            if ($image && $image->image) {
                $form->addSubmit('remove_image', 'Remove background image')
                    ->setHtmlAttribute('class', 'btn btn-sm btn-danger')
                    ->setValidationScope([])
                    ->setOmitted()
                    ->setOption('description', Html::el('img', [
                        'src' => $this->request->getUrl()->getBasePath() . $image->image,
                        'class' => 'img-fluid d-block bg-dark mt-2 mb-3 w-25'
                    ]));
            }
        }

        if ($this->id) {
            $form->addHidden('id', $this->id);
        }

        $form->addGroup();
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
        if (isset($values->image_secondary) && $values->image_secondary->isOk()) {
            $image_secondary = $values->image_secondary;
        }

        if (isset($values->image) && $values->image->isOk()){
            $image = $values->image;
        }
        unset($values->image_secondary);
        unset($values->image);

        if ($this->id) {
            $row = $this->bannerRepository->findById($this->id);
            if ($form->isSubmitted()->getName() == 'remove_image_secondary') {
                if ($row->img_popup && is_file(WWW_DIR . '/' . $row->image_secondary)) {
                    FileSystem::delete(WWW_DIR . '/' . $row->image_secondary);
                }
                $row->update(['image_secondary' => null]);
                $form->getPresenter()->flashMessage('Vymazané!', 'alert-success');
                $form->getPresenter()->redirect('edit', $this->id);
            }

            if ($form->isSubmitted()->getName() == 'remove_image') {
                if ($row->image && is_file(WWW_DIR . '/' . $row->image)) {
                    FileSystem::delete(WWW_DIR . '/' . $row->image);
                }
                $row->update(['image' => null]);
                $form->getPresenter()->flashMessage('Vymazané!', 'alert-success');
                $form->getPresenter()->redirect('edit', $this->id);
            }

            $this->bannerRepository->edit($this->id, $values);

        } else {
            $row = $this->bannerRepository->create($values);
        }

        if (isset($image_secondary) && $image_secondary) {
            $image_secondary_url = $this->imageService->saveImage(
                $image_secondary,
                [
                    'publicPath' => 'data/banner/' . $row->id ,
                    'width' => 400,
                ]
            );

            $row->update(['image_secondary' => $image_secondary_url]);
        }

        if (isset($image) && $image) {
            $image_url = $this->imageService->saveImage(
                $image,
                [
                    'publicPath' => 'data/banner/' . $row->id ,
                    'width' => 1920,
                ]
            );

            $row->update(['image' => $image_url]);
        }

        $form->getPresenter()->flashMessage('Saved', 'alert-success');
        $form->getPresenter()->redirect('Banner:default');

    }

}
