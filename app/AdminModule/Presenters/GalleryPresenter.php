<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;


use App\AdminModule\Forms\GalleryForm;
use App\AdminModule\Forms\GalleryvideoForm;
use App\Constants\Constants;
use Nette\Application\Attributes\Persistent;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;
use Nette\Http\Response;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;

class GalleryPresenter extends BasePresenter
{

    #[Inject]
    public GalleryForm $galleryForm;

    #[Inject]
    public GalleryvideoForm $galleryvideoForm;

    #[Persistent]
    public int|null $gallery_id;

    /**
     *
     */
    public function renderDefault(){
        $this->id = null;
        $this->gallery_id = null;
        $this->template->items = $this->galleryRepository->findAll()->order('ordered');
    }

    public function renderGalleryVideos($gallery_id){
        $this->template->parent = $this->galleryRepository->findById($gallery_id);
        $this->template->items = $this->galleryvideoRepository->findBy(['gallery_id' => $gallery_id])->order('id DESC');
    }

    /**
     *
     */
    public function renderAdd(){
        $this->id = null;
        $this->gallery_id = null;
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function renderEdit($id){
        $this->gallery_id = null;
        $item = $this->galleryRepository->findById($id);
        if (!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('News:default');
        }
        $this->galleryForm->id = $id;
        $this['galleryForm']->setDefaults($item);
        foreach ($item->related('gallerydictionary') as $dictionary) {
            $this['galleryForm']['dictionaries'][$dictionary->language_id]->setDefaults($dictionary);
        }

        $this->template->item = $item;
    }


    public function renderAddGalleryvideo($gallery_id){

        $this->template->parent = $this->galleryRepository->findById($gallery_id);
    }

    public function renderEditGalleryVideo($id, $gallery_id){
        $item = $this->galleryvideoRepository->findById($id);
        if (!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('News:default');
        }
        $this->galleryvideoForm->id = $id;
        $this['galleryvideoForm']->setDefaults($item);
        $this->template->item = $item;
    }

    public function renderGalleryPhotos($id){
        $item = $this->galleryRepository->findById($id);
        if (!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('default');
        }
        $this->template->row = $item;
        $this->template->items = $item->related('galleryphoto')->order('ordered');
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function handleUploadPhoto(): void
    {
        $item = $this->galleryRepository->findById($this->id);
        if (!$item) {
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('default');
        }

        $file = $this->getHttpRequest()->getFile('file');

        if (!$file->isOk()) {
            $errorMsg = $this->translator->translate('Chyba nahrávania obrázku');
            $this->getHttpResponse()->setContentType('text/plain');
            $this->getHttpResponse()->setCode(Response::S400_BAD_REQUEST);
            $this->presenter->sendResponse(new TextResponse($errorMsg));
        }

        try {
            $imageFull = $this->imageService->saveImage($file, [
                'publicPath' => Constants::IMAGE_UPLOAD_GALLERY_PATH . $this->id,
            ]);

            $imageThumb = $this->imageService->saveImage($file, [
                'publicPath' => Constants::IMAGE_UPLOAD_GALLERY_PATH . $this->id,
                'width' => Constants::IMAGE_UPLOAD_GALLERY_THUMB_WIDTH,
                'height' => Constants::IMAGE_UPLOAD_GALLERY_THUMB_HEIGHT,
                'method' => 'exact'
            ]);

            $imageOrig = $this->imageService->saveImage($file, [
                'publicPath' => Constants::IMAGE_UPLOAD_GALLERY_PATH . $this->id,
                'resize' => false,
            ]);
        } catch (\Exception  $e) {
            $this->flashMessage('Chyba : ' . $e->getMessage(), 'alert-danger');
            $this->redrawControl('flashMessage');
        }

        $imagePosition = $this->galleryphotoRepository->findBy(['gallery_id' => $this->id])->max('ordered');

        $this->galleryphotoRepository->add([
                                               'gallery_id' => $this->id,
                                               'img_thumb' => $imageThumb,
                                               'img' => $imageFull,
                                               'img_original' => $imageOrig,
                                               'ordered' => $imagePosition + 1
                                           ]);

        $this->flashMessage('Uložené', 'alert-success');
        $this->redrawControl();
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function handleSortPhotos(): void
    {

        $sortableData = Json::decode($this->getHttpRequest()->getPost('order'));

        $galleryPhotos = $this->galleryphotoRepository->findBy(['gallery_id' => $this->id]);

        foreach ($galleryPhotos as $row) {
            $row->update(['ordered' => array_search($row->id, $sortableData)]);
        }

        $this->flashMessage('Uložené', 'alert-success');
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    /**
     * @param $photoId
     * @throws \Nette\Application\AbortException
     */
    public function handleDeletePhoto($photoId): void
    {
        $galleryPhoto = $this->galleryphotoRepository->findById($photoId);

        if (!$galleryPhoto) {
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('this');
        }

        if ($galleryPhoto->img && is_file(WWW_DIR . '/' . $galleryPhoto->img)) {
            FileSystem::delete(WWW_DIR . '/' . $galleryPhoto->img);
        }

        if ($galleryPhoto->img_thumb && is_file(WWW_DIR . '/' . $galleryPhoto->img_thumb)) {
            FileSystem::delete(WWW_DIR . '/' . $galleryPhoto->img_thumb);
        }

        if ($galleryPhoto->img_original && is_file(WWW_DIR . '/' . $galleryPhoto->img_original)) {
            FileSystem::delete(WWW_DIR . '/' . $galleryPhoto->img_original);
        }

        $galleryPhoto->delete();

        $this->flashMessage('Odstránené', 'alert-success');

        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete($id){
        $item = $this->galleryRepository->findById($id);
        if(!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('this');
        }


        foreach($item->related('galleryphoto') as $photo){

            if ($photo->img && is_file(WWW_DIR . '/' . $photo->img)) {
                FileSystem::delete(WWW_DIR . '/' . $photo->img);
            }

            if ($photo->img_thumb && is_file(WWW_DIR . '/' . $photo->img_thumb)) {
                FileSystem::delete(WWW_DIR . '/' . $photo->img_thumb);
            }

            if ($photo->img_original && is_file(WWW_DIR . '/' . $photo->img_original)) {
                FileSystem::delete(WWW_DIR . '/' . $photo->img_original);
            }
        }

        $item->delete();
        $this->flashMessage('Úspešne vymazané', 'alert-success');
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');

    }



    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDeletevideogalleryitem($id){
        $item = $this->videogalleryitemRepository->findById($id);
        if(!$item){
            $this->flashMessage('Neexistujúci záznam', 'alert-danger');
            $this->redirect('this');
        }
        $item->delete();
        $this->flashMessage('Úspešne vymazané', 'alert-success');
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentGalleryForm(): Form
    {
        $this->galleryForm->id = $this->id;
        return $this->galleryForm->create();
    }

    protected function createComponentGalleryvideoForm(): Form
    {
        $this->galleryvideoForm->id = $this->id;
        $this->galleryvideoForm->gallery_id = $this->gallery_id;
        return $this->galleryvideoForm->create();
    }
}
