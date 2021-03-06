<?php

namespace UIS\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exception\HttpResponseException;
use Response;
use Input;
use UIS\Mvf\ValidationManager;
use UIS\Mvf\ValidationResult;
use Carbon\Carbon;

abstract class BaseRequest extends FormRequest
{
    protected $dataKey = null;

    protected $dataToValidate = null;

    protected $validatedData = null;

    protected $validatorInstance = null;

    /**
     * @var \UIS\Mvf\ValidationResult
     */
    protected $validationResult = null;

    public function getValidatedData()
    {
        return $this->validatedData;
    }

    /**
     * @return \UIS\Mvf\ValidationResult
     */
    public function getValidationResult()
    {
        return $this->validationResult;
    }

    /*************************************************************************************************************/
    /*************************************************************************************************************/
    /*************************************************************************************************************/

    /******************************************************************************************************************/

    public function validate()
    {
        $validatorInstance = $this->createValidatorInstance();
        $this->validationResult = $validationResult = $validatorInstance->validate();

        $this->processed();

        if ($validationResult->isValid()) {
            $this->success();
        } else {
            $this->failed();
        }

        if ($validationResult->isValid()) {
            $this->validatedData = $validatorInstance->getData();

            return true;
        }

        $this->validationFailed($validationResult);

//        if ( ! $this->passesAuthorization())
//        {
//            $this->failedAuthorization();
//        }
//        elseif ( ! $instance->passes())
//        {
//
//        }
    }

    /**
     * @return ValidationResult
     */
    public function validateData()
    {
        $validatorInstance = $this->createValidatorInstance();
        $this->validationResult = $validationResult = $validatorInstance->validate();

        $this->processed();

        if ($validationResult->isValid()) {
            $this->success();
        } else {
            $this->failed();
        }

//        if ($validationResult->isValid()) {
            $this->validatedData = $validatorInstance->getData();
//        }
        return $this->validationResult;
    }

    protected function success()
    {
    }

    protected function processed()
    {
    }

    protected function failed()
    {
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  ValidationManager  $validator
     * @return mixed
     */
    protected function validationFailed(ValidationResult $result)
    {
        throw new HttpResponseException($this->createResponse($result));
    }

    /**
     * Determine if the request passes the authorization check.
     *
     * @return bool
     */
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            return $this->container->call([$this, 'authorize']);
        }

        return false;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return mixed
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException($this->forbiddenResponse());
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createResponse($validationResult)
    {
        return $this->api(
            null,
            null,
            $validationResult
        );
    }

    public function api($status, $data = null, $validationResult = null, $httpStatusCode = 200, $httpHeaders = [])
    {
        if ($status === null) {
            $status = $validationResult->isValid() ? 'OK' : 'INVALID_DATA';
        }
        $result = [
            'status' => $status,
        ];

        if ($validationResult !== null) {
            $result['errors'] = $validationResult;
        }

        if ($data !== null) {
            $result = array_merge($result, $data);
        }

        Carbon::setToJsonFormat(Carbon::ISO8601);

        try {
            //            $httpHeaders = array();
//            uis_dump($result, $httpStatusCode, $httpHeaders);
            return Response::json($result, $httpStatusCode, $httpHeaders);
        } catch (Exception $e) {
            uis_dump($e);
        }
    }

    protected function createValidatorInstance()
    {
        $data = $this->getDataToValidate();
        $this->validatorInstance = new ValidationManager($data, $this->rules());

        return $this->validatorInstance;
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \UIS\Mvf\ValidationManager
     */
    protected function getValidatorInstance()
    {
        if ($this->validatorInstance === null) {
            return $this->createValidatorInstance();
        }

        return $this->validatorInstance;
//        $factory = $this->container->make('Illuminate\Validation\Factory');
//
//        if (method_exists($this, 'validator'))
//        {
//            return $this->container->call([$this, 'validator'], compact('factory'));
//        }
//
//        return $factory->make(
//            $this->sanitizeInput(), $this->container->call([$this, 'rules']), $this->messages()
//        );
    }

    public function getDataToValidate()
    {
        if ($this->dataKey !== null) {
            return Input::get($this->dataKey, []);
        }

        return Input::get();
    }

    public function authorize()
    {
        // Only allow logged in users
        // return \Auth::check();
        // Allows all users in
        return true;
    }

    // OPTIONAL OVERRIDE
    public function forbiddenResponse()
    {
        // Optionally, send a custom response on authorize failure
        // (default is to just redirect to initial page with errors)
        //
        // Can return a response, a view, a redirect, or whatever else
        return Response::make('Permission denied foo!', 403);
    }

    // OPTIONAL OVERRIDE
//    public function response()
//    {
    // If you want to customize what happens on a failed validation,
    // override this method.
    // See what it does natively here:
    // https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Http/FormRequest.php
//    }
}
