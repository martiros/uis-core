<?php

namespace UIS\Core\Exceptions;

class NotAuthException extends Exception
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
        return 'NOT_AUTH';
    }

    public function getHttpStatusCode()
    {
        return 401;
    }

    public function logException()
    {
        return false;
    }
}
