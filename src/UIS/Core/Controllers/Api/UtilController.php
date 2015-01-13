<?php

namespace UIS\Core\Controllers\Api;

use Config;
use Lang;
use UIS\Core\Controllers\BaseController;

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
