<?php

namespace UIS\Core\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App;

abstract class BaseController extends Controller
{
    use BaseControllerTrait, DispatchesCommands; //, ValidatesRequests;

    private $validatedData;

    public function getValidatedData()
    {
        return $this->validatedData;
    }

    private $dataToValidate;

    public function getDataToValidate()
    {
        return $this->dataToValidate;
    }

    /**
     * @param string $requestClass
     * @return \UIS\Mvf\ValidationResult
     */
    public function validate($requestClass)
    {
        $request = App::build($requestClass);
        $validationResult = $request->validateData();
        $this->validatedData = $request->getValidatedData();
        $this->dataToValidate = $request->getDataToValidate();

        return $validationResult;

//        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);
//
//        if ($validator->fails())
//        {
//            $this->throwValidationException($request, $validator);
//        }
    }
}
