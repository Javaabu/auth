<?php

namespace Javaabu\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\User;
use Javaabu\Helpers\Media\AllowedMimeTypes;

abstract class UsersRequest extends FormRequest
{
    protected string $morph_class;

    protected string $table_name;

    public function authorize(): bool
    {
        return true;
    }

    protected function tableName(): string
    {
        return $this->table_name;
    }

    protected function morphClass(): string
    {
        return $this->morph_class;
    }

    abstract protected function editingCurrentUser(): bool;

    abstract protected function getRouteUser(): User;

    protected function getUserBeingEdited(): User
    {
        if ($this->editingCurrentUser()) {
            return $this->user();
        }

        return $this->getRouteUser();
    }

    protected function baseRules(bool $password_required = true, bool $email_required = true): array
    {
        $rules = [
            'email' => 'string|email|max:255|unique:'.$this->tableName(),
            'name' => 'string|max:255',
            'email_verified' => 'nullable|boolean',
            'password' => 'string|min:8|confirmed',
            'status' => [Rule::in(UserStatuses::getKeys())],
            'avatar' => AllowedMimeTypes::getValidationRule('image'),
            'action' => 'in:approve,ban,mark_pending,update_password',
            'require_password_update' => 'boolean',
        ];

        if (! $email_required) {
            $rules['email'] .= '|nullable';
        }

        // updating
        if ($user = $this->getUserBeingEdited()) {
            $rules['email'] .= ',email,'.$user->id;
            $rules['password'] .= '|required_if:action,update_password';

            // current password required
            if ($this->editingCurrentUser() && $user->password) {
                $rules['current_password'] = 'required_with:password|passcheck:'.$this->tableName().
                                             ',password,id,'.$user->id;
            }
        } else { // creation
            if ($password_required) {
                $rules['password'] .= '|required';
            } else {
                $rules['password'] .= '|nullable';
            }

            if ($email_required) {
                $rules['email'] .= '|required';
            }

            $rules['name'] .= '|required';
        }

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        return $rules;
    }
}
