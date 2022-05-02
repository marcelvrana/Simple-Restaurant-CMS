<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Repository\AdminRepository;
use App\Model\Repository\AlacartecategoryRepository;
use App\Model\Repository\AlacarteitemallergenRepository;
use App\Model\Repository\AlacarteitemRepository;
use App\Model\Repository\AlacarteitemvariantRepository;
use App\Model\Repository\AllergenRepository;
use App\Model\Repository\BannerRepository;
use App\Model\Repository\GalleryphotoRepository;
use App\Model\Repository\GalleryRepository;
use App\Model\Repository\LanguageRepository;
use App\Model\Repository\NewsgalleryRepository;
use App\Model\Repository\NewsRepository;
use App\Model\Repository\SeosettingsRepository;
use App\Model\Repository\SettingsRepository;
use App\Model\Repository\SpaceOfferGalleryRepository;
use App\Model\Repository\SpaceOfferRepository;
use App\Model\Repository\TranslationRepository;
use App\Model\Repository\GalleryvideoRepository;
use App\Model\Repository\WeekmenuRepository;
use App\Model\Service\CmsService;
use App\Model\Service\ImageService;
use Nette;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;


class BasePresenter extends Nette\Application\UI\Presenter
{


    /** Repository */

    #[Inject]
    public AdminRepository $adminRepository;

    #[Inject]
    public LanguageRepository $languageRepository;

    #[Inject]
    public NewsRepository $newsRepository;

    #[Inject]
    public GalleryRepository $galleryRepository;

    #[Inject]
    public GalleryphotoRepository $galleryphotoRepository;

    #[Inject]
    public AlacartecategoryRepository $alacartecategoryRepository;

    #[Inject]
    public AlacarteitemRepository $alacarteitemRepository;

    #[Inject]
    public AlacarteitemvariantRepository $alacarteitemvariantRepository;

    #[Inject]
    public AlacarteitemallergenRepository $alacarteitemallergenRepository;

    #[Inject]
    public AllergenRepository $allergenRepository;

    #[Inject]
    public NewsgalleryRepository $newsgalleryRepository;

    #[Inject]
    public WeekmenuRepository $weekmenuRepository;

    #[Inject]
    public SettingsRepository $settingsRepository;

    #[Inject]
    public BannerRepository $bannerRepository;

    #[Inject]
    public SeosettingsRepository $seosettingsRepository;

    #[Inject]
    public SpaceOfferRepository $spaceOfferRepository;

    #[Inject]
    public SpaceOfferGalleryRepository $spaceOfferGalleryRepository;

    #[Inject]
    public TranslationRepository $translationRepository;

    #[Inject]
    public GalleryvideoRepository $galleryvideoRepository;

    /** Service */
    #[Inject]
    public CmsService $cmsService;

    #[Inject]
    public ImageService $imageService;


}
