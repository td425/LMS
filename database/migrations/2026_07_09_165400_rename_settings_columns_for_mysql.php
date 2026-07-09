<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (Schema::hasColumn('settings', 'key') && ! Schema::hasColumn('settings', 'name')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `settings` CHANGE `key` `name` VARCHAR(255) NOT NULL');
            } else {
                Schema::table('settings', function ($table) {
                    $table->renameColumn('key', 'name');
                });
            }
        }

        if (Schema::hasColumn('settings', 'value') && ! Schema::hasColumn('settings', 'content')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `settings` CHANGE `value` `content` TEXT NULL');
            } else {
                Schema::table('settings', function ($table) {
                    $table->renameColumn('value', 'content');
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (Schema::hasColumn('settings', 'name') && ! Schema::hasColumn('settings', 'key')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `settings` CHANGE `name` `key` VARCHAR(255) NOT NULL');
            } else {
                Schema::table('settings', function ($table) {
                    $table->renameColumn('name', 'key');
                });
            }
        }

        if (Schema::hasColumn('settings', 'content') && ! Schema::hasColumn('settings', 'value')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `settings` CHANGE `content` `value` TEXT NULL');
            } else {
                Schema::table('settings', function ($table) {
                    $table->renameColumn('content', 'value');
                });
            }
        }
    }
};
