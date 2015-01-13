<?php

namespace UIS\Core\Controllers\Api;

use UIS\Core\Controllers\BaseController;
use Illuminate\Foundation\Application;
use Lang, Config;

class UtilController extends BaseController
{
    public function xsrfRefresh()
    {
        return $this->api(
            'OK',
            array(
                'token' => csrf_token()
            )
        );
    }
}
