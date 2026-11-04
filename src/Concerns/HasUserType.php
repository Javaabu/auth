<?php

namespace Javaabu\Auth\Concerns;

use Javaabu\Auth\Notifications\ResetPassword;
use Javaabu\Auth\Notifications\VerifyEmail;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use RuntimeException;

/**
 * Ties a user model to its user type, so password reset and verification emails link to that type's routes
 */
trait HasUserType
{
    public function userType(): UserType
    {
        return UserTypes::for($this)
            ?? throw new RuntimeException('No user type uses the model ' . static::class . '. Add it to javaabu-auth.user_types.');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token, $this->userType()->name()));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail($this->userType()->name()));
    }
}
