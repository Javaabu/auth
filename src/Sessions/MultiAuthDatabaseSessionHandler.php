<?php

namespace Javaabu\Auth\Sessions;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Session\DatabaseSessionHandler;
use Javaabu\Auth\UserTypes;

/**
 * The database session driver, storing the user's morph type next to their id,
 * so sessions of different user types can be told apart (`multi_auth_database`)
 */
class MultiAuthDatabaseSessionHandler extends DatabaseSessionHandler
{
    protected function addUserInformation(&$payload)
    {
        $user = $this->authenticatedUser();

        if ($user) {
            $payload['user_id'] = $user->getKey();
            $payload['user_type'] = $user->getMorphClass();
        }

        return $this;
    }

    protected function authenticatedUser(): ?Model
    {
        if (! $this->container?->bound(AuthFactory::class)) {
            return null;
        }

        $auth = $this->container->make(AuthFactory::class);

        foreach (UserTypes::all() as $type) {
            $user = $auth->guard($type->guardName())->user();

            if ($user instanceof Model) {
                return $user;
            }
        }

        return null;
    }
}
