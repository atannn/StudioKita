<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'handled_by_user_id')) {
                $table->unsignedBigInteger('handled_by_user_id')->nullable()->after('booking_idbooking');
            }

            if (!Schema::hasColumn('payments', 'handled_at')) {
                $table->dateTime('handled_at')->nullable()->after('handled_by_user_id');
            }

            if (!Schema::hasColumn('payments', 'payment_note')) {
                $table->text('payment_note')->nullable()->after('handled_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_note')) {
                $table->dropColumn('payment_note');
            }

            if (Schema::hasColumn('payments', 'handled_at')) {
                $table->dropColumn('handled_at');
            }

            if (Schema::hasColumn('payments', 'handled_by_user_id')) {
                $table->dropColumn('handled_by_user_id');
            }
        });
    }
};

