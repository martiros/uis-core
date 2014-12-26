<?php

namespace UIS\Core\Controllers;

use Illuminate\Support\Facades\Response;
use UIS\Core\Exceptions\Exception;
use Carbon\Carbon;
class BaseController extends \Controller
{
    public function api($status, $data = null, $validationResult = null, $httpStatusCode = 200, $httpHeaders = array())
    {
        if ($status === null) {
            $status = $validationResult->isValid() ? 'OK' : 'INVALID_DATA';
        }
        $result = array(
            'status' => $status,
        );

        if ($validationResult !== null) {
            $result['errors'] = $validationResult;
        }

        if ($data !== null) {
            $result['data'] = $data;
        }

        Carbon::setToJsonFormat(Carbon::ISO8601);

        try {
//            $httpHeaders = array();
//            uis_dump($result, $httpStatusCode, $httpHeaders);
            return Response::json($result, $httpStatusCode, $httpHeaders);
        } catch (Exception $e){
            uis_dump($e);
        }
    }

    protected function isApiCall()
    {
        $request = $this->getRequest();
        $pathInfo = $request->getPathInfo();

        if ($this->request->isXmlHttpRequest() || strpos($pathInfo, '/api', 0) === 0 || strpos(
                $pathInfo,
                '/service',
                0
            ) === 0
        ) {
            return true;
        }
        return false;
    }
}