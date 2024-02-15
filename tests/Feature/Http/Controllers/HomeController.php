<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        echo $user->name;
    }
}
