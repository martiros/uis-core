<?php

namespace UIS\Core\Exceptions;

class Exception extends \Exception
{
    public function getMessageData()
    {
        return null;
    }

    public function getHttpHeaders()
    {
        return null;
    }

    public function getStatus()
    {
        return null;
    }

    public function getHttpStatusCode()
    {
        return null;
    }

    public function useDefault()
    {
        return true;
    }

    public function logException()
    {
        return true;
    }
}
