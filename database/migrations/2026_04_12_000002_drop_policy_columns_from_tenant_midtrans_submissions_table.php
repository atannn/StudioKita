<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_midtrans_submissions')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('tenant_midtrans_submissions', 'dp_enabled') ? 'dp_enabled' : null,
            Schema::hasColumn('tenant_midtrans_submissions', 'dp_percent') ? 'dp_percent' : null,
            Schema::hasColumn('tenant_midtrans_submissions', 'cash_enabled') ? 'cash_enabled' : null,
            Schema::hasColumn('tenant_midtrans_submissions', 'cash_instruction') ? 'cash_instruction' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('tenant_midtrans_submissions', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenant_midtrans_submissions')) {
            return;
        }

        Schema::table('tenant_midtrans_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_midtrans_submissions', 'dp_enabled')) {
                $table->boolean('dp_enabled')->default(true);
            }

            if (!Schema::hasColumn('tenant_midtrans_submissions', 'dp_percent')) {
                $table->unsignedTinyInteger('dp_percent')->default(30);
            }

            if (!Schema::hasColumn('tenant_midtrans_submissions', 'cash_enabled')) {
                $table->boolean('cash_enabled')->default(false);
            }

            if (!Schema::hasColumn('tenant_midtrans_submissions', 'cash_instruction')) {
                $table->text('cash_instruction')->nullable();
            }
        });
    }
};
