<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_date_harian_override_id')
                ->nullable()
                ->after('schedule_template_id');

            $table->index(
                'schedule_date_harian_override_id',
                'jadwals_schedule_date_harian_override_id_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropIndex('jadwals_schedule_date_harian_override_id_index');
            $table->dropColumn('schedule_date_harian_override_id');
        });
    }
};
