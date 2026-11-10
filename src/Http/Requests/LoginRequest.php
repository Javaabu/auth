<?php

namespace Javaabu\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Javaabu\Auth\UserTypes;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $type = UserTypes::fromRequest($this) ?? UserTypes::default();

        return [
            $type->usernameColumn() => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }
}
