<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Filter\Filter;
use App\Model\Admin\LanguageManager;
use App\Model\Repository\SeosettingsRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class SeosettingsForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;


    /**
     * NavigationForm constructor.
     * @param SeosettingsRepository $seosettingsRepository
     * @param LanguageManager $languageManager
     * @param ImageService $imageService
     * @param Request $request
     */
    public function __construct(
        private SeosettingsRepository $seosettingsRepository,
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

        $form = new Form();
        $form->addProtection('Form protection error, try again');
        $form->addGroup('Translations');
        $dictionaries = $form->addContainer('dictionaries');
        foreach ($languages as $lang){
            $dictionary = $dictionaries->addContainer($lang->id);


            $dictionary->addText('title', 'Title - ' . $lang->name)
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(false);
            $dictionary->addTextArea('description', 'Short description - '  . $lang->name)
                ->setRequired(false);

            $dictionary->addHidden('language_id', $lang->id);

            if ($this->id) {
                $dictionary->addHidden('id', NULL);
                $dictionary->addHidden('seosettings_id', $this->id);
            }
        }
        $form->addGroup('Additional information');

        $form->addUpload('ogpimage', 'OGP image')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Only image JPG, JPEG, PNG or GIF.')
            ->setOption('description', '1200 x 630 size photo!');


        if ($this->id) {
            $ogpimage = $this->seosettingsRepository->findBy(['id' => $this->id])->limit(1)->fetch();
            if ($ogpimage && $ogpimage->ogpimage) {
                $form->addSubmit('remove_ogpimage', 'Remove image')
                    ->setHtmlAttribute('class', 'btn btn-sm btn-danger')
                    ->setValidationScope([])
                    ->setOmitted()
                    ->setOption('description', Html::el('img', [
                        'src' => $this->request->getUrl()->getBasePath() . $ogpimage->ogpimage,
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

\Tracy\Debugger::barDump($values);
        if (isset($values->ogpimage) && $values->ogpimage->isOk()) {
            $ogpimage = $values->ogpimage;
        }


        unset($values->ogpimage);

        if ($this->id) {
            $row = $this->seosettingsRepository->findById($this->id);
            if ($form->isSubmitted()->getName() == 'remove_ogpimage') {
                if ($row->img_popup && is_file(WWW_DIR . '/' . $row->ogpimage)) {
                    FileSystem::delete(WWW_DIR . '/' . $row->ogpimage);
                }
                $row->update(['ogpimage' => null]);
                $form->getPresenter()->flashMessage('Deleted!', 'alert-success');
                $form->getPresenter()->redirect('edit', $this->id);
            }

            $this->seosettingsRepository->edit($this->id, $values);


        } else {
            $row = $this->seosettingsRepository->create($values);
        }


        if (isset($ogpimage) && $ogpimage) {
            $ogpimage_url = $this->imageService->saveImage(
                $ogpimage,
                [
                    'publicPath' => 'data/seo/' . $row->id ,
                ]
            );

            $row->update(['ogpimage' => $ogpimage_url]);
        }


        $form->getPresenter()->flashMessage('Saved', 'alert-success');
        $form->getPresenter()->redirect('Seosettings:edit', $row->id);

    }

}
