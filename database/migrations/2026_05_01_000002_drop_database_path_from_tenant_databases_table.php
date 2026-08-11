<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_databases') || !Schema::hasColumn('tenant_databases', 'database_path')) {
            return;
        }

        Schema::table('tenant_databases', function (Blueprint $table) {
            $table->dropColumn('database_path');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenant_databases') || Schema::hasColumn('tenant_databases', 'database_path')) {
            return;
        }

        Schema::table('tenant_databases', function (Blueprint $table) {
            $table->string('database_path', 255)->nullable()->after('driver');
        });
    }
};
