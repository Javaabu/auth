<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Javaabu\Helpers\Traits\ControllerHelpers;

class AuthBaseController extends Controller
{
    use AuthorizesRequests;
    use ControllerHelpers;
    use ValidatesRequests;
}
