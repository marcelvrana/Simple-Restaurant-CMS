<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\Attributes\Persistent;
use Tracy\Debugger;

class BasePresenter extends \App\Presenters\BasePresenter
{
    #[Persistent]
    public int|null $id = null;

    protected function startup()
    {
        parent::startup();
        $this->user->getStorage()->setNamespace('makawa-cms');

        if (!$this->user->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $logger = Debugger::getLogger();

        // e-mail, na který se posílají notifikace, že došlo k chybě
        $logger->email = 'develop@marcelvrana.sk';      // (string|string[]) výchozí je nenastaveno

        // odesílatel e-mailu
        $logger->fromEmail = 'develop@marcelvrana.sk';   // (string) výchozí je nenastaveno

        // pro jaké úrovně chyb se loguje i BlueScreen?
        Debugger::$logSeverity = [E_WARNING, E_NOTICE];

    }




    /**
     * @throws \Nette\Application\AbortException
     */
    public function handleChangeOrder()
    {
        $this->cmsService->orderItems($this->getRequest()->getPost());
        $this->isAjax() ? $this->redrawControl() : $this->redirect('default');
    }
}