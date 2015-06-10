<?php

namespace UIS\Core\Image;

use UIS\Core\File\UploadedFile;
use UIS\Core\File\Uploader as FileUploader;
use UIS\Core\File\Exceptions\FileNotFoundException;

class Uploader extends FileUploader
{
    const TYPE = 'image';

    protected $options = [
        'file_max_size' => 5,
        'extensions' => ['jpg', 'jpeg', 'png'],
    ];

    public function getUploaderKey()
    {
        // @TODO: Fixme
        return 'default';
    }

    public function getUploaderType()
    {
        return self::TYPE;
    }

    /**
     * @param int $id
     * @param bool $checkUser
     * @param bool $findOrFail
     * @return \UIS\Core\File\UploadedFile
     * @throws \UIS\Core\File\Exceptions\FileNotFoundException
     */
    public static function getTempImage($id, $findOrFail = true, $checkUser = true)
    {
        $file = self::getTempFile($id, $findOrFail, $checkUser);
        if (empty($file)) {
            return;
        }
        if ($file->getUploaderType() !== self::TYPE) {
            if ($findOrFail) {
                throw new FileNotFoundException();
            }

            return;
        }

        return $file;
    }

    protected function getFileExtension(UploadedFile $file)
    {
        if (!$file->isImage()) {
            throw new InvalidImageException('Invalid file type-'.$file->getType());
        }

        return parent::getFileExtension($file);
    }
}

class Media_ImgUploader
{
    /*
        ************************************************************************************************************
        ************************************************************************************************************
        ************************************************************************************************************
        ************************************************************************************************************
        ************************************************************************************************************
    */

    /**
     * @param string $id
     * @return bool
     */
    public static function isValidImgId($id)
    {
        return !empty(self::getTempImage($id));
    }

    /**
     * @return array
     */
    public static function getAllModules()
    {
        $imageUploaderModules = [];
        $modules = UIS_Module_Loader::getInstance()->getModuleNames();
        foreach ($modules as $moduleName) {
            $moduleImagesConfig = Core_Config::conf($moduleName.'.images');
            if (!is_array($moduleImagesConfig)) {
                continue;
            }

            foreach ($moduleImagesConfig as $imageUploaderModule => $imageUploaderOptions) {
                $imageUploaderModules[] = $moduleName.'.'.$imageUploaderModule;
            }
        }

        return $imageUploaderModules;
    }

    public static function removeAllOldTempImages()
    {
        $olderThan = time() - Core_Config::conf('media.img_uploader.clean_after');
        $olderThan = date('Y-m-d H:i:s', $olderThan);
        $tempImagesList = self::getDAO()->getAllTempImagesByDate($olderThan);
        foreach ($tempImagesList as $temImageData) {
            try {
                self::getInstance($temImageData['uploader_module'])->removeTempImage($temImageData);
            } catch (Exception $e) {
            }
        }
    }

    public function removeTempImage($tempImageData)
    {
        $filePath = $this->getTempDirectory().DS.$tempImageData['file_path'];
        if (is_file($filePath)) {
            unlink($filePath);
        }
        self::getDAO()->removeTempImage($tempImageData['id']);
    }
}
