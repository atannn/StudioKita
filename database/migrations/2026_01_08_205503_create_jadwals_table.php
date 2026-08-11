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
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id('idJadwal');
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->enum('status', ['available','booked','blocked'])->default('available');

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

            $table->timestamps();


            // Opsional: cegah slot dobel untuk room yang sama
            $table->unique(['rooms_idrooms','tanggal','waktu_mulai','waktu_selesai']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwals');
    }
};
