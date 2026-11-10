<?php

namespace Javaabu\Auth\Schema;

use Illuminate\Database\Schema\Blueprint;
use Javaabu\Auth\Enums\UserStatuses;

/**
 * Columns for user tables, for the migrations of each user type
 */
class UserSchema
{
    /**
     * Every column the package's traits use
     */
    public static function columns(Blueprint $table, bool $password_required = true, bool $email_required = true): void
    {
        $table->id();
        $table->string('name');

        $email = $table->string('email')->unique();

        if (! $email_required) {
            $email->nullable();
        }

        $table->timestamp('email_verified_at')->nullable();

        $password = $table->string('password');

        if (! $password_required) {
            $password->nullable();
        }

        $table->rememberToken();
        $table->timestamps();

        static::accountColumns($table);
        static::twoFactorColumns($table);

        $table->softDeletes();
    }

    /**
     * HasAccountStatus, LocksAfterFailedLogins and RequiresPasswordUpdate, plus the last login
     */
    public static function accountColumns(Blueprint $table): void
    {
        $table->timestamp('last_login_at')->nullable()->index();
        $table->unsignedInteger('login_attempts')->nullable();
        $table->boolean('require_password_update')->default(false);
        $table->string('status')->default(UserStatuses::PENDING->value)->index();
    }

    /**
     * TwoFactorAuthenticatable, in laravel/fortify's format
     */
    public static function twoFactorColumns(Blueprint $table): void
    {
        $table->text('two_factor_secret')->nullable();
        $table->text('two_factor_recovery_codes')->nullable();
        $table->timestamp('two_factor_confirmed_at')->nullable();
    }

    /**
     * A sessions table for the `multi_auth_database` driver
     */
    public static function sessionColumns(Blueprint $table): void
    {
        $table->string('id')->primary();
        $table->nullableMorphs('user');
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    }
}
