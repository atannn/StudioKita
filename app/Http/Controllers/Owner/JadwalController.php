<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Room;
use App\Models\Service;
use Carbon\Carbon;
use App\Support\ScheduleAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function __construct(
        private readonly ScheduleAvailabilityService $scheduleAvailabilityService
    ) {
    }

    public function index()
    {
        $tenantId = Auth::user()->tenants_idTenant;
        $now = Carbon::now();

        // hapus jadwal lama (non-booked) yang sudah lewat jam mulai
        Jadwal::where('tenants_idTenant', $tenantId)
            ->where('status', '!=', 'booked')
            ->where(function ($q) use ($now) {
                $q->where('tanggal', '<', $now->toDateString())
                  ->orWhere(function ($q2) use ($now) {
                      $q2->where('tanggal', $now->toDateString())
                         ->where('waktu_mulai', '<', $now->format('H:i:s'));
                  });
            })
            ->delete();

        $this->scheduleAvailabilityService->recomputeTenantUpcoming((int) $tenantId);

        $jadwalsAll = Jadwal::with(['room', 'service'])
            ->where('tenants_idTenant', $tenantId)
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $jadwalsLatihan = Jadwal::with(['room', 'service'])
            ->where('tenants_idTenant', $tenantId)
            ->whereHas('room', function ($q) {
                $q->where('tipe_ruangan', 'latihan');
            })
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->paginate(10, ['*'], 'latihan_page')
            ->withQueryString();

        $jadwalsRekaman = Jadwal::with(['room', 'service'])
            ->where('tenants_idTenant', $tenantId)
            ->whereHas('room', function ($q) {
                $q->where('tipe_ruangan', 'rekaman');
            })
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->paginate(10, ['*'], 'rekaman_page')
            ->withQueryString();

        $roomsAll = Room::where('tenants_idTenant', $tenantId)
            ->orderBy('nama_ruangan')
            ->get();

        return view('owner.jadwals.index', compact('jadwalsAll', 'jadwalsLatihan', 'jadwalsRekaman', 'roomsAll'));
    }

    public function create()
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $rooms = Room::where('tenants_idTenant', $tenantId)
            ->orderBy('nama_ruangan')
            ->get();

        $services = Service::where('tenants_idTenant', $tenantId)
            ->orderBy('tipe_service')
            ->orderBy('nama_service')
            ->get();

        return view('owner.jadwals.create', compact('rooms', 'services'));
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        $mode = $request->input('mode', 'single');

        $rules = [
            'mode'           => 'nullable|in:single,range',
            'service_idservice' => 'required|integer',
            'rooms_idrooms'  => 'required|integer',
            'waktu_mulai'    => 'required|date_format:H:i',
            'waktu_selesai'  => 'required|date_format:H:i|after:waktu_mulai',
            'status'         => 'required|in:available,booked,blocked',
        ];

        if ($mode === 'range') {
            $rules['tanggal_mulai'] = 'required|date';
            $rules['tanggal_selesai'] = 'required|date|after_or_equal:tanggal_mulai';
        } else {
            $rules['tanggal'] = 'required|date';
        }

        $request->validate($rules);

        $room = Room::where('tenants_idTenant', $tenantId)
            ->where('idrooms', $request->rooms_idrooms)
            ->firstOrFail();

        $service = Service::where('tenants_idTenant', $tenantId)
            ->where('idservice', $request->service_idservice)
            ->firstOrFail();

        if ($service->tipe_service !== $room->tipe_ruangan) {
            return back()
                ->withErrors([
                    'rooms_idrooms' => 'Tipe room harus sesuai dengan tipe service (rekaman/latihan).',
                ])
                ->withInput();
        }

        if ($this->resolveSlotDurationInMinutes($request->waktu_mulai, $request->waktu_selesai) !== (int) $service->durasi_menit) {
            return back()
                ->withErrors([
                    'waktu_selesai' => 'Durasi slot harus sama dengan durasi service, yaitu '.$service->durasi_menit.' menit.',
                ])
                ->withInput();
        }

        $dates = [];
        if ($mode === 'range') {
            $start = Carbon::parse($request->tanggal_mulai);
            $end = Carbon::parse($request->tanggal_selesai);
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $dates[] = $date->toDateString();
            }
        } else {
            $dates[] = $request->tanggal;
        }

        $conflicts = [];
        foreach ($dates as $tanggal) {
            $overlap = Jadwal::where('tenants_idTenant', $tenantId)
                ->where('rooms_idrooms', $request->rooms_idrooms)
                ->where('tanggal', $tanggal)
                ->where(function ($q) use ($request) {
                    $q->where('waktu_mulai', '<', $request->waktu_selesai)
                      ->where('waktu_selesai', '>', $request->waktu_mulai);
                })
                ->exists();

            if ($overlap) {
                $conflicts[] = $tanggal;
            }
        }

        if (!empty($conflicts)) {
            return back()
                ->withErrors([
                    'waktu_mulai' => 'Jadwal bentrok pada tanggal: '.implode(', ', $conflicts).'.',
                ])
                ->withInput();
        }

        foreach ($dates as $tanggal) {
            Jadwal::create([
                'rooms_idrooms'   => $request->rooms_idrooms,
                'service_idservice' => $request->service_idservice,
                'tanggal'         => $tanggal,
                'waktu_mulai'     => $request->waktu_mulai,
                'waktu_selesai'   => $request->waktu_selesai,
                'status'          => $request->status,
                'tenants_idTenant'=> $tenantId,
                'source_type'     => 'manual',
                'schedule_template_id' => null,
            ]);
        }

        return redirect()->route('owner.jadwals.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $jadwal = Jadwal::where('tenants_idTenant', $tenantId)
            ->where('idJadwal', $id)
            ->firstOrFail();

        if ($jadwal->source_type === 'template') {
            return redirect()
                ->route('owner.jadwals.index')
                ->withErrors([
                    'jadwal' => 'Slot dari template tidak bisa diedit dari menu jadwal manual. Ubah dari menu Template Jadwal.',
                ]);
        }

        $rooms = Room::where('tenants_idTenant', $tenantId)
            ->orderBy('nama_ruangan')
            ->get();

        $services = Service::where('tenants_idTenant', $tenantId)
            ->orderBy('tipe_service')
            ->orderBy('nama_service')
            ->get();

        return view('owner.jadwals.edit', compact('jadwal', 'rooms', 'services'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $request->validate([
            'service_idservice' => 'required|integer',
            'rooms_idrooms'  => 'required|integer',
            'tanggal'        => 'required|date',
            'waktu_mulai'    => 'required|date_format:H:i',
            'waktu_selesai'  => 'required|date_format:H:i|after:waktu_mulai',
            'status'         => 'required|in:available,booked,blocked',
        ]);

        $jadwal = Jadwal::where('tenants_idTenant', $tenantId)
            ->where('idJadwal', $id)
            ->firstOrFail();

        if ($jadwal->source_type === 'template') {
            return redirect()
                ->route('owner.jadwals.index')
                ->withErrors([
                    'jadwal' => 'Slot dari template tidak bisa diubah dari menu jadwal manual. Ubah dari menu Template Jadwal.',
                ]);
        }

        $room = Room::where('tenants_idTenant', $tenantId)
            ->where('idrooms', $request->rooms_idrooms)
            ->firstOrFail();

        $service = Service::where('tenants_idTenant', $tenantId)
            ->where('idservice', $request->service_idservice)
            ->firstOrFail();

        if ($service->tipe_service !== $room->tipe_ruangan) {
            return back()
                ->withErrors([
                    'rooms_idrooms' => 'Tipe room harus sesuai dengan tipe service (rekaman/latihan).',
                ])
                ->withInput();
        }

        if ($this->resolveSlotDurationInMinutes($request->waktu_mulai, $request->waktu_selesai) !== (int) $service->durasi_menit) {
            return back()
                ->withErrors([
                    'waktu_selesai' => 'Durasi slot harus sama dengan durasi service, yaitu '.$service->durasi_menit.' menit.',
                ])
                ->withInput();
        }

        // validasi overlap (exclude diri sendiri)
        $overlap = Jadwal::where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $request->rooms_idrooms)
            ->where('tanggal', $request->tanggal)
            ->where('idJadwal', '!=', $jadwal->idJadwal)
            ->where(function ($q) use ($request) {
                $q->where('waktu_mulai', '<', $request->waktu_selesai)
                  ->where('waktu_selesai', '>', $request->waktu_mulai);
            })
            ->exists();

        if ($overlap) {
            return back()
                ->withErrors(['waktu_mulai' => 'Jadwal bentrok dengan slot lain pada room & tanggal yang sama.'])
                ->withInput();
        }

        $jadwal->update([
            'rooms_idrooms'  => $request->rooms_idrooms,
            'service_idservice' => $request->service_idservice,
            'tanggal'        => $request->tanggal,
            'waktu_mulai'    => $request->waktu_mulai,
            'waktu_selesai'  => $request->waktu_selesai,
            'status'         => $request->status,
            'source_type'    => 'manual',
            'schedule_template_id' => null,
        ]);

        return redirect()->route('owner.jadwals.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $jadwal = Jadwal::where('tenants_idTenant', $tenantId)
            ->where('idJadwal', $id)
            ->firstOrFail();

        if ($jadwal->source_type === 'template') {
            return redirect()
                ->route('owner.jadwals.index')
                ->withErrors([
                    'jadwal' => 'Slot dari template tidak bisa dihapus dari menu jadwal manual. Nonaktifkan atau ubah template terlebih dahulu.',
                ]);
        }

        $jadwal->delete();

        return redirect()->route('owner.jadwals.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    private function resolveSlotDurationInMinutes(string $waktuMulai, string $waktuSelesai): int
    {
        return Carbon::createFromFormat('H:i', $waktuMulai)
            ->diffInMinutes(Carbon::createFromFormat('H:i', $waktuSelesai));
    }
}
