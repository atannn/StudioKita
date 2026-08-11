<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_date_harian_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenants_idTenant');
            $table->unsignedBigInteger('rooms_idrooms');
            $table->unsignedBigInteger('service_idservice')->nullable();
            $table->date('tanggal');
            $table->enum('override_type', ['add_slot', 'block_interval', 'close_day']);
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('rooms_idrooms')
                ->references('idrooms')
                ->on('rooms')
                ->cascadeOnDelete();

            $table->foreign('service_idservice')
                ->references('idservice')
                ->on('services')
                ->nullOnDelete();

            $table->index(['tenants_idTenant', 'tanggal'], 'schedule_date_harian_overrides_tenant_date_index');
            $table->index(['rooms_idrooms', 'tanggal'], 'schedule_date_harian_overrides_room_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_date_harian_overrides');
    }
};
