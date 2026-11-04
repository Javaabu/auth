<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash): Response
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        if (! hash_equals((string) $user->getKey(), $id)) {
            throw new AuthorizationException(__('This verification link is invalid.'));
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            throw new AuthorizationException(__('This verification link is invalid.'));
        }

        if (! $user instanceof MustVerifyEmail) {
            return redirect()->intended($type->homeUrl($user));
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        return redirect()->intended($type->homeUrl($user))
            ->with('status', __('Your email address has been verified.'));
    }
}
