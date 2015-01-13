<?php

namespace UIS\Core\Exceptions;

use UIS\Mvf\ValidationResult;

class Exception extends \Exception
{
    public function getMessageData()
    {
        return null;
    }

    public function getHttpHeaders()
    {
        return null;
    }

    public function getStatus()
    {
        return null;
    }

    public function getHttpStatusCode()
    {
        return null;
    }

    public function useDefault()
    {
        return true;
    }

    public function logException()
    {
        return true;
    }

    public function getValidationErrors()
    {

    }

    public function getErrors()
    {
        return null;
    }

    public function getValidationResult()
    {
        $errors = $this->getErrors();
        if (is_array($errors)) {
            $validationResult = new ValidationResult($errors);
            return $validationResult->errors();
        }
        return $errors;
    }
}
