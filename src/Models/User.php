<?php

namespace Javaabu\Auth\Models;

class User extends \Javaabu\Auth\User
{
    /**
     * @inheritDoc
     */
    public function getAdminUrlAttribute(): string
    {
        return route('users.show', $this);
    }

    /**
     * @inheritDoc
     */
    public function passwordUpdateUrl(): string
    {
        return route('password.new-password');
    }

    /**
     * @inheritDoc
     */
    public function homeUrl(): string
    {
        return route('home');
    }

    /**
     * @inheritDoc
     */
    public function loginUrl(): string
    {
        return route('login');
    }

    /**
     * @inheritDoc
     */
    public function getRouteForPasswordReset(): string
    {
        return 'password.reset';
    }

    /**
     * @inheritDoc
     */
    public function getRouteForEmailVerification(): string
    {
        return 'verification.verify';
    }

    /**
     * @inheritDoc
     */
    public function inactiveNoticeUrl(): string
    {
        return route('verification.notice');
    }
}
