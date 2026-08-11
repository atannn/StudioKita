<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facilities') && !Schema::hasColumn('facilities', 'total_quantity')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->unsignedInteger('total_quantity')->default(1)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facilities') && Schema::hasColumn('facilities', 'total_quantity')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->dropColumn('total_quantity');
            });
        }
    }
};
