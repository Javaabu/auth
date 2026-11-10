<?php

namespace Javaabu\Auth\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * After a correct password from a user with two-factor authentication: the code page
 */
class TwoFactorChallengeResponse implements Responsable
{
    public function toResponse($request): Response
    {
        $type = UserTypes::fromRequest($request) ?? UserTypes::default();

        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => true, 'redirect' => $type->route('two-factor.login')]);
        }

        return redirect()->to($type->route('two-factor.login'));
    }
}
