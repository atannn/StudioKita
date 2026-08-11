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
                if (Schema::hasColumn('schedule_templates', 'is_sync_enabled')) {
                    $table->dropIndex('schedule_templates_tenant_active_sync_index');
                    $table->dropColumn('is_sync_enabled');
                }
            });
        }

        if (Schema::hasTable('schedule_date_harian_overrides')) {
            Schema::table('schedule_date_harian_overrides', function (Blueprint $table) {
                if (Schema::hasColumn('schedule_date_harian_overrides', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schedule_templates')) {
            Schema::table('schedule_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('schedule_templates', 'is_sync_enabled')) {
                    $table->boolean('is_sync_enabled')
                        ->default(true)
                        ->after('is_active');

                    $table->index(
                        ['tenants_idTenant', 'is_active', 'is_sync_enabled'],
                        'schedule_templates_tenant_active_sync_index'
                    );
                }
            });
        }

        if (Schema::hasTable('schedule_date_harian_overrides')) {
            Schema::table('schedule_date_harian_overrides', function (Blueprint $table) {
                if (!Schema::hasColumn('schedule_date_harian_overrides', 'is_active')) {
                    $table->boolean('is_active')
                        ->default(true)
                        ->after('catatan');
                }
            });
        }
    }
};
