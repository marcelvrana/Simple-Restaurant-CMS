<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\SeosettingsForm;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;

class SeosettingsPresenter extends BannerPresenter
{

    #[Inject]
    public SeosettingsForm $seosettingsForm;


    public function actionDefault(){
        $this->id = null;
        $this->template->items = $this->seosettingsRepository->findAll();
    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit($id){
        $item = $this->seosettingsRepository->findById($id);
        if (!$item) {
            $this->flashMessage('Nie je možné upravovať neexistujúci záznam', 'alert-danger');
            $this->redirect('default');
        }
        $this['seosettingsForm']->setDefaults($item);
        foreach ($item->related('seosettingsdictionary') as $dictionary) {
            $this['seosettingsForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }

        $this->template->item = $item;

    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentSeosettingsForm(): Form
    {
        $this->seosettingsForm->id = $this->id;
        return $this->seosettingsForm->create();
    }
}