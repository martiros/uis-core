<?php

namespace UIS\Core\Exceptions;

use Config;

class MaintenanceModeException extends Exception
{
    private $defaultRetryAfter = 3600; // 1 hour

    public function getMessageData()
    {
        // @TODO - Translate
        return array(
            'title' => 'Service Temporarily Unavailable',
            'body' => 'Service Temporarily Unavailable',
        );
    }

    public function getHttpHeaders()
    {
        $retryAfter = Config::get('app.maintenance.retry_after');
        if ($retryAfter === null) {
            $retryAfter = $this->defaultRetryAfter;
        } else if ($retryAfter === false) {
            return array();
        }
        return array(
            'Retry-After' => $retryAfter
        );
    }

    public function getStatus()
    {
        return 'MAINTENANCE_MODE';
    }

    public function getHttpStatusCode()
    {
        return 503;
    }

    public function logException()
    {
        return false;
    }
}
