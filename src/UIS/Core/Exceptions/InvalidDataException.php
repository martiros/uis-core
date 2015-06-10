<?php

namespace UIS\Core\Exceptions;

class InvalidDataException extends Exception
{
    public function getMessageData()
    {
        return [
            'title' => trans('uis_core.error.bad_request.title'),
            'body' => trans('uis_core.error.bad_request.body'),
        ];
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
