<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Javaabu\Auth\Contracts\CreatesNewUsers;
use Javaabu\Auth\UserType;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(UserType $type, array $input): Model
    {
        $user = $type->newModel();

        Validator::make($input, $this->rules($type, $user))->validate();

        $user->forceFill([
            'name' => $input['name'],
            $type->usernameColumn() => $this->normalizeUsername($input[$type->usernameColumn()]),
            'password' => Hash::make($input['password']),
        ])->save();

        return $user;
    }

    /** @return array<string, array<int, mixed>> */
    protected function rules(UserType $type, Model $user): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            $type->usernameColumn() => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique($user::class, $type->usernameColumn()),
            ],
            'password' => $this->passwordRules(),
        ];
    }

    protected function normalizeUsername(string $username): string
    {
        return mb_strtolower(trim($username));
    }
}
