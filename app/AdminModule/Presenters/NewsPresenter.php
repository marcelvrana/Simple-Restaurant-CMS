<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;


use App\AdminModule\Forms\NewsForm;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;

class NewsPresenter extends BasePresenter
{

    #[Inject]
    public NewsForm $newsForm;


    /**
     *
     */
    public function renderDefault(){
        $this->id = null;
        $this->template->items = $this->newsRepository->findAll()->order('created DESC');
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
        $item = $this->newsRepository->findById($id);
        if (!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('News:default');
        }
        $this->newsForm->id = $id;
        $this['newsForm']->setDefaults($item);
        if ($item->showfrom) $this['newsForm']['showfrom']->setDefaultValue($item->showfrom->format('d.m.Y'));
        if ($item->showto) $this['newsForm']['showto']->setDefaultValue($item->showto->format('d.m.Y'));
        $newsgallery = $this->newsgalleryRepository->findBy(['news_id' => $id])->fetchPairs(null, 'gallery_id');
        if($newsgallery) $this['newsForm']['newsgallery']->setDefaultValue($newsgallery);
        foreach ($item->related('newsdictionary') as $dictionary) {
            $this['newsForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }

        $this->template->item = $item;

    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id){
        $item = $this->newsRepository->findById($id);
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
    protected function createComponentNewsForm(): Form
    {
        $this->newsForm->id = $this->id;
        return $this->newsForm->create();
    }
}
