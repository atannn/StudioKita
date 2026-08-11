<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_databases') || Schema::hasColumn('tenant_databases', 'database_name')) {
            return;
        }

        Schema::table('tenant_databases', function (Blueprint $table) {
            $table->string('database_name', 255)->nullable()->after('database_path');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenant_databases') || !Schema::hasColumn('tenant_databases', 'database_name')) {
            return;
        }

        Schema::table('tenant_databases', function (Blueprint $table) {
            $table->dropColumn('database_name');
        });
    }
};
