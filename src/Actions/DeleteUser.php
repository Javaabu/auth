<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Contracts\DeletesUsers;
use Javaabu\Auth\Events\AccountDeleted;
use Javaabu\Auth\UserType;

class DeleteUser implements DeletesUsers
{
    public function delete(UserType $type, Model $user): void
    {
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        $user->delete();

        AccountDeleted::dispatch($user);
    }
}
