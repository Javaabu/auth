<?php

namespace Javaabu\Auth\Tests\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Javaabu\Auth\Actions\StartAuthenticatedSession;
use Javaabu\Auth\Contracts\LoginMethod;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;

/**
 * A stand-in for javaabu/mobile-verification: logs in by email and a fixed code
 */
class CodeLoginMethod implements LoginMethod
{
    public function key(): string
    {
        return 'code';
    }

    public function label(): string
    {
        return __('Log in with a code');
    }

    public function routes(Router $router, UserType $type): void
    {
        $router->get('login/code', fn () => 'code form')->name('login.code');

        $router->post('login/code', function (Request $request, StartAuthenticatedSession $start_session) {
            $type = UserTypes::fromRequest($request);

            abort_unless($request->input('code') === '1234', 422);

            $user = $type->newModel()->newQuery()->where('email', $request->input('email'))->firstOrFail();

            return $start_session($request, $type, $user);
        })->name('login.code.store');
    }

    public function url(UserType $type): string
    {
        return $type->route('login.code');
    }
}
