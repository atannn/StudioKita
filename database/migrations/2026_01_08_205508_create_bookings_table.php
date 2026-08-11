<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('idbooking');
            $table->dateTime('tanggal_booking');
            $table->decimal('total_harga', 12, 2);
            $table->enum('status', ['pending','confirmed','cancelled','completed'])->default('pending');

            $table->unsignedBigInteger('tenants_idTenant');
            $table->foreign('tenants_idTenant')
                ->references('idTenant')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('rooms_idrooms');
            $table->foreign('rooms_idrooms')
                ->references('idrooms')
                ->on('rooms')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('service_idservice');
            $table->foreign('service_idservice')
                ->references('idservice')
                ->on('services')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('Jadwal_idJadwal');
            $table->foreign('Jadwal_idJadwal')
                ->references('idJadwal')
                ->on('jadwals')
                ->cascadeOnDelete();

            // ✅ user standar laravel
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Opsional: 1 jadwal hanya boleh 1 booking
            $table->unique(['jadwal_idjadwal']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
