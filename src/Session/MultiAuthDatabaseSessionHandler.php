<?php

namespace Javaabu\Auth\Session;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Session\DatabaseSessionHandler;

class MultiAuthDatabaseSessionHandler extends DatabaseSessionHandler
{
    protected function addUserInformation(&$payload)
    {
        if ($this->container->bound(Guard::class) && ($user_type = $this->userType())) {
            $payload['user_id'] = $this->userId();
            $payload['user_type'] = $this->userType();
        }

        return $this;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return mixed
     */
    protected function userType(): ?string
    {
        $user = $this->container->make(Guard::class)->user();

        if ($user instanceof Model) {
            return $user->getMorphClass();
        }

        return null;
    }
}
