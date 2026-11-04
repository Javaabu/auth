<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Javaabu\Auth\Contracts\LocksAfterFailedLogins;
use Javaabu\Auth\Contracts\RequiresPasswordUpdate;
use Javaabu\Auth\Contracts\ResetsUserPasswords;
use Javaabu\Auth\UserType;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function reset(UserType $type, Model $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ]);

        if ($user instanceof RequiresPasswordUpdate) {
            $user->clearRequirePasswordUpdate();
        }

        $user->save();

        if ($user instanceof LocksAfterFailedLogins) {
            $user->resetLoginAttempts();
        }
    }
}
