<?php

namespace Javaabu\Auth;

use Illuminate\Database\Schema\Blueprint;
use Javaabu\Auth\Enums\UserStatuses;

class UserSchema
{
    /**
     * Adds the columns needed for email verification
     */
    public static function columns(Blueprint $table, bool $password_required = true, bool $email_required = true): void
    {
        $table->id();
        $table->string('name');

        if ($email_required) {
            $table->string('email')->unique();
        } else {
            $table->string('email')->nullable()->unique();
        }

        $table->timestamp('email_verified_at')->nullable();

        if ($password_required) {
            $table->string('password');
        } else {
            $table->string('password')->nullable();
        }

        $table->rememberToken();
        $table->timestamps();

        // custom
        $table->timestamp('last_login_at')->nullable()->index();
        $table->unsignedInteger('login_attempts')->nullable();
        $table->boolean('require_password_update')->default(false);
        $table->nativeEnum('status', UserStatuses::class)->index();
        $table->string('new_email')->index()->nullable();
        $table->softDeletes();
    }
}
