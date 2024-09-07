<?php

namespace Javaabu\Auth\Tests\Feature\Http\Requests;

use Javaabu\Auth\User;

class UsersRequest extends \Javaabu\Auth\Http\Requests\UsersRequest
{
    protected string $morph_class = 'user';

    protected string $table_name = 'users';

    protected function editingCurrentUser(): bool
    {
        if ($this->user() instanceof User) {
            if ($user = $this->getRouteUser()) {
                return $user->id == $this->user()->id;
            } else {
                return if_route_pattern('account.*');
            }
        }

        return false;
    }

    protected function getRouteUser(): ?User
    {
        return $this->route('user');
    }
}
