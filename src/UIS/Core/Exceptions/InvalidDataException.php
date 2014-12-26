<?php

namespace UIS\Core\Exceptions;

class InvalidDataException extends Exception
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
        return 'BAD_REQUEST';
    }

    public function getHttpStatusCode()
    {
        return 400;
    }

    public function logException()
    {
        return false;
    }

    public function useDefault()
    {
        return true;
    }
}
