<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;


use App\AdminModule\Forms\SpaceOfferForm;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;

class SpaceOfferPresenter extends BasePresenter
{

    #[Inject]
    public SpaceOfferForm $spaceOfferForm;


    /**
     *
     */
    public function renderDefault(){
        $this->id = null;
        $this->template->items = $this->spaceOfferRepository->findAll();
    }

    /**
     *
     */
    public function renderAdd(){
        $this->id = null;
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function renderEdit($id){
        $item = $this->spaceOfferRepository->findById($id);
        if (!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('News:default');
        }
        $this->spaceOfferForm->id = $id;
        $this['spaceOfferForm']->setDefaults($item);

        $spaceOfferGallery = $this->spaceOfferGalleryRepository->findBy(['spaceoffer_id' => $id])->fetchPairs(null, 'gallery_id');
        if($spaceOfferGallery) $this['spaceOfferForm']['spaceoffergallery']->setDefaultValue($spaceOfferGallery);
        foreach ($item->related('spaceofferdictionary') as $dictionary) {
            $this['spaceOfferForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }

        $this->template->item = $item;

    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id){
        $item = $this->spaceOfferRepository->findById($id);
        if(!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('this');
        }
        $item->delete();
        $this->flashMessage('Úspešne vymazané', 'alert-success');
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');

    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentSpaceOfferForm(): Form
    {
        $this->spaceOfferForm->id = $this->id;
        return $this->spaceOfferForm->create();
    }
}
