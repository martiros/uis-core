<?php namespace UIS\Core\Image;

use UIS\Core\File\UploadedFile;

class SubmittedImage
{
    const IMAGE_TYPE_NEW = 'new';
    const IMAGE_TYPE_EMPTY = 'empty';
    const IMAGE_TYPE_SAME = 'same';

    /**
     * @var string
     */
    protected $imageType = null;

    /**
     * @var \UIS\Core\File\UploadedFile
     */
    protected $tempImage = null;

    /**
     * @param string $imageType
     */
    public function setImageType($imageType)
    {
        $this->imageType = $imageType;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->imageType === self::IMAGE_TYPE_NEW;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->imageType === self::IMAGE_TYPE_EMPTY;
    }

    /**
     * @return bool
     */
    public function isSame()
    {
        return $this->imageType === self::IMAGE_TYPE_SAME;
    }

    /**
     * @param UploadedFile $tempImage
     */
    public function setTempImage(UploadedFile $tempImage)
    {
        $this->tempImage = $tempImage;
    }

    /**
     * @return UploadedFile
     */
    public function getTempImage()
    {
        return $this->tempImage;
    }
}

