<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Jadwal;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class ScheduleAvailabilityService
{
    public function recomputeRoomDate(int $tenantId, int $roomId, string|CarbonInterface $date): void
    {
        $normalizedDate = $date instanceof CarbonInterface
            ? $date->toDateString()
            : $date;

        $jadwals = Jadwal::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->where('tanggal', $normalizedDate)
            ->orderBy('waktu_mulai')
            ->orderBy('waktu_selesai')
            ->get();

        if ($jadwals->isEmpty()) {
            return;
        }

        $activeBookings = Booking::query()
            ->with('jadwal')
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereHas('jadwal', function ($query) use ($tenantId, $roomId, $normalizedDate) {
                $query->where('tenants_idTenant', $tenantId)
                    ->where('rooms_idrooms', $roomId)
                    ->where('tanggal', $normalizedDate);
            })
            ->get();

        $bookedIntervals = [];
        $bookedIds = [];

        foreach ($activeBookings as $booking) {
            if (!$booking->jadwal) {
                continue;
            }

            $bookedIds[] = (int) $booking->jadwal->idJadwal;
            $bookedIntervals[] = [
                'start' => (string) $booking->jadwal->waktu_mulai,
                'end' => (string) $booking->jadwal->waktu_selesai,
                'jadwal_id' => (int) $booking->jadwal->idJadwal,
            ];
        }

        $structuralBlocks = $jadwals
            ->filter(fn (Jadwal $jadwal) => $jadwal->source_type === 'override' && $jadwal->service_idservice === null)
            ->map(fn (Jadwal $jadwal) => [
                'start' => (string) $jadwal->waktu_mulai,
                'end' => (string) $jadwal->waktu_selesai,
                'jadwal_id' => (int) $jadwal->idJadwal,
            ])
            ->values();

        foreach ($jadwals as $jadwal) {
            $targetStatus = $this->resolveStatus($jadwal, $bookedIds, $bookedIntervals, $structuralBlocks->all());

            if ((string) $jadwal->status !== $targetStatus) {
                $jadwal->forceFill([
                    'status' => $targetStatus,
                ])->save();
            }
        }
    }

    public function recomputeTenantUpcoming(int $tenantId, ?int $daysAhead = null): void
    {
        $start = today()->toDateString();
        $end = $this->resolveWindowEnd($daysAhead)->toDateString();

        Jadwal::query()
            ->select(['rooms_idrooms', 'tanggal'])
            ->where('tenants_idTenant', $tenantId)
            ->whereBetween('tanggal', [$start, $end])
            ->groupBy('rooms_idrooms', 'tanggal')
            ->orderBy('tanggal')
            ->orderBy('rooms_idrooms')
            ->get()
            ->each(fn (Jadwal $jadwal) => $this->recomputeRoomDate(
                $tenantId,
                (int) $jadwal->rooms_idrooms,
                (string) $jadwal->tanggal,
            ));
    }

    public function recomputeRoomUpcoming(int $tenantId, int $roomId, ?int $daysAhead = null): void
    {
        $start = today()->toDateString();
        $end = $this->resolveWindowEnd($daysAhead)->toDateString();

        Jadwal::query()
            ->select(['tanggal'])
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->whereBetween('tanggal', [$start, $end])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->each(fn (Jadwal $jadwal) => $this->recomputeRoomDate(
                $tenantId,
                $roomId,
                (string) $jadwal->tanggal,
            ));
    }

    public function hasActiveBookingOverlap(
        int $tenantId,
        int $roomId,
        string|CarbonInterface $date,
        string $waktuMulai,
        string $waktuSelesai,
        ?int $ignoreBookingId = null
    ): bool {
        $normalizedDate = $date instanceof CarbonInterface
            ? $date->toDateString()
            : $date;

        $query = Booking::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereHas('jadwal', function ($jadwalQuery) use ($tenantId, $roomId, $normalizedDate, $waktuMulai, $waktuSelesai) {
                $jadwalQuery->where('tenants_idTenant', $tenantId)
                    ->where('rooms_idrooms', $roomId)
                    ->where('tanggal', $normalizedDate)
                    ->where('waktu_mulai', '<', $waktuSelesai)
                    ->where('waktu_selesai', '>', $waktuMulai);
            });

        if ($ignoreBookingId) {
            $query->where('idbooking', '!=', $ignoreBookingId);
        }

        return $query->exists();
    }

    /**
     * @param array<int,int> $bookedIds
     * @param array<int,array{start:string,end:string,jadwal_id:int}> $bookedIntervals
     * @param array<int,array{start:string,end:string,jadwal_id:int}> $structuralBlocks
     */
    private function resolveStatus(
        Jadwal $jadwal,
        array $bookedIds,
        array $bookedIntervals,
        array $structuralBlocks
    ): string {
        if ($jadwal->source_type === 'override' && $jadwal->service_idservice === null) {
            return 'blocked';
        }

        if (in_array((int) $jadwal->idJadwal, $bookedIds, true)) {
            return 'booked';
        }

        foreach ($structuralBlocks as $block) {
            if ($this->intervalsOverlap(
                (string) $jadwal->waktu_mulai,
                (string) $jadwal->waktu_selesai,
                $block['start'],
                $block['end']
            )) {
                return 'blocked';
            }
        }

        foreach ($bookedIntervals as $interval) {
            if ((int) $interval['jadwal_id'] === (int) $jadwal->idJadwal) {
                continue;
            }

            if ($this->intervalsOverlap(
                (string) $jadwal->waktu_mulai,
                (string) $jadwal->waktu_selesai,
                $interval['start'],
                $interval['end']
            )) {
                return 'blocked';
            }
        }

        return 'available';
    }

    private function intervalsOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        return $startA < $endB && $endA > $startB;
    }

    private function resolveWindowEnd(?int $daysAhead = null): Carbon
    {
        $windowStart = today();

        if ($daysAhead !== null) {
            return $windowStart->copy()->addDays(max($daysAhead - 1, 0));
        }

        return $windowStart->copy()->addMonthNoOverflow()->endOfMonth();
    }
}
