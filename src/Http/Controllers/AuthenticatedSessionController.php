<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Javaabu\Auth\Actions\AttemptLogin;
use Javaabu\Auth\Http\Requests\LoginRequest;
use Javaabu\Auth\Http\Responses\LogoutResponse;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View|Responsable
    {
        $type = $this->userType($request);

        return $this->render($request, 'login', [
            'can_reset_password' => $type->hasFeature(UserType::PASSWORD_RESET) && $type->hasRoute('password.request'),
            'can_register' => $type->hasFeature(UserType::REGISTRATION) && $type->hasRoute('register'),
            'can_use_passkeys' => $type->hasFeature(UserType::PASSKEYS) && $type->hasRoute('passkey.login'),
        ]);
    }

    public function store(LoginRequest $request, AttemptLogin $attempt): Response
    {
        return $attempt($request, $this->userType($request));
    }

    public function destroy(Request $request): Response
    {
        $type = $this->userType($request);

        $type->auth()->logout();

        $this->endSession($request, $type);

        return app(LogoutResponse::class)->toResponse($request);
    }

    /**
     * Throw the session away unless another user type is still signed in on it
     */
    protected function endSession(Request $request, UserType $type): void
    {
        if (! $request->hasSession()) {
            return;
        }

        foreach (UserTypes::all() as $other) {
            if ($other->guardName() !== $type->guardName() && $other->auth()->check()) {
                $request->session()->regenerate();
                $request->session()->regenerateToken();

                return;
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
