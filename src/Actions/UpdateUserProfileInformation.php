<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Javaabu\Auth\Contracts\UpdatesUserProfileInformation;
use Javaabu\Auth\Events\ProfileUpdated;
use Javaabu\Auth\UserType;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(UserType $type, Model $user, array $input): void
    {
        $username = $type->usernameColumn();

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            $username => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique($user::class, $username)->ignore($user->getKey(), $user->getKeyName()),
            ],
        ])->validate();

        $new_username = mb_strtolower(trim($input[$username]));
        $changed_email = $new_username !== $user->getAttribute($username);

        $user->forceFill([
            'name' => $input['name'],
            $username => $new_username,
        ]);

        $must_verify = $changed_email
            && $username === 'email'
            && $user instanceof MustVerifyEmail
            && $type->hasFeature(UserType::EMAIL_VERIFICATION);

        if ($must_verify) {
            $user->forceFill(['email_verified_at' => null]);
        }

        $user->save();

        if ($must_verify) {
            $user->sendEmailVerificationNotification();
        }

        ProfileUpdated::dispatch($user);
    }
}
