<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Model\Repository\TranslationRepository;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class TranslationForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $language_id;


    public function __construct(
        private TranslationRepository $translationRepository,
    ) {
    }


    /**
     * @return Form
     */
    public function create(): Form
    {
        $translationItems = $this->translationRepository->findBy(['language_id' => $this->language_id])->fetchAll();

        $form = new Form();
        $form->addProtection('Form protection error, try again');

        $form->addGroup('Translations');
        $translations = $form->addContainer('translations');
        foreach($translationItems as $translation){
            $translationData = $translations->addContainer($translation->id);
            $translationData->addHidden('id', $translation->id);
            if($translation->translation != strip_tags($translation->translation)) {
                $input = $translationData->addTextArea('translation', $translation->placeholder . ' - This block contains HTML Tags - these must not be changed otherwise they will not work on the page. I recommend changing only the translations or the link in href');
                $input->setHtmlAttribute('style', 'height:130px;');
            } else {
                $input = $translationData->addText('translation', $translation->placeholder);
            }
            $input->setDefaultValue($translation->translation);
        }


        $form->addGroup();
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
        foreach($values->translations as $item){
            $this->translationRepository->update($item->id, ['translation' => $item->translation]);
        }

        $form->getPresenter()->flashMessage('Saved', 'alert-success');

        $form->getPresenter()->redirect('this');
    }

}
