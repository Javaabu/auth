<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Javaabu\Auth\Views\ViewRenderer;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    /**
     * The user type of the current route
     */
    protected function userType(Request $request): UserType
    {
        return UserTypes::fromRequest($request) ?? UserTypes::default();
    }

    /**
     * The signed-in user of the route's user type
     */
    protected function user(Request $request): ?Model
    {
        return $this->userType($request)->auth()->user();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function render(Request $request, string $page, array $data = [], string $layout = 'auth'): View|Responsable
    {
        return app(ViewRenderer::class)->render($this->userType($request), $page, $data, $layout);
    }

    protected function backWithStatus(string $status, ?string $message = null): mixed
    {
        return back()->with('status', $status)->with('status_message', $message);
    }
}
