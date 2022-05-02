<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Model\Admin\LanguageManager;
use App\Model\Repository\AlacarteitemvariantRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;

class AlacarteItemVariantForm
{
    use SmartObject;

    use BootstrapRenderTrait;

    public int|null $id;

    public int|null $alacarteitem_id;


    /**
     * AlacarteItemVariantForm constructor.
     * @param AlacarteitemvariantRepository $alacarteitemvariantRepository
     * @param LanguageManager $languageManager
     * @param ImageService $imageService
     * @param Request $request
     */
    public function __construct(
        private AlacarteitemvariantRepository $alacarteitemvariantRepository,
        private LanguageManager $languageManager,
        private ImageService $imageService,
        private Request $request
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
        $form->addGroup('Translations');
        $dictionaries = $form->addContainer('dictionaries');
        foreach ($languages as $lang) {
            $dictionary = $dictionaries->addContainer($lang->id);

            $dictionary->addText('name', 'Name ' . $lang->name)
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(false);

            $dictionary->addHidden('language_id', $lang->id);

            if ($this->id) {
                $dictionary->addHidden('id', null);
                $dictionary->addHidden('alacarteitemvariant_id', $this->id);
            }
        }
        $form->addGroup('Additional info');

        $form->addText('amount', 'Weight or volume of meal (e.g. 0.33l, 400g, ...)')
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired('Required');

        $form->addText('amount_side_dish', 'Weight of the side dish (e.g. 400g)')
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired(false);

        $form->addHidden('alacarteitem_id', $this->alacarteitem_id);

        $form->addText('price', 'Price')
            ->setRequired('Required')
            ->addRule(Form::FLOAT, 'A number in the format of two decimal numbers, e.g. 10.30');

        $form->addCheckbox('is_active', 'Is active?');


        if ($this->id) {
            $form->addHidden('id', $this->id);
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
        if ($this->id) {
            $this->alacarteitemvariantRepository->edit($this->id, $values);
        } else {
            $this->alacarteitemvariantRepository->create($values);
        }

        $form->getPresenter()->flashMessage('Saved!', 'alert-success');
        $form->getPresenter()->redirect('itemvariant', $this->alacarteitem_id);
    }


}
