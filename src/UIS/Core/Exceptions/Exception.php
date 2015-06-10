<?php

namespace UIS\Core\Exceptions;

use UIS\Mvf\ValidationResult;

class Exception extends \Exception
{
    public function getMessageData()
    {
        return;
    }

    public function getHttpHeaders()
    {
        return;
    }

    public function getStatus()
    {
        return;
    }

    public function getHttpStatusCode()
    {
        return;
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
        return;
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
