<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\TranslationForm;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;

class TranslationPresenter extends BasePresenter
{

    #[Inject]
    public TranslationForm $translationForm;


    public function actionDefault(){
        $this->id = null;
        $this->template->languages = $this->languageRepository->findBy(['is_active' => 1]);
    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit($id){
        $item = $this->languageRepository->findById($id);

        if (!$item) {
            $this->flashMessage('Nie je možné upravovať neexistujúci záznam', 'alert-danger');
            $this->redirect('default');
        }

        $this->template->language = $item;
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentTranslationForm(): Form
    {
        $this->translationForm->language_id = $this->id;
        return $this->translationForm->create();
    }
}