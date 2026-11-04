<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Javaabu\Auth\Contracts\LocksAfterFailedLogins;
use Javaabu\Auth\UserType;

/**
 * Logs the user in on the type's guard and starts a fresh session
 */
class LoginUser
{
    public function __invoke(Request $request, UserType $type, Authenticatable $user, bool $remember = false): void
    {
        $type->auth()->login($user, $remember);

        if ($request->hasSession()) {
            $request->session()->regenerate();
            $request->session()->forget(StartAuthenticatedSession::CHALLENGE_SESSION_KEY);
        }

        if ($user instanceof LocksAfterFailedLogins) {
            $user->resetLoginAttempts();
        }

        // Only for tables that have the column (UserSchema::accountColumns())
        if ($user instanceof Model && array_key_exists('last_login_at', $user->getAttributes())) {
            $user->forceFill(['last_login_at' => now()])->saveQuietly();
        }
    }
}
