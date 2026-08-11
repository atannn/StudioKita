<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenants_idTenant');
            $table->unsignedBigInteger('rooms_idrooms');
            $table->unsignedBigInteger('service_idservice');
            $table->string('nama_template', 120);
            $table->enum('repeat_mode', ['daily', 'weekdays', 'weekends', 'custom_days'])->default('daily');
            $table->text('days_of_week_json')->nullable();
            $table->date('effective_start_date')->nullable();
            $table->date('effective_end_date')->nullable();
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('rooms_idrooms')
                ->references('idrooms')
                ->on('rooms')
                ->cascadeOnDelete();

            $table->foreign('service_idservice')
                ->references('idservice')
                ->on('services')
                ->cascadeOnDelete();

            $table->index(['tenants_idTenant', 'is_active'], 'schedule_templates_tenant_active_index');
            $table->index(['rooms_idrooms', 'service_idservice'], 'schedule_templates_room_service_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_templates');
    }
};
