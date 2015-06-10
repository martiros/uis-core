<?php

namespace UIS\Core\Exceptions;

class MethodNotAllowed extends Exception
{
    public function getMessageData()
    {
        return [
            'title' => trans('uis_core.error.method_not_allowed.title'),
            'body' => trans('uis_core.error.method_not_allowed.body'),
        ];
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
