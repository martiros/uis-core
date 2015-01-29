<?php namespace UIS\Core\Controllers\Api;

use UIS\Core\Controllers\BaseController;
use UIS\Core\File\Uploader;

class FileUploaderController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function upload()
    {
        $imageUploader = new Uploader();
        $uploadedFileId = $imageUploader->saveToTemp();

        return $this->api(
            'OK',
            [
                'file_id' => $uploadedFileId
            ]
        );
    }
}

