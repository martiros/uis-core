<?php

namespace UIS\Core\Exceptions;

class PermissionDeniedException extends Exception
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
        return 'FORBIDDEN';
    }

    public function getHttpStatusCode()
    {
        return 403;
    }

    public function logException()
    {
        return false;
    }
}
