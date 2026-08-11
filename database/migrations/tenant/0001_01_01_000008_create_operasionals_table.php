<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasionals', function (Blueprint $table) {
            $table->id('idoperasional');
            $table->enum('day', ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->unsignedBigInteger('tenants_idTenant');
            $table->timestamps();

            $table->unique(['tenants_idTenant', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasionals');
    }
};

