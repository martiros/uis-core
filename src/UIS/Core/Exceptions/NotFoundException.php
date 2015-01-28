<?php
namespace UIS\Core\Exceptions;

class NotFoundException extends CatchableException
{
    public function getMessageData()
    {
        return [
            'title' => trans('uis_core.error.not_found.title'),
            'body' => trans('uis_core.error.not_found.body')
        ];
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
