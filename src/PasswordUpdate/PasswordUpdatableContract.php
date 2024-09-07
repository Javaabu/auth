<?php

namespace Javaabu\Auth\PasswordUpdate;

interface PasswordUpdatableContract
{
    public function shouldLogPasswordChanged(): bool;

    public function markAsPasswordReset();

    /**
     * Whether a password update is required
     *
     * @return bool
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
