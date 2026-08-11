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
        Schema::create('photos', function (Blueprint $table) {
            $table->id('idfoto');
            $table->string('foto_path', 255);
            $table->string('caption', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->dateTime('uploaded_at')->nullable();
            $table->tinyInteger('status')->default(1);

            $table->unsignedBigInteger('tenants_idTenant');
            $table->foreign('tenants_idTenant')
                ->references('idTenant')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->timestamps();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
