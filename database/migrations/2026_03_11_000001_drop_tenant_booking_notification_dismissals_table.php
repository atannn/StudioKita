<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tenant_booking_notification_dismissals');
    }

    public function down(): void
    {
        Schema::create('tenant_booking_notification_dismissals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('booking_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('tenant_id')
                ->references('idTenant')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->unique(
                ['user_id', 'tenant_id', 'booking_id'],
                'tenant_booking_notification_unique'
            );
            $table->index(['user_id', 'created_at'], 'tenant_booking_notification_user_created_index');
        });
    }
};

