<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\AllergenForm;
use App\Model\Admin\LanguageManager;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;

class AllergenPresenter extends BasePresenter
{
    #[Inject]
    public AllergenForm $allergenForm;

    #[Inject]
    public LanguageManager $languageManager;

    public function actionDefault()
    {
        $this->id = null;
        $this->template->items = $this->allergenRepository->findAll()->order('ordered');
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
        $item = $this->allergenRepository->findById($id);
        if (!$item) {
            $this->flashMessage('Nie je možné upravovať neexistujúci záznam', 'alert-danger');
            $this->redirect('default');
        }
        $this['allergenForm']->setDefaults($item);
        foreach ($item->related('allergendictionary') as $dictionary) {
            $this['allergenForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }
        $this->template->item = $item;
    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id)
    {
        $item = $this->allergenRepository->findById($id);
        if(!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('this');
        }
        $item->delete();
        $this->flashMessage('Úspešne vymazané', 'alert-success');
        $this->isAjax() ? $this->redrawControl('tablesnippet') : $this->redirect('this');
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentAllergenForm(): Form
    {
        $this->allergenForm->id = $this->id;
        return $this->allergenForm->create();
    }
}