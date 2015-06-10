<?php

namespace UIS\Core\Locale\Exceptions;

use UIS\Core\Exceptions\CatchableException;

class LanguageNotFound extends CatchableException
{
    public function getStatus()
    {
        return 'LANGUAGE_NOT_FOUND';
    }

    public function getMessageData()
    {
        return [
            'title' => trans('uis_core.error.language_not_found.title'),
            'body' => trans('uis_core.error.language_not_found.body'),
        ];
    }
}
