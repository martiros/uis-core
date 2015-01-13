<?php

namespace UIS\Core\Controllers;

use Carbon\Carbon;
use UIS\Core\Exceptions\Exception;
use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class BaseController extends Controller
{
    use BaseControllerTrait, DispatchesCommands, ValidatesRequests;
}