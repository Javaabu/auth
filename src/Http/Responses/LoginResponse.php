<?php

namespace Javaabu\Auth\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * After a successful login: the intended page or the type's home. Rebind it in the container to change it.
 */
class LoginResponse implements Responsable
{
    public function toResponse($request): Response
    {
        $type = UserTypes::fromRequest($request) ?? UserTypes::default();
        $redirect = redirect()->intended($type->homeUrl($type->auth()->user()));

        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false, 'redirect' => $redirect->getTargetUrl()]);
        }

        return $redirect;
    }
}
