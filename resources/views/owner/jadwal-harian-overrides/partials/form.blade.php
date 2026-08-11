@php
    $selectedOverride = $override ?? null;
    $selectedType = old('override_type', $selectedOverride?->override_type ?? 'add_slot');
@endphp

<div class="max-w-3xl mx-auto space-y-4">
    @if ($errors->any())
        <div class="p-4 bg-red-100 text-red-800 rounded dark:bg-red-900/30 dark:text-red-200">
            <div class="font-semibold mb-2">Terjadi kesalahan:</div>
            <ul class="list-disc ml-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
        Gunakan menu ini untuk override tanggal tertentu. Template tetap menjadi pola default, lalu pengaturan harian akan memodifikasi hasil final pada tanggal yang dipilih.
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <form method="POST"
                  action="{{ $action }}"
                  x-data="{ overrideType: '{{ $selectedType }}' }">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <div class="space-y-5">
                    <div>
                        <label class="block mb-1 font-medium">Jenis Pengaturan</label>
                        <select id="override_type"
                                name="override_type"
                                x-model="overrideType"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            <option value="add_slot">Tambah slot manual</option>
                            <option value="block_interval">Blok interval jam</option>
                            <option value="close_day">Tutup satu hari penuh</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Room</label>
                        <select id="rooms_idrooms"
                                name="rooms_idrooms"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            <option value="">-- pilih room --</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->idrooms }}"
                                        data-type="{{ $room->tipe_ruangan }}"
                                        @selected(old('rooms_idrooms', $selectedOverride?->rooms_idrooms) == $room->idrooms)>
                                    {{ $room->nama_ruangan }} ({{ $room->tipe_ruangan }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="overrideType === 'add_slot'">
                        <label class="block mb-1 font-medium">Service</label>
                        <select id="service_idservice"
                                name="service_idservice"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                :required="overrideType === 'add_slot'">
                            <option value="">-- pilih service --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->idservice }}"
                                        data-type="{{ $service->tipe_service }}"
                                        data-duration="{{ $service->durasi_menit }}"
                                        @selected(old('service_idservice', $selectedOverride?->service_idservice) == $service->idservice)>
                                    {{ $service->nama_service }} ({{ $service->tipe_service }}, {{ $service->durasi_menit }} menit)
                                </option>
                            @endforeach
                        </select>
                        <div id="service_duration_hint" class="mt-2 text-xs text-gray-500">
                            Untuk tambah slot manual, durasi slot harus sama dengan durasi service.
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Tanggal</label>
                        <input type="date"
                               name="tanggal"
                               class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('tanggal', optional($selectedOverride?->tanggal)->format('Y-m-d')) }}"
                               required>
                    </div>

                    <div x-show="overrideType !== 'close_day'" class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 font-medium">Waktu Mulai</label>
                            <input type="time"
                                   name="waktu_mulai"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('waktu_mulai', $selectedOverride?->waktu_mulai ? substr($selectedOverride->waktu_mulai, 0, 5) : '') }}"
                                   :required="overrideType !== 'close_day'">
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Waktu Selesai</label>
                            <input type="time"
                                   name="waktu_selesai"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('waktu_selesai', $selectedOverride?->waktu_selesai ? substr($selectedOverride->waktu_selesai, 0, 5) : '') }}"
                                   :required="overrideType !== 'close_day'">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Catatan</label>
                        <textarea name="catatan"
                                  rows="3"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Opsional">{{ old('catatan', $selectedOverride?->catatan) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ $submitLabel }}
                        </button>

                        <a href="{{ route('owner.jadwals.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const serviceEl = document.getElementById('service_idservice');
        const roomEl = document.getElementById('rooms_idrooms');
        const overrideTypeEl = document.getElementById('override_type');
        const durationHintEl = document.getElementById('service_duration_hint');
        const allRoomOptions = Array.from(roomEl.querySelectorAll('option'));

        const updateHint = () => {
            const selectedService = serviceEl?.options?.[serviceEl.selectedIndex];
            const duration = selectedService?.dataset?.duration;

            if (overrideTypeEl.value !== 'add_slot') {
                durationHintEl.textContent = 'Service tidak diperlukan untuk blok interval atau tutup satu hari penuh.';
                return;
            }

            if (selectedService?.value && duration) {
                durationHintEl.textContent = `Durasi slot manual wajib ${duration} menit untuk service ini.`;
                return;
            }

            durationHintEl.textContent = 'Untuk tambah slot manual, durasi slot harus sama dengan durasi service.';
        };

        const filterRoomsByServiceType = () => {
            const selectedService = serviceEl?.options?.[serviceEl.selectedIndex];
            const serviceType = overrideTypeEl.value === 'add_slot' ? selectedService?.dataset?.type : null;

            if (!serviceType) {
                allRoomOptions.forEach((option) => {
                    option.hidden = false;
                });
                updateHint();
                return;
            }

            allRoomOptions.forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = option.dataset.type !== serviceType;
            });

            const selectedRoom = roomEl.options[roomEl.selectedIndex];
            if (!selectedRoom?.value || selectedRoom.dataset.type !== serviceType) {
                roomEl.value = '';
            }

            updateHint();
        };

        serviceEl?.addEventListener('change', filterRoomsByServiceType);
        overrideTypeEl.addEventListener('change', filterRoomsByServiceType);

        filterRoomsByServiceType();
    });
</script>
