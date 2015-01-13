<?php

namespace UIS\Core\Exceptions;

use \UIS\Core\Exceptions\CatchableException;

class NotFoundException extends CatchableException
{
    public function getMessageData()
    {
        return array( // @TODO: Translate this
            'title' => 'message title',
            'body' => 'message body'
        );
    }

    public function getStatus()
    {
        return 'NOT_FOUND';
    }

    public function getHttpStatusCode()
    {
        return 404;
    }

    public function logException()
    {
        return false;
    }
}
