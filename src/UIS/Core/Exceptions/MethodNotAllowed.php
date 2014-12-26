<?php

namespace UIS\Core\Exceptions;

class MethodNotAllowed extends Exception
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
        return 'METHOD_NOT_ALLOWED';
    }

    public function getHttpStatusCode()
    {
        return 405;
    }

    public function logException()
    {
        return false;
    }
}
