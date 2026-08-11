<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class WaveStudiosAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'wave-studios')
            ->orWhere('nama', 'Wave Studios')
            ->first();

        if (!$tenant) {
            $this->command?->warn('Tenant Wave Studios tidak ditemukan. Seeder dibatalkan.');
            return;
        }

        $rooms = Room::where('tenants_idTenant', $tenant->idTenant)->get();
        $services = Service::where('tenants_idTenant', $tenant->idTenant)->get();

        if ($rooms->isEmpty() || $services->isEmpty()) {
            $this->command?->warn('Rooms/Services untuk Wave Studios belum lengkap. Seeder dibatalkan.');
            return;
        }

        $customers = User::where('role', 'customer')->limit(5)->get();
        if ($customers->isEmpty()) {
            for ($i = 1; $i <= 5; $i++) {
                $customers->push(User::create([
                    'name' => "Customer {$i}",
                    'email' => "customer{$i}@studiokita.test",
                    'password' => Hash::make('password123'),
                    'role' => 'customer',
                    'status' => true,
                ]));
            }
        }

        $slotTimes = ['09:00:00', '11:00:00', '13:00:00', '15:00:00', '17:00:00', '19:00:00'];
        $statusPool = ['completed', 'completed', 'completed', 'confirmed', 'pending', 'cancelled'];
        $dailyCounts = [0, 0, 1, 1, 1, 2, 2, 3];

        $start = Carbon::now()->subYear()->startOfDay();
        $end = Carbon::now()->endOfDay();
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $todayBookings = Arr::random($dailyCounts);
            $created = 0;
            $attempts = 0;

            while ($created < $todayBookings && $attempts < ($todayBookings * 5 + 5)) {
                $attempts++;
                $service = $services->random();
                $roomsForType = $rooms->where('tipe_ruangan', $service->tipe_service);
                $room = $roomsForType->isNotEmpty() ? $roomsForType->random() : $rooms->random();

                $startTime = Arr::random($slotTimes);
                $endTime = Carbon::parse($startTime)->addMinutes((int) $service->durasi_menit)->format('H:i:s');

                $exists = Jadwal::where('tenants_idTenant', $tenant->idTenant)
                    ->where('rooms_idrooms', $room->idrooms)
                    ->where('tanggal', $date->toDateString())
                    ->where('waktu_mulai', $startTime)
                    ->where('waktu_selesai', $endTime)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $status = Arr::random($statusPool);
                $jadwalStatus = $status === 'cancelled' ? 'available' : 'booked';

                $jadwal = Jadwal::create([
                    'rooms_idrooms' => $room->idrooms,
                    'tanggal' => $date->toDateString(),
                    'waktu_mulai' => $startTime,
                    'waktu_selesai' => $endTime,
                    'status' => $jadwalStatus,
                    'tenants_idTenant' => $tenant->idTenant,
                ]);

                $bookingTime = $date->copy()->setTime(rand(8, 20), rand(0, 59));

                Booking::create([
                    'tanggal_booking' => $bookingTime,
                    'total_harga' => $service->getPriceForDate($date->toDateString()),
                    'status' => $status,
                    'tenants_idTenant' => $tenant->idTenant,
                    'rooms_idrooms' => $room->idrooms,
                    'service_idservice' => $service->idservice,
                    'Jadwal_idJadwal' => $jadwal->idJadwal,
                    'user_id' => $customers->random()->id,
                ]);

                $created++;
            }
        }

        $this->command?->info('Simulasi booking Wave Studios (1 tahun terakhir) berhasil dibuat.');
    }
}
