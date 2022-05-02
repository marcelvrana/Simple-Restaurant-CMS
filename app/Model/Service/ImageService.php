<?php

declare(strict_types=1);

namespace App\Model\Service;

use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\Random;
use Nette\Utils\Strings;

/**
 * Operations with images.
 */
class ImageService
{
    public const METHOD_PLACE = 'place';

    public const METHOD_EXACT = 'exact';

    private int|null $width;

    private bool $resize;

    private int|null $height;

    private string|null $publicPath;

    private string|null $filename;

    private string|null $method;


    /**
     * @param $options
     */
    private function setOptions($options): void
    {
        if (isset($options['filename'])) {
            $this->filename = $options['filename'];
        } else {
            $this->filename = Random::generate(10);
        }

        if (isset($options['width'])) {
            $this->width = $options['width'];
        } else {
            $this->width = 1600;
        }

        if (isset($options['height'])) {
            $this->height = $options['height'];
        } else {
            $this->height = null;
        }

        if (isset($options['resize'])) {
            $this->resize = $options['resize'];
        } else {
            $this->resize = true;
        }

        if (isset($options['publicPath'])) {
            $this->publicPath = $options['publicPath'];
        } else {
            $this->publicPath = 'data';
        }

        if (isset($options['method'])) {
            $this->method = $options['method'];
        } else {
            $this->method = null;
        }
    }


    /**
     * @param \Nette\Http\FileUpload $file
     * @param array                  $options
     *
     * @return string|false
     * @throws \Nette\Utils\ImageException
     * @throws \Nette\Utils\UnknownImageFileException
     */
    public function saveImage(\Nette\Http\FileUpload $file, array $options = []): string|false
    {
        if (!$file->isOk()) {
            return false;
        }

        $this->setOptions($options);

        $this->filename = Strings::webalize($this->filename);

        $systemPath = WWW_DIR . '/' . $this->publicPath;

        // Create directory
        FileSystem::createDir($systemPath);

        // svg support
        if ($file->getContentType() == 'image/svg+xml') {
            $file->move($systemPath . '/' . $this->filename . '.svg');

            return $this->publicPath . '/' . $this->filename . '.svg';
        }

        $image = Image::fromFile($file->getTemporaryFile());

        // resize?
        if ($this->resize) {
            if ($this->method === self::METHOD_PLACE) {
                $baseImage = Image::fromBlank($this->width, $this->height, Image::rgb(255, 255, 255));
                $image->resize($this->width, null, Image::SHRINK_ONLY);

                if ($image->height > $this->height) {
                    $image->resize(null, $this->height, Image::SHRINK_ONLY);
                    $image = $baseImage->place($image, '50%', 0);
                } elseif ($image->height < $this->height) {
                    $image = $baseImage->place($image, 0, '50%');
                }
            }
            else if ($this->method === self::METHOD_EXACT){
                $image->resize($this->width, $this->height, Image::EXACT | Image::SHRINK_ONLY);
            } else {
                $image->resize($this->width, $this->height, Image::SHRINK_ONLY);
            }
        }

        $image->save($systemPath . '/' . $this->filename . '.' . $file->getImageFileExtension());

        return $this->publicPath . '/' . $this->filename . '.' . $file->getImageFileExtension();
    }
}
