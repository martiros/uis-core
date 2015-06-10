<?php

namespace UIS\Core\Exceptions;

use Config;

class MaintenanceModeException extends Exception
{
    private $defaultRetryAfter = 3600; // 1 hour

    public function getMessageData()
    {
        return [
            'title' => trans('uis_core.error.maintenance_mode.title'),
            'body' => trans('uis_core.error.maintenance_mode.body'),
        ];
    }

    public function getHttpHeaders()
    {
        $retryAfter = Config::get('app.maintenance.retry_after');
        if ($retryAfter === null) {
            $retryAfter = $this->defaultRetryAfter;
        } elseif ($retryAfter === false) {
            return [];
        }

        return [
            'Retry-After' => $retryAfter,
        ];
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
