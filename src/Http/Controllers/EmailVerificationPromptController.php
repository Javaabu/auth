<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): View|Responsable|RedirectResponse
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($type->homeUrl($user));
        }

        return $this->render($request, 'verify-email', [
            'email' => $user->getEmailForVerification(),
        ]);
    }
}
