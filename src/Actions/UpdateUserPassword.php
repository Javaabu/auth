<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Javaabu\Auth\Contracts\RequiresPasswordUpdate;
use Javaabu\Auth\Contracts\UpdatesUserPasswords;
use Javaabu\Auth\Events\PasswordUpdated;
use Javaabu\Auth\UserType;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    public function update(UserType $type, Model $user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', "current_password:{$type->guardName()}"],
            'password' => [...$this->passwordRules(), 'different:current_password'],
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ]);

        if ($user instanceof RequiresPasswordUpdate) {
            $user->clearRequirePasswordUpdate();
        }

        $user->save();

        PasswordUpdated::dispatch($user);
    }
}
