<?php

namespace UIS\Core\Exceptions;

class NotAuthException extends Exception
{
    public function getMessageData()
    {
        return array(
            'title' => trans('uis_core.error.not_auth.title'),
            'body' => trans('uis_core.error.not_auth.body')
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
