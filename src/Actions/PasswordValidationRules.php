<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /** @return array<int, mixed> */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }
}
