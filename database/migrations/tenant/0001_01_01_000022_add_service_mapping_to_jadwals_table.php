<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->unsignedBigInteger('service_idservice')
                ->nullable()
                ->after('rooms_idrooms');

            $table->index('service_idservice', 'jadwals_service_idservice_index');
        });

        $jadwals = DB::table('jadwals')
            ->whereNull('service_idservice')
            ->get(['idJadwal', 'tenants_idTenant', 'rooms_idrooms']);

        foreach ($jadwals as $jadwal) {
            $room = DB::table('rooms')
                ->where('idrooms', $jadwal->rooms_idrooms)
                ->first(['tipe_ruangan']);

            if (!$room) {
                continue;
            }

            $serviceIds = DB::table('services')
                ->where('tenants_idTenant', $jadwal->tenants_idTenant)
                ->where('tipe_service', $room->tipe_ruangan)
                ->where('status', 1)
                ->orderBy('idservice')
                ->pluck('idservice');

            if ($serviceIds->count() === 1) {
                DB::table('jadwals')
                    ->where('idJadwal', $jadwal->idJadwal)
                    ->update([
                        'service_idservice' => (int) $serviceIds->first(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropIndex('jadwals_service_idservice_index');
            $table->dropColumn('service_idservice');
        });
    }
};
