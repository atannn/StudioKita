<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('idbooking');
            $table->dateTime('tanggal_booking');
            $table->decimal('total_harga', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->unsignedBigInteger('tenants_idTenant');
            $table->unsignedBigInteger('rooms_idrooms');
            $table->unsignedBigInteger('service_idservice');
            $table->unsignedBigInteger('Jadwal_idJadwal');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('rooms_idrooms')
                ->references('idrooms')
                ->on('rooms')
                ->cascadeOnDelete();

            $table->foreign('service_idservice')
                ->references('idservice')
                ->on('services')
                ->cascadeOnDelete();

            $table->foreign('Jadwal_idJadwal')
                ->references('idJadwal')
                ->on('jadwals')
                ->cascadeOnDelete();

            $table->index('user_id');
            $table->index('Jadwal_idJadwal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

