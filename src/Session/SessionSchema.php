<?php

namespace Javaabu\Auth\Session;

use Illuminate\Database\Schema\Blueprint;

class SessionSchema
{
    /**
     * Adds the columns needed for email verification
     */
    public static function columns(Blueprint $table): void
    {
        $table->string('id')->primary();
        $table->nullableMorphs('user');
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    }
}
