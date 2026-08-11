<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\ScheduleTemplate;
use App\Models\Service;
use App\Support\ScheduleTemplateSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class JadwalTemplateController extends Controller
{
    public function __construct(
        private readonly ScheduleTemplateSyncService $syncService
    ) {
    }

    public function index()
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $templates = ScheduleTemplate::query()
            ->with(['room', 'service'])
            ->where('tenants_idTenant', $tenantId)
            ->orderByDesc('is_active')
            ->orderBy('nama_template')
            ->paginate(12)
            ->withQueryString();

        return view('owner.jadwal-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('owner.jadwal-templates.create', $this->sharedFormData());
    }

    public function store(Request $request)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;
        $validated = $this->validateTemplate($request, $tenantId);

        ScheduleTemplate::query()->create(array_merge($validated, [
            'tenants_idTenant' => $tenantId,
        ]));

        return redirect()
            ->route('owner.jadwals.templates.index')
            ->with('success', 'Template jadwal berhasil ditambahkan. Sinkronisasi dilakukan manual dari aksi Sync pada daftar template.');
    }

    public function edit(int $id)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $template = ScheduleTemplate::query()
            ->where('tenants_idTenant', $tenantId)
            ->findOrFail($id);

        return view('owner.jadwal-templates.edit', array_merge(
            $this->sharedFormData(),
            ['template' => $template]
        ));
    }

    public function update(Request $request, int $id)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $template = ScheduleTemplate::query()
            ->where('tenants_idTenant', $tenantId)
            ->findOrFail($id);

        $validated = $this->validateTemplate($request, $tenantId);

        $template->update($validated);

        return redirect()
            ->route('owner.jadwals.templates.index')
            ->with('success', 'Template jadwal berhasil diperbarui. Sinkronisasi dilakukan manual dari aksi Sync pada daftar template.');
    }

    public function destroy(int $id)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $template = ScheduleTemplate::query()
            ->where('tenants_idTenant', $tenantId)
            ->findOrFail($id);

        $deletedSlots = $this->syncService->purgeTemplateFutureSlots($template);
        $template->delete();

        return redirect()
            ->route('owner.jadwals.templates.index')
            ->with('success', 'Template jadwal berhasil dihapus. Slot template '.$this->syncService->syncWindowLabel().' yang belum dibooking dibersihkan: '.$deletedSlots.'.');
    }

    public function syncAll()
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;
        $stats = $this->syncService->syncTenantTemplates($tenantId);

        return redirect()
            ->route('owner.jadwals.templates.index')
            ->with('success', $this->buildSyncMessage('Sinkronisasi template selesai.', $stats));
    }

    public function syncOne(int $id)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $template = ScheduleTemplate::query()
            ->where('tenants_idTenant', $tenantId)
            ->findOrFail($id);

        $stats = $this->syncService->syncSingleTemplate($template);

        return redirect()
            ->route('owner.jadwals.templates.index')
            ->with('success', $this->buildSyncMessage('Template terpilih berhasil disinkronkan.', $stats));
    }

    public function toggleActive(int $id)
    {
        $tenantId = (int) Auth::user()->tenants_idTenant;

        $template = ScheduleTemplate::query()
            ->where('tenants_idTenant', $tenantId)
            ->findOrFail($id);

        $nextActive = !$template->is_active;

        $template->forceFill([
            'is_active' => $nextActive,
        ])->save();

        return redirect()
            ->route('owner.jadwals.templates.index')
            ->with('success', $nextActive
                ? 'Template berhasil diaktifkan. Sinkronisasi dilakukan manual dari aksi Sync pada daftar template.'
                : 'Template berhasil dinonaktifkan. Jika slot jadwal sudah pernah disinkronkan, jalankan Sync untuk memperbarui jadwal.');
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
            'repeatModes' => [
                'daily' => 'Setiap hari',
                'weekdays' => 'Weekdays',
                'weekends' => 'Weekend',
                'custom_days' => 'Hari tertentu',
            ],
            'dayOptions' => [
                1 => 'Sen',
                2 => 'Sel',
                3 => 'Rab',
                4 => 'Kam',
                5 => 'Jum',
                6 => 'Sab',
                7 => 'Min',
            ],
        ];
    }

    private function validateTemplate(Request $request, int $tenantId): array
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:120',
            'service_idservice' => 'required|integer',
            'rooms_idrooms' => 'required|integer',
            'repeat_mode' => 'required|in:daily,weekdays,weekends,custom_days',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'is_active' => 'nullable|boolean',
        ]);

        $room = Room::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('idrooms', $validated['rooms_idrooms'])
            ->where('status', 1)
            ->firstOrFail();

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

        if ($this->resolveSlotDurationInMinutes($validated['waktu_mulai'], $validated['waktu_selesai']) !== (int) $service->durasi_menit) {
            throw ValidationException::withMessages([
                'waktu_selesai' => 'Durasi template harus sama dengan durasi service, yaitu '.$service->durasi_menit.' menit.',
            ]);
        }

        $daysOfWeek = $validated['repeat_mode'] === 'custom_days'
            ? array_values(array_unique(array_map('intval', $validated['days_of_week'] ?? [])))
            : null;

        if ($validated['repeat_mode'] === 'custom_days' && empty($daysOfWeek)) {
            throw ValidationException::withMessages([
                'days_of_week' => 'Pilih minimal satu hari untuk mode custom.',
            ]);
        }

        $isActive = (bool) ($validated['is_active'] ?? false);

        return [
            'nama_template' => $validated['nama_template'],
            'service_idservice' => (int) $validated['service_idservice'],
            'rooms_idrooms' => (int) $validated['rooms_idrooms'],
            'repeat_mode' => $validated['repeat_mode'],
            'days_of_week_json' => $daysOfWeek,
            'waktu_mulai' => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'is_active' => $isActive,
        ];
    }

    private function resolveSlotDurationInMinutes(string $waktuMulai, string $waktuSelesai): int
    {
        return Carbon::createFromFormat('H:i', $waktuMulai)
            ->diffInMinutes(Carbon::createFromFormat('H:i', $waktuSelesai));
    }

    /**
     * @param array{created:int,updated:int,deleted:int,skipped:int} $stats
     */
    private function buildSyncMessage(string $prefix, array $stats): string
    {
        return $prefix.' Hasil sync '.$this->syncService->syncWindowLabel().': buat '.$stats['created'].', update '.$stats['updated'].', hapus '.$stats['deleted'].', skip '.$stats['skipped'].'.';
    }
}
