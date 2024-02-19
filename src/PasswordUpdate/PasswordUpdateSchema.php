<?php

namespace Javaabu\Auth\PasswordUpdate;

use Illuminate\Database\Schema\Blueprint;

class PasswordUpdateSchema
{
    /**
     * Adds the columns needed for email verification
     *
     * @param  Blueprint  $table
     */
    public static function columns(Blueprint $table): void
    {
        $table->string('email')->index();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    }
}
