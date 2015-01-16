<?php namespace UIS\Core\Image;

use UIS\Core\File\UploadedTempFile;

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
     * @var \UIS\Core\File\UploadedTempFile
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
     * @param UploadedTempFile $tempImage
     */
    public function setTempImage(UploadedTempFile $tempImage)
    {
        $this->tempImage = $tempImage;
    }

    /**
     * @return UploadedTempFile
     */
    public function getTempImage()
    {
        return $this->tempImage;
    }
}

