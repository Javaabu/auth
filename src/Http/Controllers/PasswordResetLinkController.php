<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetLinkController extends Controller
{
    public function create(Request $request): View|Responsable
    {
        return $this->render($request, 'forgot-password');
    }

    public function store(Request $request): Response
    {
        $type = $this->userType($request);
        $username = $type->usernameColumn();

        $request->validate([
            $username => ['required', 'string', 'email'],
        ]);

        $status = $type->broker()->sendResetLink([
            $username => mb_strtolower(trim((string) $request->input($username))),
        ]);

        if ($status === PasswordBroker::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                $username => __('Please wait before retrying.'),
            ]);
        }

        // The same answer whether or not the account exists, so the form can't be used to find accounts
        $message = __('If an account with that email exists, we have emailed a password reset link.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
