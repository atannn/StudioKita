<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['Jadwal_idJadwal']);
            $table->dropUnique('bookings_jadwal_idjadwal_unique');
            $table->index('Jadwal_idJadwal');
            $table->foreign('Jadwal_idJadwal')
                ->references('idJadwal')
                ->on('jadwals')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['Jadwal_idJadwal']);
            $table->dropIndex('bookings_jadwal_idjadwal_index');
            $table->unique(['Jadwal_idJadwal'], 'bookings_jadwal_idjadwal_unique');
            $table->foreign('Jadwal_idJadwal')
                ->references('idJadwal')
                ->on('jadwals')
                ->cascadeOnDelete();
        });
    }
};
