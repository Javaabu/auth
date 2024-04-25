<?php

namespace Javaabu\Auth;

use Illuminate\Database\Schema\Blueprint;

class PasswordResetsSchema
{
    /**
     * Adds the columns needed for email verification
     */
    public static function columns(Blueprint $table): void
    {
        $table->string('email')->index();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    }
}
