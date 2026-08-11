<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $rooms = Room::where('tenants_idTenant', $tenantId)
            ->with(['facilities' => fn ($query) => $query->orderBy('nama_fasilitas')])
            ->orderBy('idrooms', 'desc')
            ->get();

        return view('owner.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $tenantId = Auth::user()->tenants_idTenant;
        $facilities = $this->tenantFacilities($tenantId);

        return view('owner.rooms.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'tipe_ruangan' => 'required|in:latihan,rekaman',
            'status' => 'required|in:0,1',
            'foto_ruangan' => 'nullable|image|max:5120',
            'facilities' => 'nullable|array',
            'facilities.*.selected' => 'nullable|boolean',
            'facilities.*.notes' => 'nullable|string|max:255',
            'move_facilities' => 'nullable|array',
            'move_facilities.*' => 'nullable|boolean',
        ]);

        $tenantId = Auth::user()->tenants_idTenant;
        [$facilitySyncData, $moveFacilityIds] = $this->resolveFacilitySyncData($request, $tenantId);

        $photoPath = null;
        if ($request->hasFile('foto_ruangan')) {
            $photoPath = $request->file('foto_ruangan')->store('rooms', 'public');
        }

        $room = Room::create([
            'nama_ruangan' => $request->nama_ruangan,
            'deskripsi' => $request->deskripsi,
            'kapasitas' => $request->kapasitas,
            'tipe_ruangan' => $request->tipe_ruangan,
            'status' => (int) $request->status,
            'foto_ruangan' => $photoPath,
            'tenants_idTenant' => $tenantId,
        ]);

        $this->detachMovedFacilities($moveFacilityIds, (int) $room->idrooms);
        $room->facilities()->sync($facilitySyncData);

        return redirect()->route('owner.rooms.index')
            ->with('success', 'Room berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $room = Room::where('tenants_idTenant', $tenantId)
            ->with('facilities')
            ->where('idrooms', $id)
            ->firstOrFail();

        $facilities = $this->tenantFacilities($tenantId);

        return view('owner.rooms.edit', compact('room', 'facilities'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'tipe_ruangan' => 'required|in:latihan,rekaman',
            'status' => 'required|in:0,1',
            'foto_ruangan' => 'nullable|image|max:5120',
            'facilities' => 'nullable|array',
            'facilities.*.selected' => 'nullable|boolean',
            'facilities.*.notes' => 'nullable|string|max:255',
            'move_facilities' => 'nullable|array',
            'move_facilities.*' => 'nullable|boolean',
        ]);

        $tenantId = Auth::user()->tenants_idTenant;

        $room = Room::where('tenants_idTenant', $tenantId)
            ->where('idrooms', $id)
            ->firstOrFail();

        [$facilitySyncData, $moveFacilityIds] = $this->resolveFacilitySyncData($request, $tenantId, (int) $room->idrooms);

        $photoPath = $room->foto_ruangan;
        if ($request->hasFile('foto_ruangan')) {
            $photoPath = $request->file('foto_ruangan')->store('rooms', 'public');
            if ($room->foto_ruangan) {
                Storage::disk('public')->delete($room->foto_ruangan);
            }
        }

        $room->update([
            'nama_ruangan' => $request->nama_ruangan,
            'deskripsi' => $request->deskripsi,
            'kapasitas' => $request->kapasitas,
            'tipe_ruangan' => $request->tipe_ruangan,
            'status' => (int) $request->status,
            'foto_ruangan' => $photoPath,
        ]);

        $this->detachMovedFacilities($moveFacilityIds, (int) $room->idrooms);
        $room->facilities()->sync($facilitySyncData);

        return redirect()->route('owner.rooms.index')
            ->with('success', 'Room berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $room = Room::where('tenants_idTenant', $tenantId)
            ->where('idrooms', $id)
            ->firstOrFail();

        $room->facilities()->detach();
        $room->delete();

        return redirect()->route('owner.rooms.index')
            ->with('success', 'Room berhasil dihapus.');
    }

    private function tenantFacilities(int $tenantId)
    {
        return Facility::query()
            ->where('tenants_idTenant', $tenantId)
            ->with(['rooms' => fn ($query) => $query->orderBy('nama_ruangan')])
            ->orderBy('nama_fasilitas')
            ->get();
    }

    private function resolveFacilitySyncData(Request $request, int $tenantId, ?int $currentRoomId = null): array
    {
        $input = $request->input('facilities', []);

        if (!is_array($input) || $input === []) {
            return [[], []];
        }

        $selectedIds = collect($input)
            ->filter(fn ($value) => filter_var($value['selected'] ?? false, FILTER_VALIDATE_BOOLEAN))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($selectedIds->isEmpty()) {
            return [[], []];
        }

        $facilities = Facility::query()
            ->where('tenants_idTenant', $tenantId)
            ->whereIn('idfasiltas', $selectedIds)
            ->with('rooms')
            ->get()
            ->keyBy(fn (Facility $facility) => (int) $facility->idfasiltas);

        $syncData = [];
        $moveFacilityIds = [];
        $moveFlags = $request->input('move_facilities', []);
        $errors = [];

        foreach ($facilities as $facilityId => $facility) {
            $facilityInput = $input[$facilityId] ?? $input[(string) $facilityId] ?? [];
            $otherRooms = $facility->rooms
                ->reject(fn ($room) => $currentRoomId && (int) $room->idrooms === $currentRoomId);
            $quantity = max(1, (int) ($facility->quantity ?? 1));
            $isCapacityFull = $otherRooms->count() >= $quantity;
            $moveRequested = filter_var(
                $moveFlags[$facilityId] ?? $moveFlags[(string) $facilityId] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            if ($isCapacityFull) {
                if ($moveRequested) {
                    $moveFacilityIds[] = (int) $facilityId;
                } else {
                    $roomNames = $otherRooms
                        ->pluck('nama_ruangan')
                        ->implode(', ');
                    $errors["facilities.{$facilityId}.selected"] = "Jumlah {$facility->nama_fasilitas} sudah habis karena sedang digunakan di {$roomNames}. Pilih fasilitas lagi dan konfirmasi pemindahan jika ingin memindahkannya ke ruangan ini.";
                    continue;
                }
            }

            $syncData[$facilityId] = [
                'tenants_idTenant' => $tenantId,
                'notes' => $this->normalizeNullableString($facilityInput['notes'] ?? null),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return [$syncData, array_values(array_unique($moveFacilityIds))];
    }

    private function detachMovedFacilities(array $facilityIds, int $targetRoomId): void
    {
        if ($facilityIds === []) {
            return;
        }

        foreach ($facilityIds as $facilityId) {
            $facility = Facility::query()
                ->with('rooms')
                ->find($facilityId);

            if (!$facility) {
                continue;
            }

            $otherRoomIds = $facility->rooms
                ->pluck('idrooms')
                ->map(fn ($id) => (int) $id)
                ->reject(fn (int $roomId) => $roomId === $targetRoomId)
                ->values()
                ->all();

            if ($otherRoomIds !== []) {
                $facility->rooms()->detach($otherRoomIds);
            }
        }
    }

    private function normalizeNullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
