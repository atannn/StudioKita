<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\ScheduleDateHarianOverride;
use App\Models\ScheduleTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleTemplateSyncService
{
    public function __construct(
        private readonly ScheduleAvailabilityService $availabilityService
    ) {
    }

    /**
     * @return array{created:int,updated:int,deleted:int,skipped:int}
     */
    public function syncTemplate(ScheduleTemplate $template, ?int $daysAhead = null): array
    {
        $windowStart = $this->resolveWindowStart();
        $windowEnd = $this->resolveWindowEnd($daysAhead);
        $shouldMaterialize = $template->is_active;

        $desiredDates = collect();
        $desiredSlotKeys = collect();
        if ($shouldMaterialize) {
            $cursor = $windowStart->copy();

            while ($cursor->lte($windowEnd)) {
                if ($template->appliesToDate($cursor)) {
                    $date = $cursor->toDateString();
                    $desiredDates->push($date);
                    $desiredSlotKeys->push($this->buildSlotKey($date, $template->waktu_mulai, $template->waktu_selesai));
                }

                $cursor->addDay();
            }
        }

        $stats = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
        ];

        $this->cleanupUndesiredFutureSlots($template, $desiredSlotKeys, $windowStart, $windowEnd, $stats);

        if (!$shouldMaterialize) {
            return $stats;
        }

        foreach ($desiredDates as $date) {
            $existing = Jadwal::query()
                ->where('tenants_idTenant', $template->tenants_idTenant)
                ->where('rooms_idrooms', $template->rooms_idrooms)
                ->where('tanggal', $date)
                ->where('waktu_mulai', $template->waktu_mulai)
                ->where('waktu_selesai', $template->waktu_selesai)
                ->first();

            if ($existing) {
                if ($existing->source_type === 'template' && (int) $existing->schedule_template_id === (int) $template->id) {
                    $existing->forceFill([
                        'service_idservice' => $template->service_idservice,
                        'source_type' => 'template',
                        'schedule_template_id' => $template->id,
                    ])->save();

                    $stats['updated']++;
                } else {
                    $stats['skipped']++;
                }

                continue;
            }

            Jadwal::query()->create([
                'tanggal' => $date,
                'waktu_mulai' => $template->waktu_mulai,
                'waktu_selesai' => $template->waktu_selesai,
                'status' => 'available',
                'tenants_idTenant' => $template->tenants_idTenant,
                'rooms_idrooms' => $template->rooms_idrooms,
                'service_idservice' => $template->service_idservice,
                'source_type' => 'template',
                'schedule_template_id' => $template->id,
            ]);

            $stats['created']++;
        }

        return $stats;
    }

    /**
     * @return array{created:int,updated:int,deleted:int,skipped:int}
     */
    public function syncTenantTemplates(int $tenantId, ?int $daysAhead = null): array
    {
        $totals = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
        ];

        ScheduleTemplate::query()
            ->where('tenants_idTenant', $tenantId)
            ->orderBy('id')
            ->get()
            ->each(function (ScheduleTemplate $template) use (&$totals, $daysAhead) {
                $result = $this->syncTemplate($template, $daysAhead);

                foreach ($totals as $key => $value) {
                    $totals[$key] += $result[$key] ?? 0;
                }
            });

        $overrideStats = $this->syncOverridesForTenant($tenantId, $daysAhead);
        foreach ($totals as $key => $value) {
            $totals[$key] += $overrideStats[$key] ?? 0;
        }

        $this->availabilityService->recomputeTenantUpcoming($tenantId, $daysAhead);

        return $totals;
    }

    /**
     * @return array{created:int,updated:int,deleted:int,skipped:int}
     */
    public function syncSingleTemplate(ScheduleTemplate $template, ?int $daysAhead = null): array
    {
        $totals = $this->syncTemplate($template, $daysAhead);

        $overrideStats = $this->syncOverridesForRoom(
            (int) $template->tenants_idTenant,
            (int) $template->rooms_idrooms,
            $daysAhead
        );

        foreach ($totals as $key => $value) {
            $totals[$key] += $overrideStats[$key] ?? 0;
        }

        $this->availabilityService->recomputeRoomUpcoming(
            (int) $template->tenants_idTenant,
            (int) $template->rooms_idrooms,
            $daysAhead
        );

        return $totals;
    }

    /**
     * @return array{created:int,updated:int,deleted:int,skipped:int}
     */
    public function syncOverridesForTenant(int $tenantId, ?int $daysAhead = null): array
    {
        $windowStart = $this->resolveWindowStart();
        $windowEnd = $this->resolveWindowEnd($daysAhead);
        $stats = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
        ];

        ScheduleDateHarianOverride::query()
            ->where('tenants_idTenant', $tenantId)
            ->whereBetween('tanggal', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->each(function (ScheduleDateHarianOverride $override) use (&$stats, $daysAhead) {
                $result = $this->syncOverride($override, $daysAhead);

                foreach ($stats as $key => $value) {
                    $stats[$key] += $result[$key] ?? 0;
                }
            });

        return $stats;
    }

    /**
     * @return array{created:int,updated:int,deleted:int,skipped:int}
     */
    public function syncOverridesForRoom(int $tenantId, int $roomId, ?int $daysAhead = null): array
    {
        $windowStart = $this->resolveWindowStart();
        $windowEnd = $this->resolveWindowEnd($daysAhead);
        $stats = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
        ];

        ScheduleDateHarianOverride::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->whereBetween('tanggal', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->each(function (ScheduleDateHarianOverride $override) use (&$stats, $daysAhead) {
                $result = $this->syncOverride($override, $daysAhead);

                foreach ($stats as $key => $value) {
                    $stats[$key] += $result[$key] ?? 0;
                }
            });

        return $stats;
    }

    /**
     * @return array{created:int,updated:int,deleted:int,skipped:int}
     */
    public function syncOverride(ScheduleDateHarianOverride $override, ?int $daysAhead = null): array
    {
        $windowStart = $this->resolveWindowStart();
        $windowEnd = $this->resolveWindowEnd($daysAhead);
        $stats = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
        ];

        $this->cleanupOverrideSlots($override, $stats);

        if ($override->tanggal->toDateString() < $windowStart->toDateString() || $override->tanggal->toDateString() > $windowEnd->toDateString()) {
            return $stats;
        }

        [$startTime, $endTime] = $this->resolveOverrideInterval($override);

        if ($override->override_type === 'add_slot') {
            $existing = Jadwal::query()
                ->where('tenants_idTenant', $override->tenants_idTenant)
                ->where('rooms_idrooms', $override->rooms_idrooms)
                ->where('tanggal', $override->tanggal->toDateString())
                ->where('waktu_mulai', $startTime)
                ->where('waktu_selesai', $endTime)
                ->first();

            if ($existing) {
                if ($existing->source_type === 'override'
                    && (int) $existing->schedule_date_harian_override_id === (int) $override->id) {
                    $existing->forceFill([
                        'status' => 'available',
                        'service_idservice' => $override->service_idservice,
                        'source_type' => 'override',
                        'schedule_template_id' => null,
                        'schedule_date_harian_override_id' => $override->id,
                    ])->save();

                    $stats['updated']++;
                } else {
                    $stats['skipped']++;
                }

                return $stats;
            }

            if ($this->hasFinalSlotOverlap(
                (int) $override->tenants_idTenant,
                (int) $override->rooms_idrooms,
                $override->tanggal->toDateString(),
                $startTime,
                $endTime
            )) {
                $stats['skipped']++;
                return $stats;
            }

            Jadwal::query()->create([
                'tanggal' => $override->tanggal->toDateString(),
                'waktu_mulai' => $startTime,
                'waktu_selesai' => $endTime,
                'status' => 'available',
                'tenants_idTenant' => $override->tenants_idTenant,
                'rooms_idrooms' => $override->rooms_idrooms,
                'service_idservice' => $override->service_idservice,
                'source_type' => 'override',
                'schedule_template_id' => null,
                'schedule_date_harian_override_id' => $override->id,
            ]);

            $stats['created']++;
            return $stats;
        }

        $deletedTemplates = $this->removeOverlappingTemplateSlots(
            (int) $override->tenants_idTenant,
            (int) $override->rooms_idrooms,
            $override->tanggal->toDateString(),
            $startTime,
            $endTime
        );
        $stats['deleted'] += $deletedTemplates;

        $existing = Jadwal::query()
            ->where('tenants_idTenant', $override->tenants_idTenant)
            ->where('source_type', 'override')
            ->where('schedule_date_harian_override_id', $override->id)
            ->where('tanggal', $override->tanggal->toDateString())
            ->first();

        if ($existing) {
            $existing->forceFill([
                'waktu_mulai' => $startTime,
                'waktu_selesai' => $endTime,
                'status' => 'blocked',
                'service_idservice' => null,
                'source_type' => 'override',
                'schedule_template_id' => null,
                'schedule_date_harian_override_id' => $override->id,
            ])->save();

            $stats['updated']++;
            return $stats;
        }

        Jadwal::query()->create([
            'tanggal' => $override->tanggal->toDateString(),
            'waktu_mulai' => $startTime,
            'waktu_selesai' => $endTime,
            'status' => 'blocked',
            'tenants_idTenant' => $override->tenants_idTenant,
            'rooms_idrooms' => $override->rooms_idrooms,
            'service_idservice' => null,
            'source_type' => 'override',
            'schedule_template_id' => null,
            'schedule_date_harian_override_id' => $override->id,
        ]);

        $stats['created']++;

        return $stats;
    }

    public function purgeTemplateFutureSlots(ScheduleTemplate $template, ?int $daysAhead = null): int
    {
        $windowStart = $this->resolveWindowStart();
        $windowEnd = $this->resolveWindowEnd($daysAhead);
        $deleted = 0;

        Jadwal::query()
            ->where('tenants_idTenant', $template->tenants_idTenant)
            ->where('source_type', 'template')
            ->where('schedule_template_id', $template->id)
            ->whereBetween('tanggal', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->get()
            ->each(function (Jadwal $jadwal) use (&$deleted) {
                $hasActiveBooking = Booking::query()
                    ->where('Jadwal_idJadwal', $jadwal->idJadwal)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->exists();

                if ($hasActiveBooking) {
                    return;
                }

                $jadwal->delete();
                $deleted++;
            });

        return $deleted;
    }

    public function syncWindowLabel(?int $daysAhead = null): string
    {
        if ($daysAhead !== null) {
            return $daysAhead.' hari';
        }

        return 'sampai akhir bulan depan';
    }

    public function purgeOverrideSlots(ScheduleDateHarianOverride $override): int
    {
        $deleted = 0;

        Jadwal::query()
            ->where('tenants_idTenant', $override->tenants_idTenant)
            ->where('source_type', 'override')
            ->where('schedule_date_harian_override_id', $override->id)
            ->get()
            ->each(function (Jadwal $jadwal) use (&$deleted) {
                $hasActiveBooking = Booking::query()
                    ->where('Jadwal_idJadwal', $jadwal->idJadwal)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->exists();

                if ($hasActiveBooking) {
                    return;
                }

                $jadwal->delete();
                $deleted++;
            });

        return $deleted;
    }

    /**
     * @param Collection<int,string> $desiredSlotKeys
     * @param array{created:int,updated:int,deleted:int,skipped:int} $stats
     */
    private function cleanupUndesiredFutureSlots(
        ScheduleTemplate $template,
        Collection $desiredSlotKeys,
        Carbon $windowStart,
        Carbon $windowEnd,
        array &$stats
    ): void {
        Jadwal::query()
            ->where('tenants_idTenant', $template->tenants_idTenant)
            ->where('source_type', 'template')
            ->where('schedule_template_id', $template->id)
            ->whereBetween('tanggal', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->get()
            ->each(function (Jadwal $jadwal) use (&$stats, $desiredSlotKeys) {
                $slotKey = $this->buildSlotKey($jadwal->tanggal, $jadwal->waktu_mulai, $jadwal->waktu_selesai);
                if ($desiredSlotKeys->contains($slotKey)) {
                    return;
                }

                $hasActiveBooking = Booking::query()
                    ->where('Jadwal_idJadwal', $jadwal->idJadwal)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->exists();

            if ($hasActiveBooking) {
                $stats['skipped']++;
                return;
            }

                $jadwal->delete();
                $stats['deleted']++;
            });
    }

    /**
     * @param array{created:int,updated:int,deleted:int,skipped:int} $stats
     */
    private function cleanupOverrideSlots(ScheduleDateHarianOverride $override, array &$stats): void
    {
        Jadwal::query()
            ->where('tenants_idTenant', $override->tenants_idTenant)
            ->where('source_type', 'override')
            ->where('schedule_date_harian_override_id', $override->id)
            ->get()
            ->each(function (Jadwal $jadwal) use (&$stats) {
                $hasActiveBooking = Booking::query()
                    ->where('Jadwal_idJadwal', $jadwal->idJadwal)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->exists();

                if ($hasActiveBooking) {
                    $stats['skipped']++;
                    return;
                }

                $jadwal->delete();
                $stats['deleted']++;
            });
    }

    /**
     * @return array{0:string,1:string}
     */
    private function resolveOverrideInterval(ScheduleDateHarianOverride $override): array
    {
        if ($override->override_type === 'close_day') {
            return ['00:00:00', '23:59:59'];
        }

        return [
            $this->normalizeTime($override->waktu_mulai),
            $this->normalizeTime($override->waktu_selesai),
        ];
    }

    private function hasFinalSlotOverlap(int $tenantId, int $roomId, string $date, string $startTime, string $endTime): bool
    {
        return Jadwal::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->where('tanggal', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('waktu_mulai', '<', $endTime)
                    ->where('waktu_selesai', '>', $startTime);
            })
            ->exists();
    }

    private function removeOverlappingTemplateSlots(int $tenantId, int $roomId, string $date, string $startTime, string $endTime): int
    {
        $deleted = 0;

        Jadwal::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->where('tanggal', $date)
            ->where('source_type', 'template')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('waktu_mulai', '<', $endTime)
                    ->where('waktu_selesai', '>', $startTime);
            })
            ->get()
            ->each(function (Jadwal $jadwal) use (&$deleted) {
                $hasActiveBooking = Booking::query()
                    ->where('Jadwal_idJadwal', $jadwal->idJadwal)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->exists();

                if ($hasActiveBooking) {
                    return;
                }

                $jadwal->delete();
                $deleted++;
            });

        return $deleted;
    }

    private function buildSlotKey(string $date, string $waktuMulai, string $waktuSelesai): string
    {
        return $date.'|'.$waktuMulai.'|'.$waktuSelesai;
    }

    private function resolveWindowStart(): Carbon
    {
        return today();
    }

    private function resolveWindowEnd(?int $daysAhead = null): Carbon
    {
        $windowStart = $this->resolveWindowStart();

        if ($daysAhead !== null) {
            return $windowStart->copy()->addDays(max($daysAhead - 1, 0));
        }

        return $windowStart->copy()->addMonthNoOverflow()->endOfMonth();
    }

    private function normalizeTime(?string $time): string
    {
        if (!$time) {
            return '00:00:00';
        }

        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
