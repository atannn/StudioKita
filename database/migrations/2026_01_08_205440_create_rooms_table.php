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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('idrooms');
            $table->string('nama_ruangan', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->integer('kapasitas')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->enum('tipe_ruangan', ['latihan','rekaman']);

            $table->unsignedBigInteger('tenants_idTenant');
            $table->foreign('tenants_idTenant')
                ->references('idTenant')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->timestamps();


            // Opsional: nama ruangan unik per tenant
            $table->unique(['tenants_idTenant','nama_ruangan']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
