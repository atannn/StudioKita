<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'snap_token')) {
                $table->string('snap_token', 255)->nullable()->after('midtrans_transaction_id');
            }

            if (!Schema::hasColumn('payments', 'snap_redirect_url')) {
                $table->string('snap_redirect_url', 255)->nullable()->after('snap_token');
            }

            if (!Schema::hasColumn('payments', 'raw_status')) {
                $table->string('raw_status', 50)->nullable()->after('status');
            }

            if (!Schema::hasColumn('payments', 'webhook_payload')) {
                $table->text('webhook_payload')->nullable()->after('raw_status');
            }

            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->dateTime('paid_at')->nullable()->after('payment_time');
            }

            if (!Schema::hasColumn('payments', 'failed_at')) {
                $table->dateTime('failed_at')->nullable()->after('paid_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = [
                'snap_token',
                'snap_redirect_url',
                'raw_status',
                'webhook_payload',
                'paid_at',
                'failed_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

