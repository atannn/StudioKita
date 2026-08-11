<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id('idservice');
            $table->string('nama_service', 100);
            $table->enum('tipe_service', ['latihan', 'rekaman']);
            $table->integer('durasi_menit');
            $table->decimal('weekday_price', 12, 2)->default(0);
            $table->decimal('weekend_price', 12, 2)->default(0);
            $table->string('deskripsi', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('tenants_idTenant');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
