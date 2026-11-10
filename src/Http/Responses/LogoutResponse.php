<?php

namespace Javaabu\Auth\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements Responsable
{
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        $type = UserTypes::fromRequest($request) ?? UserTypes::default();

        return redirect()->to($type->route('login'));
    }
}
