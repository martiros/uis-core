<?php

namespace UIS\Core\Controllers\Api;

use UIS\Core\Controllers\BaseController;

class UtilController extends BaseController
{
    public function xsrfRefresh()
    {
        return $this->api(
            'OK',
            [
                'token' => csrf_token(),
            ]
        );
    }
}
