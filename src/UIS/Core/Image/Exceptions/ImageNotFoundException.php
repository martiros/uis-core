<?php namespace UIS\Core\Image\Exceptions;

use UIS\Core\Exceptions\NotFoundException;

class ImageNotFoundException extends NotFoundException
{
    protected $errorKey = null;

    public function setErrorKey($errorKey)
    {
        $this->errorKey = $errorKey;
    }
}
