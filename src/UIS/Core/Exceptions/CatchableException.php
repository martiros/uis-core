<?php

namespace UIS\Core\Exceptions;

class CatchableException extends Exception
{
    public function getMessageData()
    {
        return false;
    }

    public function getStatus()
    {
        return;
    }

    public function getHttpStatusCode()
    {
        return 200;
    }

    public function useDefault()
    {
        return true;
    }

    public function logException()
    {
        return false;
    }
}
