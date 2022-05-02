<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Model\Admin\LanguageManager;
use App\Model\Repository\AlacartecategoryRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class AlacarteCategoryForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;


    /**
     * NavigationForm constructor.
     * @param AlacartecategoryRepository $alacartecategoryRepository
     * @param LanguageManager $languageManager
     * @param ImageService $imageService
     * @param Request $request
     */
    public function __construct(
        private AlacartecategoryRepository $alacartecategoryRepository,
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
        foreach ($languages as $lang) {
            $dictionary = $dictionaries->addContainer($lang->id);

            $dictionary->addText('name', 'Name ' . $lang->name)
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(false);

            $dictionary->addText('description', 'Category description - ' . $lang->name)
                ->setRequired(false);


            $dictionary->addHidden('language_id', $lang->id);

            if ($this->id) {
                $dictionary->addHidden('id', null);
                $dictionary->addHidden('alacartecategory_id', $this->id);
            }
        }

        $form->addUpload('img_main', 'Image for category')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Only image JPG, JPEG, PNG or GIF.');

        if ($this->id) {
            $img_main = $this->alacartecategoryRepository->findById($this->id);
            if ($img_main && $img_main->img_main) {
                $form->addSubmit('remove_img_main', 'Delete image')
                    ->setHtmlAttribute('class', 'btn btn-sm btn-danger')
                    ->setValidationScope([])
                    ->setOmitted()
                    ->setOption(
                        'description',
                        Html::el(
                            'img',
                            [
                                'src' => $this->request->getUrl()->getBasePath() . $img_main->img_main,
                                'class' => 'img-fluid d-block mt-2 w-25'
                            ]
                        )
                    );
            }
        }

        $form->addSelect('image_position', 'Position of image', ['1' => 'Na ľavo', '2' => 'Na pravo'])
            ->setRequired(false);

        $form->addCheckbox('is_active', 'Is active?');
        $form->addCheckbox('is_half', 'Cut in half of container');

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
        if (isset($values->img_main) && $values->img_main->isOk()) {
            $img_main = $values->img_main;
        }
        unset($values->img_main);
        if ($this->id) {
            $row = $this->alacartecategoryRepository->findById($this->id);
            if ($form->isSubmitted()->getName() == 'remove_img_main') {
                if ($row->img_main && is_file(WWW_DIR . '/' . $row->img_main)) {
                    FileSystem::delete(WWW_DIR . '/' . $row->img_main);
                }
                $row->update(['img_main' => null]);
                $form->getPresenter()->flashMessage('Delted!', 'alert-success');
                $form->getPresenter()->redirect('edit', $this->id);
            }

            $this->alacartecategoryRepository->edit($this->id, $values);
        } else {
            $last = $this->alacartecategoryRepository->findAll()->order('ordered DESC')->limit(1)->fetch();
            $values['ordered'] = $last ? $last->ordered + 1 : 1;
            $row = $this->alacartecategoryRepository->create($values);
        }

        if (isset($img_main) && $img_main) {
            $img_main_url = $this->imageService->saveImage(
                $img_main,
                [
                    'publicPath' => 'data/alacartecategory/' . $row->id,
                    'width' => 1330,
                    'height' => 951,

                ]
            );

            $row->update(['img_main' => $img_main_url]);
        }

        $form->getPresenter()->flashMessage('Saved!', 'alert-success');
        $form->getPresenter()->redirect('default');
    }


}
