<?php

namespace UIS\Core\Locale;

/**
 * @see \UIS\Core\Locale\Language
 */
class LangFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'translator';
    }
}
