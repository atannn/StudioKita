<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_payment_accounts')) {
            return;
        }

        Schema::table('tenant_payment_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_payment_accounts', 'dp_enabled')) {
                $table->boolean('dp_enabled')->default(true)->after('is_active');
            }

            if (!Schema::hasColumn('tenant_payment_accounts', 'dp_allowed_percents')) {
                $table->text('dp_allowed_percents')->nullable()->after('dp_enabled');
            }
        });

        DB::table('tenant_payment_accounts')
            ->whereNull('dp_allowed_percents')
            ->update([
                'dp_allowed_percents' => json_encode([10, 25, 40, 60]),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenant_payment_accounts')) {
            return;
        }

        Schema::table('tenant_payment_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_payment_accounts', 'dp_allowed_percents')) {
                $table->dropColumn('dp_allowed_percents');
            }

            if (Schema::hasColumn('tenant_payment_accounts', 'dp_enabled')) {
                $table->dropColumn('dp_enabled');
            }
        });
    }
};

