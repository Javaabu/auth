<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Support\AccountState;

/**
 * Tells users whose account is pending, banned or locked why they can't continue
 */
class AccountStatusController extends Controller
{
    public function __invoke(Request $request): View|Responsable|RedirectResponse
    {
        $type = $this->userType($request);
        $user = $this->user($request);
        $message = AccountState::inactiveMessage($user);

        if ($message === null) {
            return redirect()->intended($type->homeUrl($user));
        }

        return $this->render($request, 'account-inactive', [
            'message' => $message,
        ]);
    }
}
