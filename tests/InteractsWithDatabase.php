<?php

namespace Javaabu\Auth\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait InteractsWithDatabase
{
    protected function runMigrations(): void
    {
        if (! RefreshDatabaseState::$migrated) {
            $this->dropAllTables();

            include_once __DIR__ . '/database/create_users_table.php';

            (new \CreateUsersTable())->up();

            RefreshDatabaseState::$migrated = true;
        }
    }

    /**
     * Drop all tables
     */
    protected function dropAllTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        $tables = DB::select('SHOW TABLES');
        foreach($tables as $table){
            $table = implode(json_decode(json_encode($table), true));
            Schema::drop($table);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }
}
