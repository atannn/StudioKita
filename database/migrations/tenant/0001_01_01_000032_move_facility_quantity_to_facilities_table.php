<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('facilities', 'quantity')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->default(1)->after('status');
            });
        }

        if (Schema::hasColumn('facilities', 'total_quantity')) {
            DB::table('facilities')->update([
                'quantity' => DB::raw('total_quantity'),
            ]);

            Schema::table('facilities', function (Blueprint $table) {
                $table->dropColumn('total_quantity');
            });
        }

        if (Schema::hasTable('room_facilities') && Schema::hasColumn('room_facilities', 'quantity')) {
            $assignedQuantities = DB::table('room_facilities')
                ->select('facilities_idfasiltas', DB::raw('SUM(quantity) as assigned_quantity'))
                ->groupBy('facilities_idfasiltas')
                ->get();

            foreach ($assignedQuantities as $assigned) {
                DB::table('facilities')
                    ->where('idfasiltas', $assigned->facilities_idfasiltas)
                    ->where('quantity', '<', (int) $assigned->assigned_quantity)
                    ->update(['quantity' => (int) $assigned->assigned_quantity]);
            }

            Schema::table('room_facilities', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('room_facilities') && !Schema::hasColumn('room_facilities', 'quantity')) {
            Schema::table('room_facilities', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->default(1)->after('tenants_idTenant');
            });
        }

        if (!Schema::hasColumn('facilities', 'total_quantity')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->unsignedInteger('total_quantity')->default(1)->after('status');
            });
        }

        if (Schema::hasColumn('facilities', 'quantity')) {
            DB::table('facilities')->update([
                'total_quantity' => DB::raw('quantity'),
            ]);

            Schema::table('facilities', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};
