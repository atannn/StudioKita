<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->string('source_type', 20)
                ->default('manual')
                ->after('service_idservice');

            $table->unsignedBigInteger('schedule_template_id')
                ->nullable()
                ->after('source_type');

            $table->index('schedule_template_id', 'jadwals_schedule_template_id_index');
            $table->index(['source_type', 'schedule_template_id'], 'jadwals_source_template_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropIndex('jadwals_schedule_template_id_index');
            $table->dropIndex('jadwals_source_template_lookup_index');
            $table->dropColumn(['source_type', 'schedule_template_id']);
        });
    }
};
