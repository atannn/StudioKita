<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('idrooms');
            $table->string('nama_ruangan', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->integer('kapasitas')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->enum('tipe_ruangan', ['latihan', 'rekaman']);
            $table->string('foto_ruangan')->nullable();
            $table->unsignedBigInteger('tenants_idTenant');
            $table->timestamps();

            $table->unique(['tenants_idTenant', 'nama_ruangan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

