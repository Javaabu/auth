<?php

namespace Javaabu\Auth\Traits;

trait InteractsWithUserTypes
{
    public function userClass(): string
    {
        return get_class($this->userType());
    }
}
