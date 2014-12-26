<?php

namespace UIS\Core\Exceptions;

class ExpiredDataException extends CatchableException
{
    public function getStatus()
    {
        return 'EXPIRED_DATA';
    }
}
