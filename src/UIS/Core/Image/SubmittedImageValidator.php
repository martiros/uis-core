<?php
namespace UIS\Core\Image;

use Auth;
use UIS\Mvf\ValidatorTypes\Int;

class SubmittedImageValidator extends Int
{
    public function validate()
    {
        $submittedImageValue = trim($this->getVarValue());
        $submittedImage = new SubmittedImage();
        $this->setVarValue($submittedImage);
        if (empty($submittedImageValue)) {
            $submittedImage->setImageType(SubmittedImage::FILE_TYPE_EMPTY);
        } else if ($submittedImageValue === 'same') {
            $submittedImage->setImageType(SubmittedImage::FILE_TYPE_SAME);
        } else {
            return $this->validateTempImage($submittedImage, $submittedImageValue);
        }
        return $this->makeValid();
    }

    protected function validateTempImage(SubmittedImage $submittedImage, $id)
    {
        $tempFile = Uploader::getTempImage($id, false, true);
        if (empty($tempFile)) {
            return $this->makeError('image_not_found');
        }
        $submittedImage->setImageType(SubmittedImage::FILE_TYPE_NEW);
        $submittedImage->setTempImage($tempFile);
        return $this->makeValid();
    }

    public function allowChangeData()
    {
        return true;
    }

    public function validateRequired()
    {
        return $this->makeValid();
    }

    public function isEmpty()
    {
        return false;
    }
}
