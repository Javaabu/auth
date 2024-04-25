<?php

namespace Javaabu\Auth\Tests;

trait InteractsWithDatabase
{
    protected function runMigrations(): void
    {
        include_once __DIR__ . '/database/create_users_table.php';
        include_once __DIR__ . '/database/create_password_resets_table.php';
        include_once __DIR__ . '/database/create_sessions_table.php';

        (new \CreateUsersTable())->up();
        (new \CreatePasswordResetsTable())->up();
        (new \CreateSessionsTable())->up();

    }
}
