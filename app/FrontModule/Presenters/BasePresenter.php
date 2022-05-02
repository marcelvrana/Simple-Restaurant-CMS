<?php

declare(strict_types=1);

namespace App\FrontModule\Presenters;


use Nette\Application\Attributes\Persistent;
use Nette\Database\SqlLiteral;
use Tracy\Debugger;

class BasePresenter extends \App\Presenters\BasePresenter
{
    #[Persistent]
    public string $locale;

    /**
     *
     */
    protected function startup()
    {
        parent::startup();
        $this->template->locale = $this->locale;
        $this->template->language = $this->languageRepository->findBy(['shortcode' => $this->locale])->fetch();

        $logger = Debugger::getLogger();

        // e-mail, na který se posílají notifikace, že došlo k chybě
        $logger->email = 'log@mywebsite.com';      // (string|string[]) výchozí je nenastaveno
        $logger->fromEmail = 'log@mywebsite.com';   // (string) výchozí je nenastaveno
        Debugger::$logSeverity = [E_WARNING, E_NOTICE];
    }

    /**
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function beforeRender()
    {
    }

}