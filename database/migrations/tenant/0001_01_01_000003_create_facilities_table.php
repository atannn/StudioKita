<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id('idfasiltas');
            $table->string('nama_fasilitas', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('tenants_idTenant');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};

