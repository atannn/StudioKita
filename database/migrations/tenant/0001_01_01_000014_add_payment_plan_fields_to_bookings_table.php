<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_scheme', 20)->default('full');
            $table->unsignedTinyInteger('dp_percent')->nullable();
            $table->string('payment_state', 20)->default('unpaid');
            $table->decimal('paid_amount', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_scheme',
                'dp_percent',
                'payment_state',
                'paid_amount',
            ]);
        });
    }
};

