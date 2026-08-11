@php
    $selectedFacilities = old('facilities');

    if ($selectedFacilities === null && isset($room)) {
        $selectedFacilities = $room->facilities
            ->mapWithKeys(function ($facility) {
                return [
                    (string) $facility->idfasiltas => [
                        'selected' => '1',
                        'notes' => $facility->pivot->notes,
                    ],
                ];
            })
            ->toArray();
    }

    $selectedFacilities = is_array($selectedFacilities) ? $selectedFacilities : [];
    $moveFacilities = old('move_facilities', []);
    $moveFacilities = is_array($moveFacilities) ? $moveFacilities : [];
    $currentRoomId = isset($room) ? (int) $room->idrooms : null;
@endphp

<div>
    <label class="block mb-1 font-medium">Fasilitas / Alat di Ruangan</label>
    <p class="mb-3 text-xs text-gray-500">
        Pilih alat yang ada di ruangan ini. Jika alat dipindah ke ruangan lain, cukup ubah pilihan fasilitas pada ruangan terkait.
    </p>

    @if ($facilities->isEmpty())
        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
            Belum ada data fasilitas. Tambahkan fasilitas terlebih dahulu dari menu Manajemen Fasilitas.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($facilities as $facility)
                @php
                    $facilityId = (string) $facility->idfasiltas;
                    $facilityInput = $selectedFacilities[$facilityId] ?? [];
                    $isSelected = filter_var($facilityInput['selected'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $quantity = max(1, (int) ($facility->quantity ?? 1));
                    $otherRooms = $facility->rooms->reject(fn ($roomItem) => $currentRoomId && (int) $roomItem->idrooms === $currentRoomId);
                    $otherRoomLabel = $otherRooms
                        ->pluck('nama_ruangan')
                        ->implode(', ');
                    $hasConflict = $otherRooms->count() >= $quantity;
                    $moveChecked = filter_var($moveFacilities[$facilityId] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $conflictMessage = $otherRoomLabel
                        ? "Jumlah fasilitas {$facility->nama_fasilitas} sudah habis karena digunakan di {$otherRoomLabel}. Apakah ingin memindahkan fasilitas ini ke ruangan ini?"
                        : null;
                    $autoMoveSelected = $hasConflict && $isSelected && $moveChecked;
                @endphp

                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <label class="flex items-start gap-3">
                            <input type="hidden" name="facilities[{{ $facility->idfasiltas }}][selected]" value="0">
                            <input type="hidden"
                                   name="move_facilities[{{ $facility->idfasiltas }}]"
                                   value="{{ $autoMoveSelected ? '1' : '0' }}"
                                   data-move-flag>
                            <input type="checkbox"
                                   name="facilities[{{ $facility->idfasiltas }}][selected]"
                                   value="1"
                                   class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   data-facility-select
                                   data-conflict-message="{{ $hasConflict ? $conflictMessage : '' }}"
                                   @checked($isSelected)>
                            <span>
                                <span class="block font-semibold">{{ $facility->nama_fasilitas }}</span>
                                <span class="block text-xs text-gray-500">
                                    {{ $facility->deskripsi ?: 'Tidak ada deskripsi.' }}
                                </span>
                                <span class="mt-1 block text-xs text-gray-600">
                                    Jumlah tersedia: {{ $quantity }}
                                </span>
                                @if ($otherRoomLabel)
                                    <span class="mt-1 block text-xs text-amber-700">
                                        Sedang digunakan di: {{ $otherRoomLabel }}
                                    </span>
                                @endif
                            </span>
                        </label>

                        <div class="md:w-64">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Catatan</label>
                                <input name="facilities[{{ $facility->idfasiltas }}][notes]"
                                       value="{{ $facilityInput['notes'] ?? '' }}"
                                       placeholder="Opsional"
                                       class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-facility-select]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const message = checkbox.dataset.conflictMessage;
                const moveFlag = checkbox.closest('label')?.querySelector('[data-move-flag]');

                if (moveFlag && !checkbox.checked) {
                    moveFlag.value = '0';
                }

                if (!checkbox.checked || !message) return;

                if (window.confirm(message)) {
                    if (moveFlag) {
                        moveFlag.value = '1';
                    }
                } else {
                    checkbox.checked = false;
                    if (moveFlag) {
                        moveFlag.value = '0';
                    }
                }
            });
        });
    });
</script>
