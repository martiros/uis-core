<?php

namespace UIS\Core\Exceptions;

use App;
use Auth;
use Config;
use Exception as PHPException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Request;
use Log;
use Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use UIS\Core\Controllers\BaseControllerTrait;
use UIS\Core\Exceptions\Exception as UISException;

class Handler extends ExceptionHandler
{
    use BaseControllerTrait;

    protected $exception = null;

    protected $httpCodeToStatusMap = [
        '400' => 'BAD_REQUEST',
        '401' => 'NOT_AUTH',
        '403' => 'FORBIDDEN',
        '404' => 'NOT_FOUND',
        '405' => 'METHOD_NOT_ALLOWED',
        '500' => 'APP_ERROR',
        '503' => 'MAINTENANCE_MODE',
    ];

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException',
    ];

    public function report(PHPException $e)
    {
        // do not report, check is needed report on redering
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, PHPException $e)
    {
        //        $e = new NotSupportedVersionException();

        $exceptionData = $this->getExceptionData($e);
        $data = [];
        if (!empty($exceptionData['message'])) {
            $data = [
                'message' => $exceptionData['message'],
            ];
        }

        if (Config::get('app.debug')) {
            $data['debug_info'] = $this->getLogData($e);
        }

        if ($exceptionData['log'] === true) {
            $this->logException($e);
        }

        $data = !empty($data) ? $data : null;
        if (!$this->isApiCall()) {
            return $this->showErrorPage(
                $exceptionData['status'],
                $data,
                $exceptionData['validation_result'],
                $exceptionData['http_status_code'],
                $exceptionData['http_headers']
            );
        }

        return $this->api(
            $exceptionData['status'],
            $data,
            $exceptionData['validation_result'],
            $exceptionData['http_status_code'],
            $exceptionData['http_headers']
        );
    }

    protected function showErrorPage($status, $data, $validationResult, $httpStatusCode, $httpHeaders)
    {
        $title = isset($data['message']['title']) ? $data['message']['title'] : trans('uis_core.error.unknown.title');
        $body = isset($data['message']['body']) ? $data['message']['body'] : trans('uis_core.error.unknown.body');

        return response()->view(
            'errors.base',
            ['title' => $title, 'body' => $body],
            $httpStatusCode,
            $httpHeaders
        );
    }

    protected function logException(PHPException $e)
    {
        $logData = $this->getLogData($e);
        Log::critical('Log ID-'.md5(uniqid(true).microtime(true)), $logData);
    }

    protected function getLogData(PHPException $e)
    {
        return [
            'message' => get_class($e).': '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_url' => Request::url(),
            'request_method' => Request::method(),
            'get' => $_GET,
            'post' => $_POST,
            'headers' => Request::header(),
            'cookies' => $_COOKIE,
            'session' => Session::all(),
            'ip' => Request::getClientIp(),
            'user_logged_in' => Auth::check(),
        ];
    }

    protected function getExceptionData(PHPException $ex)
    {
        $exceptionData = [];
        $useDefault = true;
        $exceptionData['validation_result'] = null;
        if ($ex instanceof UISException) {
            $exceptionData['message'] = $ex->getMessageData();
            if ($exceptionData['message'] === null) {
                unset($exceptionData['message']);
            }

            $exceptionData['validation_result'] = $ex->getValidationResult();

            $exceptionData['http_headers'] = $ex->getHttpHeaders();
            if ($exceptionData['http_headers'] === null) {
                unset($exceptionData['http_headers']);
            }

            $exceptionData['http_status_code'] = $ex->getHttpStatusCode();
            if ($exceptionData['http_status_code'] === null) {
                unset($exceptionData['http_status_code']);
            }

            $exceptionData['status'] = $ex->getStatus();

            $exceptionData['log'] = $ex->logException();
            if ($exceptionData['log'] === null) {
                unset($exceptionData['log']);
            }

            $useDefault = $ex->useDefault();
        } else {
            if ($ex instanceof HttpException) {
                $exceptionData['http_status_code'] = $ex->getStatusCode();
            }
        }

        if ($useDefault) {
            $exceptionDefaultData = $this->getExceptionDefaultData($ex);
            $exceptionData = array_merge($exceptionDefaultData, $exceptionData);
        }
        $exceptionData['status'] = !empty($exceptionData['status']) ? $exceptionData['status'] : 'APP_ERROR';
        if (App::environment('production') && $ex instanceof \Illuminate\Database\QueryException) {
            $errorCodes = $this->getErrorCodes();
            $exceptionData = $errorCodes['NOT_FOUND']['data'];
        }

        if (!isset($exceptionData['http_headers'])) {
            $exceptionData['http_headers'] = [];
        }

        if (!isset($exceptionData['log'])) {
            $exceptionData['log'] = false;
        }

        return $exceptionData;
    }

    protected function getExceptionDefaultData(PHPException $ex)
    {
        $errorCodes = $this->getErrorCodes();
        if ($ex instanceof HttpException) {
            $code = $ex->getStatusCode();
            if (isset($this->httpCodeToStatusMap[$code])) {
                return $errorCodes[$this->httpCodeToStatusMap[$code]]['data'];
            }
        }

        $exceptionData = null;
        foreach ($errorCodes as $errorOptions) {
            if (!isset($errorOptions['exception_types'])) {
                continue;
            }
            foreach ($errorOptions['exception_types'] as $exceptionType) {
                if (is_subclass_of($ex, $exceptionType) || is_a($ex, $exceptionType)) {
                    return $errorOptions['data'];
                }
            }
        }

        return $errorCodes['APP_ERROR']['data'];
    }

    private function getErrorCodes()
    {
        $errorCodes = [];

        $errorCodes['NOT_FOUND'] = [
            'data' => [
                'status' => 'NOT_FOUND',
                'http_status_code' => '404',
                'message' => [
                    'title' => trans('uis_core.error.not_found.title'),
                    'body' => trans('uis_core.error.not_found.body'),
                ],
            ],
            'exception_types' => [
                '\Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
                '\Illuminate\Database\Eloquent\ModelNotFoundException',
                '\UIS\Core\Exceptions\NotFoundException',
            ],
        ];

        $errorCodes['FORBIDDEN'] = [
            'data' => [
                'status' => 'FORBIDDEN',
                'http_status_code' => '403',
                'message' => [
                    'title' => trans('uis_core.error.forbidden.title'),
                    'body' => trans('uis_core.error.forbidden.body'),
                ],
            ],
            'exception_types' => [
                '\UIS\Core\Exceptions\PermissionDeniedException',
            ],
        ];

        $errorCodes['NOT_AUTH'] = [
            'data' => [
                'status' => 'NOT_AUTH',
                'http_status_code' => '401',
                'message' => [
                    'title' => trans('uis_core.error.not_auth.title'),
                    'body' => trans('uis_core.error.not_auth.body'),
                ],
            ],
            'exception_types' => [
                '\UIS\Core\Exceptions\NotAuthException',
            ],
        ];

        $errorCodes['BAD_REQUEST'] = [
            'data' => [
                'status' => 'BAD_REQUEST',
                'http_status_code' => '400',
                'message' => [
                    'title' => trans('uis_core.error.bad_request.title'),
                    'body' => trans('uis_core.error.bad_request.body'),
                ],
            ],
            'exception_types' => [
                '\UIS\Core\Exceptions\InvalidDataException',
            ],
        ];

        $maintenanceModeException = new MaintenanceModeException();
        $errorCodes['MAINTENANCE_MODE'] = [
            'data' => [
                'status' => 'MAINTENANCE_MODE',
                'http_status_code' => '503',
                'http_headers' => $maintenanceModeException->getHttpHeaders(),
                'message' => [
                    'title' => trans('uis_core.error.maintenance_mode.title'),
                    'body' => trans('uis_core.error.maintenance_mode.body'),
                ],
            ],
            'exception_types' => [
                '\UIS\Core\Exceptions\MaintenanceModeException',
            ],
        ];

        $errorCodes['TOKEN_MISMATCH'] = [
            'data' => [
                'status' => 'TOKEN_MISMATCH',
                'http_status_code' => '200',
                'message' => [
                    'title' => trans('uis_core.error.token_mismatch.title'),
                    'body' => trans('uis_core.error.token_mismatch.body'),
                ],
            ],
            'exception_types' => [
                '\Illuminate\Session\TokenMismatchException',
            ],
        ];

        $errorCodes['METHOD_NOT_ALLOWED'] = [
            'data' => [
                'status' => 'METHOD_NOT_ALLOWED',
                'http_status_code' => '405',
                'message' => [
                    'title' => trans('uis_core.error.method_not_allowed.title'),
                    'body' => trans('uis_core.error.method_not_allowed.body'),
                ],
            ],
            'exception_types' => [
                '\UIS\Core\Exceptions\MethodNotAllowed',
                '\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException',
            ],
        ];

        $errorCodes['APP_ERROR'] = [
            'data' => [
                'status' => 'APP_ERROR',
                'http_status_code' => '500',
                'message' => [
                    'title' => trans('uis_core.error.app_error.title'),
                    'body' => trans('uis_core.error.app_error.body'),
                ],
                'log' => true,
            ],
            'exception_types' => [
                '\Exception',
                '\UIS\Core\Exceptions\Exception',
            ],
        ];

        return $errorCodes;
    }
}
