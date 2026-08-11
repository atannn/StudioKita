<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_facilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rooms_idrooms');
            $table->unsignedBigInteger('facilities_idfasiltas');
            $table->unsignedBigInteger('tenants_idTenant');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('rooms_idrooms')
                ->references('idrooms')
                ->on('rooms')
                ->cascadeOnDelete();

            $table->foreign('facilities_idfasiltas')
                ->references('idfasiltas')
                ->on('facilities')
                ->cascadeOnDelete();

            $table->unique(['rooms_idrooms', 'facilities_idfasiltas']);
            $table->index('tenants_idTenant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_facilities');
    }
};
