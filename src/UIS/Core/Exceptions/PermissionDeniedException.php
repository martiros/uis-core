<?php namespace UIS\Core\Exceptions;

class PermissionDeniedException extends Exception
{
    public function getMessageData()
    {
        return [
            'title' => trans('uis_core.error.forbidden.title'),
            'body' => trans('uis_core.error.forbidden.body')
        ];
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
