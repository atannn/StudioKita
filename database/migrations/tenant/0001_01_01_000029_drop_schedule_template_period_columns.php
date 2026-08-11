<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schedule_templates')) {
            Schema::table('schedule_templates', function (Blueprint $table) {
                if (Schema::hasColumn('schedule_templates', 'effective_start_date')) {
                    $table->dropColumn('effective_start_date');
                }

                if (Schema::hasColumn('schedule_templates', 'effective_end_date')) {
                    $table->dropColumn('effective_end_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schedule_templates')) {
            Schema::table('schedule_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('schedule_templates', 'effective_start_date')) {
                    $table->date('effective_start_date')
                        ->nullable()
                        ->after('days_of_week_json');
                }

                if (!Schema::hasColumn('schedule_templates', 'effective_end_date')) {
                    $table->date('effective_end_date')
                        ->nullable()
                        ->after('effective_start_date');
                }
            });
        }
    }
};
