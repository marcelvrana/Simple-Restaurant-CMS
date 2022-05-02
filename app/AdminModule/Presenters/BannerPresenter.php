<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\BannerForm;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;

class BannerPresenter extends BasePresenter{

    #[Persistent]
    public int|null $id = null;

    #[Inject]
    public BannerForm $bannerForm;


    public function actionDefault(){
        $this->id = null;
        $this->template->items = $this->bannerRepository->findAll();
    }

    public function actionAdd(){
        $this->id = null;
    }

    public function actionEdit($id){
        $item = $this->bannerRepository->findById($id);
        if (!$item) {
            $this->flashMessage('Item not found', 'alert-danger');
            $this->redirect('Banner:default');
        }
        $this['bannerForm']->setDefaults($item);
        foreach ($item->related('bannerdictionary') as $dictionary) {
            $this['bannerForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }
        $this->template->item = $item;
    }

    public function handleDelete($id){
        $item = $this->bannerRepository->findById($id);
        if(!$item){
            $this->flashMessage('Item not found', 'alert-danger');
            $this->redirect('this');
        }
        $item->delete();
        $this->flashMessage('Deleted!', 'alert-success');
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function createComponentBannerForm(){
        $this->bannerForm->id = $this->id;
        return $this->bannerForm->create();
    }
    
}