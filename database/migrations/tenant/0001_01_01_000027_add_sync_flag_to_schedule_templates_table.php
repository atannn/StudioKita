<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_templates', function (Blueprint $table) {
            $table->boolean('is_sync_enabled')
                ->default(true)
                ->after('is_active');

            $table->index(
                ['tenants_idTenant', 'is_active', 'is_sync_enabled'],
                'schedule_templates_tenant_active_sync_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('schedule_templates', function (Blueprint $table) {
            $table->dropIndex('schedule_templates_tenant_active_sync_index');
            $table->dropColumn('is_sync_enabled');
        });
    }
};
