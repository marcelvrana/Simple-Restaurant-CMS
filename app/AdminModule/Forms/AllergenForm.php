<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Model\Admin\LanguageManager;
use App\Model\Repository\AllergenRepository;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class AllergenForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;


    /**
     * NavigationForm constructor.
     * @param AllergenRepository $allergenRepository
     * @param LanguageManager $languageManager
     */
    public function __construct(
        private AllergenRepository $allergenRepository,
        private LanguageManager $languageManager
    ) {
    }


    /**
     * @return Form
     */
    public function create(): Form
    {
        $languages = $this->languageManager->getActiveLanguages();
        $form = new Form();
        $form->addProtection('Form protection error, try again');
        $form->addGroup('Basic information');

        $form->addInteger('number', 'Allergen number')
        ->addRule(FORM::INTEGER, 'Must contains only number!');

        $dictionaries = $form->addContainer('dictionaries');
        foreach ($languages as $lang){
            $dictionary = $dictionaries->addContainer($lang->id);

            $dictionary->addText('name', 'Name - ' . $lang->name )
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(FALSE);

            $dictionary->addHidden('language_id', $lang->id);

            if ($this->id) {
                $dictionary->addHidden('id', NULL);
                $dictionary->addHidden('allergen_id', $this->id);
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
        if ($this->id) {

            $this->allergenRepository->edit($this->id, $values);

        } else {
            $last = $this->allergenRepository->findAll()->order('ordered DESC')->limit(1)->fetch();
            $values['ordered'] = $last ? $last->ordered + 1 : 1;
            $row = $this->allergenRepository->create($values);

        }
        $form->getPresenter()->flashMessage('Saved', 'alert-success');
            $form->getPresenter()->redirect('Allergen:default');
    }

}
