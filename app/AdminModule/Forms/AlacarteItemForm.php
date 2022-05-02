<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Filter\Filter;
use App\Model\Admin\LanguageManager;
use App\Model\Repository\AlacartecategoryRepository;
use App\Model\Repository\AlacarteitemallergenRepository;
use App\Model\Repository\AlacarteitemRepository;
use App\Model\Repository\AllergenRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class AlacarteItemForm
{
    use SmartObject;

    use BootstrapRenderTrait;

    public int|null $id;

    public int|null $category_id;


    /**
     * AlacarteItemForm constructor.
     * @param AlacartecategoryRepository $alacartecategoryRepository
     * @param AlacarteitemRepository $alacarteitemRepository
     * @param AlacarteitemallergenRepository $alacarteitemallergenRepository
     * @param AllergenRepository $allergenRepository
     * @param LanguageManager $languageManager
     * @param ImageService $imageService
     * @param Request $request
     */
    public function __construct(
        private AlacartecategoryRepository $alacartecategoryRepository,
        private AlacarteitemRepository $alacarteitemRepository,
        private AlacarteitemallergenRepository $alacarteitemallergenRepository,
        private AllergenRepository $allergenRepository,
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
        $allergens = $this->allergenRepository->findAll();
        $allergensSelect = [];
        foreach ($allergens as $allergen) {
            $allergensSelect[$allergen->id] = $allergen->number . ' - ' . Filter::dictionaryData(
                    $allergen,
                    'allergen',
                    'name'
                );
        }
        $form = new Form();
        $form->addProtection('Form protection error, try again');
        $form->addGroup('Translations');
        $dictionaries = $form->addContainer('dictionaries');
        foreach ($languages as $lang) {
            $dictionary = $dictionaries->addContainer($lang->id);

            $dictionary->addText('name', 'Name ' . $lang->name)
                ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
                ->setRequired(false);

            $dictionary->addText('description', 'Description of meal - ' . $lang->name)
                ->setRequired(false);

            $dictionary->addHidden('language_id', $lang->id);

            if ($this->id) {
                $dictionary->addHidden('id', null);
                $dictionary->addHidden('alacarteitem_id', $this->id);
            }
        }
        $form->addGroup('Additional info');
        $form->addText('amount', 'Weight or volume of meal (e.g. 0.33l, 400g, ...)')
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired('Required');

        $form->addText('amount_side_dish', 'Weight of the side dish (e.g. 400g)')
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired(false);

        $form->addText('price', 'Price of meal')
            ->setRequired(false)
            ->addRule(Form::FLOAT, 'A number in the format of two decimal numbers, e.g. 10.30');


        $form->addMultiSelect('allergens', 'Allergens in meal', $allergensSelect)
            ->setHtmlAttribute('class', 'select2');

        $form->addCheckbox('top', 'Recommended food?');
        $form->addCheckbox('hot', 'Spicy food?');
        $form->addCheckbox('vegan', 'No meat meal?');
        $form->addCheckbox('is_active', 'Is active?');


        if ($this->id) {
            $form->addHidden('id', $this->id);
        }
        $form->addHidden('alacartecategory_id', $this->category_id);

        $form->addGroup();
        $form->addSubmit('submit', 'save');


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
        if (isset($values->allergens)) {
            $allergens = $values->allergens;
        }
        if ($values->price == '') {
            unset($values->price);
        }
        unset($values->allergens);


        if ($this->id) {
            $row = $this->alacarteitemRepository->findById($this->id);
            $this->alacarteitemRepository->edit($this->id, $values);
        } else {
            $last = $this->alacarteitemRepository->findAll()->order('ordered DESC')->limit(1)->fetch();
            $values['ordered'] = $last ? $last->ordered + 1 : 1;
            $row = $this->alacarteitemRepository->create($values);
        }

        if (isset($allergens)) {
            $old = $this->alacarteitemallergenRepository->findBy(['alacarteitem_id' => $row->id]);
            if ($old->count('*')) {
                $old->delete();
            }
            foreach ($allergens as $allergen) {
                $this->alacarteitemallergenRepository->add(['alacarteitem_id' => $row->id, 'allergen_id' => $allergen]);
            }
        }


        $form->getPresenter()->flashMessage('Saved!', 'alert-success');
        $form->getPresenter()->redirect('categoryItems', $this->category_id);
    }


}
