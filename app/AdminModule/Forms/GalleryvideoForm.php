<?php

declare(strict_types=1);

namespace App\AdminModule\Forms;


use App\Filter\Filter;
use App\Model\Repository\GalleryvideoRepository;
use App\Model\Service\ImageService;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\SmartObject;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class GalleryvideoForm
{
    use SmartObject;
    use BootstrapRenderTrait;

    public int|null $id;
    public int|null $gallery_id;


    /**
     * @param GalleryvideoRepository $galleryvideoRepository
     * @param ImageService $imageService
     * @param Request $request
     */
    public function __construct(
        private GalleryvideoRepository $galleryvideoRepository,
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
        $form->addGroup();
        $form->addText('name', '')
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired('Povinné pole');
        $form->addText('code', 'Youtube video code from the link behind "?v="')
            ->setOption('description', Html::el('p')
                ->setHtml('e.g.: https://www.youtube.com/watch?v=<strong class="text-danger">VHoT4N43jK8</strong>')
            )
            ->addRule($form::MAX_LENGTH, 'Text is too long, maximum length is %d', 255)
            ->setRequired('Povinné pole');

        $form->addCheckbox('protectpersonalinfo', 'Enable privacy mode')
        ->setOption('description', 'When you turn on privacy mode, YouTube will not store information about visitors to your site unless they watch a video.');


\Tracy\Debugger::barDump($this->gallery_id);
        if ($this->id) $form->addHidden('id', $this->id);
        if ($this->gallery_id) $form->addHidden('gallery_id', $this->gallery_id);

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
            $row = $this->galleryvideoRepository->findById($this->id);
            $this->galleryvideoRepository->update($this->id, $values);
        } else {
            $row = $this->galleryvideoRepository->add($values);
        }

        $form->getPresenter()->flashMessage('Saved', 'alert-success');
        $form->getPresenter()->redirect('Gallery:galleryVideos', $this->gallery_id);

    }

}
