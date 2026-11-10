<?php

namespace Javaabu\Auth\Tests\Fixtures;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Javaabu\Auth\Http\Controllers\AuthenticatedSessionController;

class CustomLoginController extends AuthenticatedSessionController
{
    public function create(Request $request): View|Responsable
    {
        return view('custom-login', ['type' => $this->userType($request)->name()]);
    }
}
