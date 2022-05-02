<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\SettingsForm;
use Nette\DI\Attributes\Inject;

class SettingsPresenter extends BasePresenter{

    #[Inject]
    public SettingsForm $settingsForm;

    public function actionDefault(){
        $row = $this->settingsRepository->findById(1);
        $this['settingsForm']->setDefaults($row);

    }

    /**
     * @return \Nette\Application\UI\Form
     */
    public function createComponentSettingsForm(): \Nette\Application\UI\Form
    {
        $this->settingsForm->id = 1;
        return $this->settingsForm->create();
    }
}