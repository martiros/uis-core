<?php namespace UIS\Core\Image;

use Auth;
use UIS\Mvf\ValidatorTypes\Int;

class SubmittedImageValidator extends Int
{
    public function validate()
    {
        $submittedImageValue = trim($this->getVarValue());
        $submittedImage = new SubmittedImage();
        $this->setVarValue($submittedImage);
        if ($this->isEmpty()) {
            $submittedImage->setImageType(SubmittedImage::IMAGE_TYPE_EMPTY);
        } else if ($submittedImageValue === 'same') {
            $submittedImage->setImageType(SubmittedImage::IMAGE_TYPE_SAME);
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
        $submittedImage->setImageType(SubmittedImage::IMAGE_TYPE_NEW);
        $submittedImage->setTempImage($tempFile);
        return $this->makeValid();
    }

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

    private $module = null;

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function dd_validate()
    {
        if($this->isEmpty()){
            $submittedImage->setImageType(Media_ImgUploader_SubmittedImage::IMAGE_TYPE_EMPTY);
        } else {
            $validatorError = parent::validate();
            if( !$validatorError->isValid() ){
                return $validatorError;
            }

            if ($submittedImageValue==='0') {
                $submittedImage->setImageType(Media_ImgUploader_SubmittedImage::IMAGE_TYPE_SAME);
            } else{
                $this->validateTempImage($submittedImage);
            }
        }
        $this->setVarValue($submittedImage);
        return $this->validatorError;
    }

    public function allowChangeData()
    {
        return true;
    }

    public function passEmptyData()
    {
        return true;
    }
}
