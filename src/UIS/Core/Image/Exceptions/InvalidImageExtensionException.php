<?php namespace UIS\Core\Image\Exceptions;

use UIS\Core\Exceptions\CatchableException;

class InvalidImageExtensionException extends CatchableException
{
    protected $errorKey = null;

    protected $allowedExtensions = null;

    public function setErrorKey($errorKey)
    {
        $this->errorKey = $errorKey;
    }

    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    public function setAllowedExtensions($allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
    }

}
