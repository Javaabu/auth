<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\UserType;
use Symfony\Component\HttpFoundation\Response;

class ConfirmablePasswordController extends Controller
{
    public function show(Request $request): View|Responsable
    {
        $type = $this->userType($request);

        return $this->render($request, 'confirm-password', [
            'can_use_passkeys' => $type->hasFeature(UserType::PASSKEYS)
                && $type->hasRoute('passkey.confirm')
                && method_exists($this->user($request), 'hasPasskeysEnabled')
                && $this->user($request)->hasPasskeysEnabled(),
        ]);
    }

    public function store(Request $request): Response
    {
        $type = $this->userType($request);

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $confirmed = $type->auth()->getProvider()->validateCredentials($this->user($request), [
            'password' => (string) $request->input('password'),
        ]);

        if (! $confirmed) {
            throw ValidationException::withMessages([
                'password' => __('The provided password is incorrect.'),
            ]);
        }

        RequirePassword::markConfirmed($request, $type);

        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        return redirect()->intended($type->homeUrl($this->user($request)));
    }
}
