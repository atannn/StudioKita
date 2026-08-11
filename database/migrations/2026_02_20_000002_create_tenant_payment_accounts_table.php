<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('provider', 30)->default('midtrans');
            $table->string('merchant_id', 100)->nullable();
            $table->text('midtrans_client_key_enc')->nullable();
            $table->text('midtrans_server_key_enc')->nullable();
            $table->boolean('is_production')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('idTenant')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payment_accounts');
    }
};

