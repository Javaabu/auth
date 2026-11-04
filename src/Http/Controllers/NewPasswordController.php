<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Contracts\ResetsUserPasswords;
use Symfony\Component\HttpFoundation\Response;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View|Responsable
    {
        $type = $this->userType($request);

        return $this->render($request, 'reset-password', [
            'token' => $token,
            'email' => (string) $request->query($type->usernameColumn(), ''),
        ]);
    }

    public function store(Request $request, ResetsUserPasswords $resetter): Response
    {
        $type = $this->userType($request);
        $username = $type->usernameColumn();

        $request->validate([
            'token' => ['required', 'string'],
            $username => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $type->findByUsername((string) $request->input($username));

        $status = $type->broker()->reset(
            [
                $username => $user?->getAttribute($username) ?? trim((string) $request->input($username)),
                'password' => $request->input('password'),
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $request->input('token'),
            ],
            function ($user) use ($request, $resetter, $type) {
                $resetter->reset($type, $user, $request->all());

                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                $username => __('This password reset link is invalid or has expired.'),
            ]);
        }

        $message = __('Your password has been reset.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return redirect()->to($type->route('login'))->with('status', $message);
    }
}
