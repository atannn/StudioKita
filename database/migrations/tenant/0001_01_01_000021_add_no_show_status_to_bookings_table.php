<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        DB::statement(
            "ALTER TABLE `bookings` MODIFY `status` ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') NOT NULL DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        DB::table('bookings')
            ->where('status', 'no_show')
            ->update(['status' => 'cancelled']);

        DB::statement(
            "ALTER TABLE `bookings` MODIFY `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending'"
        );
    }
};
