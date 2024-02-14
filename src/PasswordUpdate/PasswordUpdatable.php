<?php

namespace Javaabu\Auth\PasswordUpdate;

trait PasswordUpdatable
{
    /**
     * Whether a password update is required
     *
     * @return boolean
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
