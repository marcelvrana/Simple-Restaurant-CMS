<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\LanguageForm;
use App\Model\Admin\LanguageManager;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;

class LanguagePresenter extends BasePresenter
{
    #[Inject]
    public LanguageForm $languageForm;

    #[Inject]
    public LanguageManager $languageManager;

    public function actionDefault()
    {
        $this->id = null;
        $this->template->items = $this->languageRepository->findAll()->order('ordered');
    }

    /**
     *
     */
    public function actionAdd()
    {
        $this->id = null;
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit($id): void
    {
        $item = $this->languageRepository->findById($id);
        if (!$item) {
            $this->flashMessage('Nie je možné upravovať neexistujúci záznam', 'alert-danger');
            $this->redirect('Language:default');
        }
        $this['languageForm']->setDefaults($item);
        $this->template->item = $item;
    }



    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleSetActive($id){
        $result = $this->languageManager->setActive($id);
        $this->flashMessage($result['message'], $result['type']);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleSetDefault($id){
        $result = $this->languageManager->setDefault($id);
        $this->flashMessage($result['message'], $result['type']);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id)
    {
        $result = $this->languageManager->safeDeleteItem($id);
        $this->flashMessage($result['message'], $result['type']);

        $this->isAjax() ? $this->redrawControl() : $this->redirect('Language:default');
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentLanguageForm(): Form
    {
        $this->languageForm->id = $this->id;
        return $this->languageForm->create();
    }
}