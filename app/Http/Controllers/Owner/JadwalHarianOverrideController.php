<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Room;
use App\Models\ScheduleDateHarianOverride;
use App\Models\Service;
use App\Support\ScheduleTemplateSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class JadwalHarianOverrideController extends Controller
{
    public function __construct(
        private readonly ScheduleTemplateSyncService $syncService
    ) {
    }

    public function create()
    {
        return view('owner.jadwal-harian-overrides.create', $this->sharedFormData());
    }

    public function store(Request $request)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;
        $validated = $this->validateOverride($request, $tenantId);

        ScheduleDateHarianOverride::query()->create(array_merge($validated, [
            'tenants_idTenant' => $tenantId,
        ]));

        $stats = $this->syncService->syncTenantTemplates($tenantId);

        return redirect()
            ->route('owner.jadwals.index')
            ->with('success', $this->buildSyncMessage('Pengaturan jadwal harian berhasil disimpan.', $stats));
    }

    public function edit(int $id)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $override = ScheduleDateHarianOverride::query()
            ->where('tenants_idTenant', $tenantId)
            ->findOrFail($id);

        return view('owner.jadwal-harian-overrides.edit', array_merge(
            $this->sharedFormData(),
            ['override' => $override]
        ));
    }

    public function update(Request $request, int $id)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $override = ScheduleDateHarianOverride::query()
            ->where('tenants_idTenant', $tenantId)
            ->findOrFail($id);

        $validated = $this->validateOverride($request, $tenantId, $override->id);

        $override->update($validated);
        $stats = $this->syncService->syncTenantTemplates($tenantId);

        return redirect()
            ->route('owner.jadwals.index')
            ->with('success', $this->buildSyncMessage('Pengaturan jadwal harian berhasil diperbarui.', $stats));
    }

    public function destroy(int $id)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $override = ScheduleDateHarianOverride::query()
            ->where('tenants_idTenant', $tenantId)
            ->findOrFail($id);

        $this->syncService->purgeOverrideSlots($override);
        $override->delete();

        $stats = $this->syncService->syncTenantTemplates($tenantId);

        return redirect()
            ->route('owner.jadwals.index')
            ->with('success', $this->buildSyncMessage('Pengaturan jadwal harian berhasil dihapus.', $stats));
    }

    private function sharedFormData(): array
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        return [
            'rooms' => Room::query()
                ->where('tenants_idTenant', $tenantId)
                ->where('status', 1)
                ->orderBy('nama_ruangan')
                ->get(),
            'services' => Service::query()
                ->where('tenants_idTenant', $tenantId)
                ->where('status', 1)
                ->orderBy('tipe_service')
                ->orderBy('nama_service')
                ->get(),
            'overrideTypes' => [
                'add_slot' => 'Tambah slot manual',
                'block_interval' => 'Blok interval jam',
                'close_day' => 'Tutup satu hari penuh',
            ],
        ];
    }

    private function validateOverride(Request $request, int $tenantId, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'rooms_idrooms' => 'required|integer',
            'service_idservice' => 'nullable|integer',
            'tanggal' => 'required|date',
            'override_type' => 'required|in:add_slot,block_interval,close_day',
            'waktu_mulai' => 'nullable|date_format:H:i',
            'waktu_selesai' => 'nullable|date_format:H:i|after:waktu_mulai',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $room = Room::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('idrooms', $validated['rooms_idrooms'])
            ->where('status', 1)
            ->firstOrFail();

        $overrideType = (string) $validated['override_type'];
        $serviceId = null;

        if ($overrideType === 'add_slot') {
            if (empty($validated['service_idservice'])) {
                throw ValidationException::withMessages([
                    'service_idservice' => 'Service wajib dipilih untuk tambah slot manual.',
                ]);
            }

            $service = Service::query()
                ->where('tenants_idTenant', $tenantId)
                ->where('idservice', $validated['service_idservice'])
                ->where('status', 1)
                ->firstOrFail();

            if ($service->tipe_service !== $room->tipe_ruangan) {
                throw ValidationException::withMessages([
                    'rooms_idrooms' => 'Tipe room harus sesuai dengan tipe service (rekaman/latihan).',
                ]);
            }

            if (empty($validated['waktu_mulai']) || empty($validated['waktu_selesai'])) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Waktu mulai dan selesai wajib diisi untuk tambah slot manual.',
                ]);
            }

            if ($this->resolveSlotDurationInMinutes($validated['waktu_mulai'], $validated['waktu_selesai']) !== (int) $service->durasi_menit) {
                throw ValidationException::withMessages([
                    'waktu_selesai' => 'Durasi slot harus sama dengan durasi service, yaitu '.$service->durasi_menit.' menit.',
                ]);
            }

            if ($this->hasAddSlotConflict(
                $tenantId,
                (int) $room->idrooms,
                $validated['tanggal'],
                $validated['waktu_mulai'],
                $validated['waktu_selesai'],
                $ignoreId
            )) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Slot manual duplikat atau bertabrakan dengan blok harian pada room dan tanggal yang sama.',
                ]);
            }

            $serviceId = (int) $service->idservice;
        }

        if ($overrideType === 'block_interval') {
            if (empty($validated['waktu_mulai']) || empty($validated['waktu_selesai'])) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Waktu mulai dan selesai wajib diisi untuk blok interval.',
                ]);
            }

            if ($this->hasBlockingConflict(
                $tenantId,
                (int) $room->idrooms,
                $validated['tanggal'],
                $validated['waktu_mulai'],
                $validated['waktu_selesai'],
                $ignoreId
            )) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Blok interval bentrok dengan booking aktif atau slot manual lain.',
                ]);
            }
        }

        if ($overrideType === 'close_day') {
            if ($this->hasCloseDayConflict(
                $tenantId,
                (int) $room->idrooms,
                $validated['tanggal'],
                $ignoreId
            )) {
                throw ValidationException::withMessages([
                    'tanggal' => 'Tidak bisa menutup hari karena masih ada booking aktif atau slot manual lain pada tanggal tersebut.',
                ]);
            }
        }

        return [
            'rooms_idrooms' => (int) $room->idrooms,
            'service_idservice' => $serviceId,
            'tanggal' => $validated['tanggal'],
            'override_type' => $overrideType,
            'waktu_mulai' => $this->normalizeTimeOrNull($validated['waktu_mulai'] ?? null),
            'waktu_selesai' => $this->normalizeTimeOrNull($validated['waktu_selesai'] ?? null),
            'catatan' => $validated['catatan'] ?? null,
        ];
    }

    private function hasAddSlotConflict(
        int $tenantId,
        int $roomId,
        string $tanggal,
        string $waktuMulai,
        string $waktuSelesai,
        ?int $ignoreOverrideId = null
    ): bool {
        $normalizedStart = $this->normalizeTimeOrNull($waktuMulai);
        $normalizedEnd = $this->normalizeTimeOrNull($waktuSelesai);

        $hasExactDuplicate = Jadwal::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->where('tanggal', $tanggal)
            ->where('waktu_mulai', $normalizedStart)
            ->where('waktu_selesai', $normalizedEnd)
            ->when($ignoreOverrideId, function ($query) use ($ignoreOverrideId) {
                $query->where(function ($inner) use ($ignoreOverrideId) {
                    $inner->where('source_type', '!=', 'override')
                        ->orWhereNull('schedule_date_harian_override_id')
                        ->orWhere('schedule_date_harian_override_id', '!=', $ignoreOverrideId);
                });
            })
            ->exists();

        if ($hasExactDuplicate) {
            return true;
        }

        return Jadwal::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->where('tanggal', $tanggal)
            ->where('source_type', 'override')
            ->whereNull('service_idservice')
            ->when($ignoreOverrideId, fn ($query) => $query->where('schedule_date_harian_override_id', '!=', $ignoreOverrideId))
            ->where(function ($query) use ($normalizedStart, $normalizedEnd) {
                $query->where('waktu_mulai', '<', $normalizedEnd)
                    ->where('waktu_selesai', '>', $normalizedStart);
            })
            ->exists();
    }

    private function hasBlockingConflict(
        int $tenantId,
        int $roomId,
        string $tanggal,
        string $waktuMulai,
        string $waktuSelesai,
        ?int $ignoreOverrideId = null
    ): bool {
        $normalizedStart = $this->normalizeTimeOrNull($waktuMulai);
        $normalizedEnd = $this->normalizeTimeOrNull($waktuSelesai);

        $hasActiveBooking = Booking::query()
            ->whereHas('jadwal', function ($query) use ($tenantId, $roomId, $tanggal, $normalizedStart, $normalizedEnd) {
                $query->where('tenants_idTenant', $tenantId)
                    ->where('rooms_idrooms', $roomId)
                    ->where('tanggal', $tanggal)
                    ->where('waktu_mulai', '<', $normalizedEnd)
                    ->where('waktu_selesai', '>', $normalizedStart);
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveBooking) {
            return true;
        }

        return Jadwal::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->where('tanggal', $tanggal)
            ->where(function ($query) use ($normalizedStart, $normalizedEnd) {
                $query->where('waktu_mulai', '<', $normalizedEnd)
                    ->where('waktu_selesai', '>', $normalizedStart);
            })
            ->where(function ($query) use ($ignoreOverrideId) {
                $query->where('source_type', 'manual')
                    ->orWhere(function ($inner) use ($ignoreOverrideId) {
                        $inner->where('source_type', 'override');
                        if ($ignoreOverrideId) {
                            $inner->where('schedule_date_harian_override_id', '!=', $ignoreOverrideId);
                        }
                    });
            })
            ->exists();
    }

    private function hasCloseDayConflict(
        int $tenantId,
        int $roomId,
        string $tanggal,
        ?int $ignoreOverrideId = null
    ): bool {
        $hasActiveBooking = Booking::query()
            ->whereHas('jadwal', function ($query) use ($tenantId, $roomId, $tanggal) {
                $query->where('tenants_idTenant', $tenantId)
                    ->where('rooms_idrooms', $roomId)
                    ->where('tanggal', $tanggal);
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveBooking) {
            return true;
        }

        return Jadwal::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $roomId)
            ->where('tanggal', $tanggal)
            ->where(function ($query) use ($ignoreOverrideId) {
                $query->where('source_type', 'manual')
                    ->orWhere(function ($inner) use ($ignoreOverrideId) {
                        $inner->where('source_type', 'override');
                        if ($ignoreOverrideId) {
                            $inner->where('schedule_date_harian_override_id', '!=', $ignoreOverrideId);
                        }
                    });
            })
            ->exists();
    }

    private function resolveSlotDurationInMinutes(string $waktuMulai, string $waktuSelesai): int
    {
        return Carbon::createFromFormat('H:i', $waktuMulai)
            ->diffInMinutes(Carbon::createFromFormat('H:i', $waktuSelesai));
    }

    private function normalizeTimeOrNull(?string $time): ?string
    {
        if (!$time) {
            return null;
        }

        return strlen($time) === 5 ? $time.':00' : $time;
    }

    /**
     * @param array{created:int,updated:int,deleted:int,skipped:int} $stats
     */
    private function buildSyncMessage(string $prefix, array $stats): string
    {
        return $prefix.' Hasil sync '.$this->syncService->syncWindowLabel().': buat '.$stats['created'].', update '.$stats['updated'].', hapus '.$stats['deleted'].', skip '.$stats['skipped'].'.';
    }
}
