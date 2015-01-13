<?php namespace UIS\Core\Exceptions;


use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use UIS\Core\Exceptions\Exception as UISException;
use UIS\Core\Controllers\BaseControllerTrait;
use App, Log, Session;
use Exception as PHPException;

class Handler extends ExceptionHandler
{
    use BaseControllerTrait;

    protected $exception = null;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(PHPException $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, PHPException $e)
    {
//        die( get_class($e) . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() );

        if ($this->isHttpException($e))
        {
            return $this->renderHttpException($e);
        }

//        $e = new \UIS\Core\Exceptions\MaintenanceModeException();
        $exceptionData = $this->getExceptionData($e);
        $data = [];
        if (!empty($exceptionData['message'])) {
            $data = array(
                'message' => $exceptionData['message']
            );
        }


        if (!App::environment('production') || true) {
            $data['debug_info'] = array(
                'message' => get_class($e) . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(),
                'request_url' => Request::url(),
                'request_method' => Request::method(),
                'get' => $_GET,
                'post' => $_POST,
                'cookies' => $_COOKIE,
                'headers' => Request::header()
            );
        }

        if ($exceptionData['log'] === true) {
            Log::critical(
                get_class($e),
                array(
                    'ex' => $e,
                    'get' => $_GET,
                    'post' => $_POST,
                    'server' => $_SERVER,
                    'cookie' => $_COOKIE,
                    'session' => Session::all(),
                )
            );
        }

//        $data = [
//            'context' => $context,
//            'user_id' => Auth::check() ? Auth::user()->id : 0,
//            'user_name' => Auth::check() ? Auth::user()->getDisplayName() : '',
//            'url' => Request::url(),
//            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
//            'ip' => Request::getClientIp(),
//            'count' => $count,
//            'code' => $code
//        ];
//
//        Log::error($error, $data);


//        uis_dump('B-'.get_class($e), $data, $exceptionData);

        if (intval($exceptionData['http_status_code'] / 100) === 5) {
            $exceptionData['http_status_code'] = '200';
        }

        $data = !empty($data) ? $data : null;
        return $this->api(
            $exceptionData['status'],
            $data,
            $exceptionData['validation_result'],
            $exceptionData['http_status_code'],
            $exceptionData['http_headers']
        );

        //              return parent::render($request, $e);
    }


    protected function getExceptionData(PHPException $ex)
    {
        $exceptionData = array();
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
            $exceptionData['http_headers'] = array();
        }

        if (!isset($exceptionData['log'])) {
            $exceptionData['log'] = false;
        }

        return $exceptionData;
    }

    protected function getExceptionDefaultData(PHPException $ex)
    {
        $errorCodes = $this->getErrorCodes();
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
        return $errorCodes['APP_ERROR'];
    }

    private function getErrorCodes()
    {
        $errorCodes = array();

        $errorCodes['NOT_FOUND'] = array(
            'data' => array(
                'status' => 'NOT_FOUND',
                'http_status_code' => '404',
                'message' => array( // @TODO: Translate this
                    'title' => 'message title',
                    'body' => 'message body'
                )
            ),
            'exception_types' => array(
                '\Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
                '\Illuminate\Database\Eloquent\ModelNotFoundException',
                '\UIS\Core\Exceptions\NotFoundException'
            ),
        );

        $errorCodes['FORBIDDEN'] = array(
            'data' => array(
                'status' => 'FORBIDDEN',
                'http_status_code' => '403',
                'message' => array( // @TODO: Translate this
                    'title' => 'message title',
                    'body' => 'message body'
                )
            ),
            'exception_types' => array(
                '\UIS\Core\Exceptions\PermissionDeniedException'
            )
        );

        $errorCodes['NOT_AUTH'] = array(
            'data' => array(
                'status' => 'NOT_AUTH',
                'http_status_code' => '401',
                'message' => array( // @TODO: Translate this
                    'title' => 'message title',
                    'body' => 'message body'
                )
            ),
            'exception_types' => array(
                '\UIS\Core\Exceptions\NotAuthException'
            )
        );

        $errorCodes['BAD_REQUEST'] = array(
            'data' => array(
                'status' => 'BAD_REQUEST',
                'http_status_code' => '400',
                'message' => array( // @TODO: Translate this
                    'title' => 'message title',
                    'body' => 'message body'
                )
            ),
            'exception_types' => array(
                '\UIS\Core\Exceptions\InvalidDataException'
            )
        );

        $errorCodes['MAINTENANCE_MODE'] = array(
            'data' => array(
                'status' => 'MAINTENANCE_MODE',
                'http_status_code' => '503',
                'message' => array( // @TODO: Translate this
                    'title' => 'message title',
                    'body' => 'message body'
                )
            ),
            'exception_types' => array(
                '\UIS\Core\Exceptions\MaintenanceModeException'
            )
        );

        $errorCodes['TOKEN_MISMATCH'] = array(
            'data' => array(
                'status' => 'TOKEN_MISMATCH',
                'http_status_code' => '200',
                'message' => array( // @TODO: Translate this
                    'title' => 'message title',
                    'body' => 'message body'
                )
            ),
            'exception_types' => array(
                '\Illuminate\Session\TokenMismatchException'
            )
        );

        $errorCodes['METHOD_NOT_ALLOWED'] = array(
            'data' => array(
                'status' => 'METHOD_NOT_ALLOWED',
                'http_status_code' => '405',
                'message' => array( // @TODO: Translate this
                    'title' => 'message title',
                    'body' => 'message body'
                )
            ),
            'exception_types' => array(
                '\UIS\Core\Exceptions\MethodNotAllowed',
                '\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException'
            )
        );

        $errorCodes['APP_ERROR'] = array(
            'data' => array(
                'status' => 'APP_ERROR',
                'http_status_code' => '500',
                'message' => array( // @TODO: Translate this
                    'title' => 'message title',
                    'body' => 'message body'
                ),
                'log' => true
            ),
            'exception_types' => array(
                '\Exception',
                '\UIS\Core\Exceptions\Exception',
            )
        );

        return $errorCodes;
    }

}
