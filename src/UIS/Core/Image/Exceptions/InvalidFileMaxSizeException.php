<?php namespace UIS\Core\Image\Exceptions;

use UIS\Core\Exceptions\NotFoundException;

class InvalidFileMaxSizeException extends NotFoundException
{
    protected $errorKey = null;

    public function setErrorKey($errorKey)
    {
        $this->errorKey = $errorKey;
    }

    private $maxSize = null;
    private $fileSize = null;

    public function setMaxSize($maxSize){
        $this->maxSize = $maxSize;
    }

    public function getMaxSize(){
        return $this->maxSize;
    }

    public function setFileSize($fileSize){
        $this->fileSize = $fileSize;
    }

    public function getFileSize(){
        return $this->fileSize;
    }
}
