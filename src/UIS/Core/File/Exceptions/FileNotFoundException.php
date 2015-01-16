<?php namespace UIS\Core\File\Exceptions;

use UIS\Core\Exceptions\NotFoundException;

class FileNotFoundException extends NotFoundException
{
    public function getStatus()
    {
        return 'FILE_NOT_FOUND';
    }
}
