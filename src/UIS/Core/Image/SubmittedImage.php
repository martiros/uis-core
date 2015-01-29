<?php
namespace UIS\Core\Image;

use UIS\Core\File\UploadedTempFile;

class SubmittedImage extends SubmittedFile
{
    /**
     * @var \UIS\Core\File\UploadedTempFile
     */
    protected $tempImage = null;

    /**
     * @param string $imageType
     */
    public function setImageType($imageType)
    {
        $this->setFileType($imageType);
    }

    /**
     * @param UploadedTempFile $tempImage
     */
    public function setTempImage(UploadedTempFile $tempImage)
    {
        $this->setTempFile($tempImage);
    }

    /**
     * @return UploadedTempFile
     */
    public function getTempImage()
    {
        return $this->getTempFile();
    }
}

