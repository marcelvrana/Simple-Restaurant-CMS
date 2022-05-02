<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\AlacarteCategoryForm;
use App\AdminModule\Forms\AlacarteItemForm;
use App\AdminModule\Forms\AlacarteItemVariantForm;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;

class AlacartePresenter extends BasePresenter
{
    #[Persistent]
    public int|null $id = null;

    #[Persistent]
    public int|null $category_id = null;

    #[Persistent]
    public int|null $alacarteitem_id = null;

    #[Inject]
    public AlacarteCategoryForm $alacarteCategoryForm;

    #[Inject]
    public AlacarteItemForm $alacarteItemForm;

    #[Inject]
    public AlacarteItemVariantForm $alacarteItemVariantForm;


    public function renderDefault()
    {
        $this->template->items = $this->alacartecategoryRepository->findAll()->order('ordered');
        $this->id = null;
        $this->category_id = null;
    }

    public function renderCategoryItems($id)
    {
        $this->template->id = $id;
        $this->template->category = $this->alacartecategoryRepository->findById($id);
        $this->template->items = $this->alacarteitemRepository->findBy(['alacartecategory_id' => $id])->order('ordered');
    }

    public function renderItemVariant($id)
    {
        $this->template->id = $id;
        $this->template->alacarteitem = $this->alacarteitemRepository->findById($id);
        $this->template->items = $this->alacarteitemvariantRepository->findBy(['alacarteitem_id' => $id]);
    }

    public function renderAdd()
    {
        $this->id = null;
    }

    public function renderEdit($id)
    {
        $item = $this->alacartecategoryRepository->findById($id);
        if (!$item) {
            $this->flashMessage('Nie je možné upravovať neexistujúci záznam', 'alert-danger');
            $this->redirect('Alacarte:default');
        }
        $this['alacarteCategoryForm']->setDefaults($item);
        foreach ($item->related('alacartecategorydictionary') as $dictionary) {
            $this['alacarteCategoryForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }
        $this->template->item = $item;
    }

    public function renderAddItem($category_id)
    {
        $category = $this->alacartecategoryRepository->findById($category_id);
        if (!$category) {
            $this->flashMessage('Nenašiel som kategóriu, do ktorej chceš pridávať položky!', 'alert-danger');
            $this->redirect('Alacarte:default');
        }

        $this->template->category = $category;
    }

    public function renderEditItem($id)
    {
        $row = $this->alacarteitemRepository->findById($id);
        if (!$row) {
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('Alacarte:default');
        }
        $this['alacarteItemForm']->setDefaults($row);
        $this['alacarteItemForm']['allergens']->setDefaultValue(
            $this->alacarteitemallergenRepository->findBy(['alacarteitem_id' => $id])->fetchPairs(null, 'allergen_id')
        );
        foreach ($row->related('alacarteitemdictionary') as $dictionary) {
            $this['alacarteItemForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }
        $this->template->category = $row->alacartecategory;
        $this->category_id = $row->alacartecategory->id;
    }

    public function renderAddItemVariant($alacarteitem_id)
    {
        $this->template->alacarteitem = $this->alacarteitemRepository->findById($alacarteitem_id);
    }

    public function renderEditItemVariant($id)
    {
        $row = $this->alacarteitemvariantRepository->findById($id);
        if (!$row) {
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('Alacarte:default');
        }
        $this['alacarteItemVariantForm']->setDefaults($row);
        foreach ($row->related('alacarteitemvariantdictionary') as $dictionary) {
            $this['alacarteItemVariantForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }
        $this->template->alacarteitem = $row->alacarteitem;
        $this->alacarteitem_id = $row->alacarteitem->id;
    }

    public function handleDelete($id)
    {
        $item = $this->alacartecategoryRepository->findById($id);
        if (!$item) {
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('this');
        }
        $item->delete();
        $this->flashMessage('Úspešne vymazané', 'alert-success');
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handleDeleteItem($id)
    {
        $item = $this->alacarteitemRepository->findById($id);
        if (!$item) {
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('this');
        }
        $item->delete();
        $this->flashMessage('Úspešne vymazané', 'alert-success');
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handleDeleteItemVariant($id)
    {
        $item = $this->alacarteitemvariantRepository->findById($id);
        if (!$item) {
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('this');
        }
        $item->delete();
        $this->flashMessage('Úspešne vymazané', 'alert-success');
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleSetActive($id, $repository)
    {
        $result = $this->cmsService->setActive($id, $repository);
        $this->flashMessage($result['message'], $result['type']);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }


    public function createComponentAlacarteCategoryForm()
    {
        $this->alacarteCategoryForm->id = $this->id;

        return $this->alacarteCategoryForm->create();
    }

    public function createComponentAlacarteItemForm()
    {
        $this->alacarteItemForm->id = $this->id;
        $this->alacarteItemForm->category_id = $this->category_id;

        return $this->alacarteItemForm->create();
    }

    public function createComponentAlacarteItemVariantForm()
    {
        $this->alacarteItemVariantForm->id = $this->id;
        $this->alacarteItemVariantForm->alacarteitem_id = $this->alacarteitem_id;

        return $this->alacarteItemVariantForm->create();
    }

}