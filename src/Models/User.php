<?php

namespace Javaabu\Auth\Models;

class User extends \Javaabu\Auth\User
{
    /**
     * @inheritDoc
     */
    public function getAdminUrlAttribute(): string
    {
        return route('admin.users.show', $this);
    }

    /**
     * @inheritDoc
     */
    public function passwordUpdateUrl(): string
    {
        return route('admin.password.new-password');
    }

    /**
     * @inheritDoc
     */
    public function homeUrl(): string
    {
        return route('admin.home');
    }

    /**
     * @inheritDoc
     */
    public function loginUrl(): string
    {
        return route('admin.login');
    }

    /**
     * @inheritDoc
     */
    public function getRouteForPasswordReset(): string
    {
        return 'admin.password.reset';
    }

    /**
     * @inheritDoc
     */
    public function getRouteForEmailVerification(): string
    {
        return 'admin.verification.verify';
    }

    /**
     * @inheritDoc
     */
    public function inactiveNoticeUrl(): string
    {
        return route('admin.verification.notice');
    }
}
