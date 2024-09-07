<?php

namespace Javaabu\Auth\PasswordUpdate;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Events\PasswordChanged;

trait PasswordUpdatable
{
    protected bool $password_reset = false;

    public static function bootPasswordUpdatable()
    {
        static::updated(function ($user) {
            /** @var Model $user */
            if ($user->isDirty('password') && $user->shouldLogPasswordChanged()) {
                event(new PasswordChanged($user, auth()->user()));
            }
        });
    }

    public function shouldLogPasswordChanged(): bool
    {
        return ! $this->password_reset;
    }

    public function markAsPasswordReset()
    {
        $this->password_reset = true;
    }

    /**
     * Whether a password update is required
     *
     * @return bool
     */
    public function requiresPasswordUpdate()
    {
        return $this->require_password_update;
    }

    /**
     * The password update redirect url
     *
     * @return string
     */
    public function passwordUpdateRedirectUrl()
    {
        return $this->homeUrl();
    }

    /**
     * The clear require password update
     *
     * @return void
     */
    public function clearRequirePasswordUpdate()
    {
        $this->require_password_update = false;
    }
}
