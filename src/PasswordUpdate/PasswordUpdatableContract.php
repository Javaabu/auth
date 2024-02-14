<?php

namespace Javaabu\Auth\PasswordUpdate;

interface PasswordUpdatableContract
{
    /**
     * Whether a password update is required
     *
     * @return boolean
     */
    public function requiresPasswordUpdate();

    /**
     * The password update redirect url
     *
     * @return string
     */
    public function passwordUpdateRedirectUrl();

    /**
     * The password update url
     *
     * @return string
     */
    public function passwordUpdateUrl();

    /**
     * The clear require password update
     *
     * @return void
     */
    public function clearRequirePasswordUpdate();
}
