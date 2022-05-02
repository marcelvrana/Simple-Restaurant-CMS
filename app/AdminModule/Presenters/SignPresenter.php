<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;


use App\AdminModule\Forms\SignInForm;
use App\Authenticator\AdminAuthenticator;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;

class SignPresenter extends Presenter
{
    /**
     * @ORM\Column(type="string")
     */

    #[Inject]
    public AdminAuthenticator $adminAuthenticator;

    #[Inject]
    public SignInForm $signInForm;


    protected function startup(): void
    {
        parent::startup();

        $this->user->setAuthenticator($this->adminAuthenticator);
        $this->user->getStorage()->setNamespace('makawa-cms');
    }


    public function ActionIn(){
        $this->setLayout('login');
    }

    public function ActionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Boli ste úspešne odhlásený', 'info');
        $this->redirect('Sign:in');
    }

    protected function createComponentSignInForm(): Form
    {
        return $this->signInForm->create();
    }

}