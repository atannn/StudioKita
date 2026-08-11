<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_databases')) {
            return;
        }

        DB::statement("ALTER TABLE `tenant_databases` MODIFY `driver` VARCHAR(20) NOT NULL DEFAULT 'mysql'");

        if (Schema::hasColumn('tenant_databases', 'database_path')) {
            DB::statement("ALTER TABLE `tenant_databases` MODIFY `database_path` VARCHAR(255) NULL");
            DB::statement("UPDATE `tenant_databases` SET `database_path` = NULL WHERE `driver` = 'mysql'");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenant_databases')) {
            return;
        }

        if (Schema::hasColumn('tenant_databases', 'database_path')) {
            DB::statement("UPDATE `tenant_databases` SET `database_path` = '' WHERE `database_path` IS NULL");
            DB::statement("ALTER TABLE `tenant_databases` MODIFY `database_path` VARCHAR(255) NOT NULL");
        }

        DB::statement("ALTER TABLE `tenant_databases` MODIFY `driver` VARCHAR(20) NOT NULL DEFAULT 'mysql'");
    }
};
