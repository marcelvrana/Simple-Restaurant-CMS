<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Model\Repository\LanguageRepository;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class LanguageForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;

    /**
     * LanguageForm constructor.
     * @param LanguageRepository $languageRepository
     */
    public function __construct( private LanguageRepository $languageRepository)
    {
    }


    /**
     * @return Form
     */
    public function create(): Form
    {
        $form = new Form();
        $form->addProtection('Form protection error, try again');

        $form->addGroup('Informations');
        $form->addText('name', 'Name')
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired('Required');

        $form->addText('webname', 'Name on website')
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired('Require');

        $form->addText('shortcode', 'Shortcode (e.g. sk, en, de, ...)')
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired('Required');

        $form->addCheckbox('is_active', 'Is active?');

        $form->addSubmit('submit', 'Save');

        if ($this->id) {
            $form->addHidden('id', $this->id);
            $form->addHidden('is_default');
        }

        $form->onValidate[] = [$this, 'validateForm'];

        $form->onError[] = [$this, 'errorForm'];

        $form->onSuccess[] = [$this, 'successForm'];

        return $this->setBootstrapRender($form);
    }

    public function validateForm(Form $form, ArrayHash $values): void
    {
        $row = $this->languageRepository->findBy(['shortcode' => $values->shortcode])->fetch();

        if ($row){
            $form['shortcode']->addError('Language with this shortcode exists!');
        }

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
            $this->languageRepository->update($this->id, $values);
            $form->getPresenter()->redirect('this');
        } else {
            $last = $this->languageRepository->findAll()->order('ordered DESC')->limit(1)->fetch();
            $values['ordered'] = $last->ordered + 1;
            $row = $this->languageRepository->add($values);
            $form->getPresenter()->redirect('Language:default');
        }
    }

}