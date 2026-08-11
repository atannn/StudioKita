<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','owner','customer','developer') NOT NULL DEFAULT 'customer'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET role='customer' WHERE role='developer'");
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','owner','customer') NOT NULL DEFAULT 'customer'");
        }
    }
};
