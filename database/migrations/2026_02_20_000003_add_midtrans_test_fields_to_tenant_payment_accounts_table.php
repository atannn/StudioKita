<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_payment_accounts', function (Blueprint $table) {
            $table->boolean('midtrans_last_test_success')
                ->default(false)
                ->after('is_active');
            $table->timestamp('midtrans_last_tested_at')
                ->nullable()
                ->after('midtrans_last_test_success');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_payment_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_last_test_success',
                'midtrans_last_tested_at',
            ]);
        });
    }
};

