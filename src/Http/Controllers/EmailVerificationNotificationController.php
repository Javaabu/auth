<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): Response
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        if (! $user instanceof MustVerifyEmail || $user->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse('', 204)
                : redirect()->intended($type->homeUrl($user));
        }

        $user->sendEmailVerificationNotification();

        $message = __('A new verification link has been sent to your email address.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message], 202);
        }

        return back()->with('status', $message);
    }
}
