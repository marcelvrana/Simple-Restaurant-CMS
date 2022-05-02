<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Filter\Filter;
use App\Model\Repository\SettingsRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class SettingsForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;


    public function __construct(
        private SettingsRepository $settingsRepository,
        private ImageService $imageService,
        private Request $request
    ) {
    }


    /**
     * @return Form
     */
    public function create(): Form
    {
        $form = new Form();
        $form->addProtection('Form protection error, try again');
        $form->addGroup('Basic settings');

        $form->addText('email', 'Email')
            ->addRule(form::EMAIL, 'Invalid email format');
        $form->addText('phone', 'Phone');
        $form->addText('name', 'Company name');
        $form->addTextArea('address', 'Address');
        $form->addText('maplink', 'Google Map link');
        $form->addText('facebook', 'Facebook');
        $form->addText('instagram', 'Instagram');

        $form->addGroup('Opening hours');
        $form->addText('mo', 'Monday');
        $form->addText('tu', 'Tuesday');
        $form->addText('we', 'Wednesday');
        $form->addText('th', 'Thursday');
        $form->addText('fr', 'Friday');
        $form->addText('sa', 'Saturday');
        $form->addText('su', 'Sunday');

        if ($this->id) {
            $form->addHidden('id', $this->id);
        }
        $form->addGroup();
        $form->addSubmit('submit', 'Uložiť');


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
            $this->settingsRepository->update($this->id, $values);
            $form->getPresenter()->flashMessage('Uložené', 'alert-success');
            $form->getPresenter()->redirect('this');
        }
    }

}
