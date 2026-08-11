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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id('idTenant');
            $table->string('nama', 45);
            $table->string('nama_pemilik', 45);
            $table->string('email', 45)->unique();
            $table->string('no_telp', 45);
            $table->string('alamat', 45)->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->date('createdAt')->useCurrent();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
