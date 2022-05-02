<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette\Application\UI\Form;
use Nette\Security\User;

class SignInForm {

    /***/
    public function __construct(private User $user)
    {
    }


    /**
     * @return Form
     */
    public function create(): Form
    {

        $form = new Form();

        $form->addText('login', 'Login')
            ->setRequired('Required');

        $form->addPassword('password', 'Heslo')
            ->setRequired('Required');

        $form->addCheckbox('remember', 'Remember me');

        $form->addSubmit('submit', 'Log in');


        $form->onError[] = [$this, 'errorForm'];

        $form->onSuccess[] = [$this, 'successForm'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function errorForm(Form $form): void
    {
        if ($form->getPresenter()->isAjax()) {
            $form->getPresenter()->redrawControl();
        }
    }

    /**
     * @param $form
     * @param $values
     */
    public function successForm($form, $values): void
    {
        try {
            $this->user->setExpiration($values->remember ? '14 days' : '1440 minutes');
            $this->user->login($values->login, $values->password);
            $form->getPresenter()->redirect('Admin:');

        } catch (\Nette\Security\AuthenticationException $e) {
            $form['login']->addError(' ');
            $form['password']->addError(' ');
            $form->getPresenter()->flashMessage('Login or password isn\'t correct.');
            $form->getPresenter()->redrawControl();
            return;
        }

    }

}