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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('idpayments');
            $table->enum('method', ['Midtrans','Cash']);
            $table->string('midtrans_order_id', 100)->nullable();
            $table->string('midtrans_transaction_id', 100)->nullable();
            $table->enum('status', ['pending','success','failed','expired','cancelled'])->default('pending');
            $table->dateTime('payment_time')->nullable();
            $table->dateTime('expires_time')->nullable();
            $table->enum('payment_type', ['dp','full','remaining'])->default('full');
            $table->decimal('amount', 12, 2)->default(0);

            $table->unsignedBigInteger('tenants_idTenant');
            $table->foreign('tenants_idTenant')
                ->references('idTenant')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('booking_idbooking');
            $table->foreign('booking_idbooking')
                ->references('idbooking')
                ->on('bookings')
                ->cascadeOnDelete();

            $table->timestamps();


            // Opsional: 1 booking maksimal 1 payment (kalau tidak ada cicilan)
            // Kalau nanti mau DP+pelunasan (lebih dari 1), jangan pakai unique ini.
            // $table->unique(['booking_idbooking']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
