<?php namespace UIS\Core\Image;

use UIS\Mvf\ValidatorTypes\Int;
use UIS\Core\Image\Uploader;
use Auth;

class SubmittedImageValidator extends Int
{
    public function validate()
    {
        $submittedImageValue = trim($this->getVarValue());
        $submittedImage = new SubmittedImage();
        $this->setVarValue($submittedImage);
        if ($this->isEmpty()) {
            return $submittedImage->setImageType(SubmittedImage::IMAGE_TYPE_EMPTY);
        } else if ($submittedImageValue === 'new') {
            $submittedImage->setImageType(SubmittedImage::IMAGE_TYPE_NEW);
        } else {
            return $this->validateTempImage($submittedImage, $submittedImageValue);
        }


//        $valueToValidate = $this->getVarValue();
//        if (Util::isEmail($valueToValidate) === false) {
//            return $this->makeError();
//        }
//        return $this->makeValid();
    }

    protected function validateTempImage(SubmittedImage $submittedImage, $id)
    {
        $tempFileData = Uploader::getTempImage($id . '');
        if (empty($tempFile)) {
            return $this->makeError('image_not_found');
        }

        $userId = Auth::user()->id;
//        if () {
//
//        }
        uis_dump($tempFile);
        /*********************************************************************/
        /*********************************************************************/
        /*********************************************************************/

        $configItem = $this->getConfig();


        if( empty($tempFile) ){
            return $this->validatorError->setError( $configItem->getError() );
        }

        $adminId = Core_Admin::cAdmin('id');
        if( $adminId != $tempFile->data('add_admin_id') ){
            return $this->validatorError->setError( $configItem->getError() );
        }

        // uis_dump( $tempFile->data('uploader_module') );

        $submittedImage->setImageType(Media_ImgUploader_SubmittedImage::IMAGE_TYPE_NEW);
        $submittedImage->setTempImage($tempFile);
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
