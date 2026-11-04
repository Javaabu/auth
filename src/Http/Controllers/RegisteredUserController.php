<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Javaabu\Auth\Actions\LoginUser;
use Javaabu\Auth\Contracts\CreatesNewUsers;
use Javaabu\Auth\Http\Responses\RegisterResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View|Responsable
    {
        return $this->render($request, 'register');
    }

    public function store(Request $request, CreatesNewUsers $creator, LoginUser $login): Response
    {
        $type = $this->userType($request);

        $user = $creator->create($type, $request->all());

        event(new Registered($user));

        $login($request, $type, $user);

        return app(RegisterResponse::class)->toResponse($request);
    }
}
