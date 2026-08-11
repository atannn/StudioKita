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
        Schema::create('operasionals', function (Blueprint $table) {
        $table->id('idoperasional');
        $table->enum('day', ['mon','tue','wed','thu','fri','sat','sun']);
        $table->time('open_time')->nullable();
        $table->time('close_time')->nullable();
        $table->boolean('is_closed')->default(false);

        $table->unsignedBigInteger('tenants_idTenant');
        $table->foreign('tenants_idTenant')
            ->references('idTenant')
            ->on('tenants')
            ->cascadeOnDelete();

        $table->timestamps();


            // Opsional tapi bagus: 1 tenant hanya punya 1 record per hari
            $table->unique(['tenants_idTenant','day']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasionals');
    }
};
