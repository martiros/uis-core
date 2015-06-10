<?php

namespace UIS\Core\File\Exceptions;

use UIS\Core\Exceptions\NotFoundException;

class FileNotFoundException extends NotFoundException
{
    protected $errorKey = null;

    public function getStatus()
    {
        return 'FILE_NOT_FOUND';
    }

    public function setErrorKey($errorKey)
    {
        $this->errorKey = $errorKey;
    }
}
