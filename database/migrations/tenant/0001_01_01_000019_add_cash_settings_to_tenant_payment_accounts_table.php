<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_payment_accounts')) {
            return;
        }

        Schema::table('tenant_payment_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_payment_accounts', 'cash_enabled')) {
                $table->boolean('cash_enabled')->default(false)->after('dp_percent');
            }

            if (!Schema::hasColumn('tenant_payment_accounts', 'cash_instruction')) {
                $table->text('cash_instruction')->nullable()->after('cash_enabled');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenant_payment_accounts')) {
            return;
        }

        Schema::table('tenant_payment_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_payment_accounts', 'cash_instruction')) {
                $table->dropColumn('cash_instruction');
            }

            if (Schema::hasColumn('tenant_payment_accounts', 'cash_enabled')) {
                $table->dropColumn('cash_enabled');
            }
        });
    }
};

