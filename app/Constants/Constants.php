<?php

declare(strict_types=1);

namespace App\Constants;

class Constants
{

    /**
     * Default admin paginator items per page.
     */
    public const FRONT_PAGINATOR_ITEMS_PER_PAGE = 20;

    /**
     * Main admin role name and ID used in ACL and database.
     */
    public const SUPERADMIN_ROLE = 'superadmin';
    public const SUPERADMIN_ROLE_ID = 1;

    /**
     * Admin Module language IDs in use
     */
    public const ADMIN_LANGUAGES = [1, 3];

    public const DEFAULT_LOCALE_ID = 2;


    /**
     * IMAGE UPLOAD SETTINGS
     */
    public const IMAGE_UPLOAD_GALLERY_PATH = 'data/gallery/';
    public const IMAGE_UPLOAD_GALLERY_THUMB_WIDTH = 800;
    public const IMAGE_UPLOAD_GALLERY_THUMB_HEIGHT = 800;
    public const IMAGE_UPLOAD_ARTICLE_GALLERY_THUMB_HEIGHT = 344;
    public const IMAGE_UPLOAD_SLIDESHOW_MOBILE_HEIGHT = 600;


    /**
     * Registration discount value for 1st order in percent
     */
    public const REGISTRATION_DISCOUNT = 5;

    /**
     * Export paths settings
     */
    public const STOCK_EXPORT_PATH = ROOT_DIR . '/temp/csv/';

}
