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

            include_once __DIR__ . '/../vendor/javaabu/activitylog/database/migrations/2024_02_05_223412_create_activity_log_table.php';
            include_once __DIR__ . '/../vendor/javaabu/activitylog/database/migrations/2024_02_05_223413_add_event_column_to_activity_log_table.php';
            include_once __DIR__ . '/../vendor/javaabu/activitylog/database/migrations/2024_02_05_223414_add_batch_uuid_column_to_activity_log_table.php';

            (new \CreateActivityLogTable)->up();
            (new \AddEventColumnToActivityLogTable)->up();
            (new \AddBatchUuidColumnToActivityLogTable)->up();

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
