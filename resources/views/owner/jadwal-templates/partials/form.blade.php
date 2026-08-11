@php
    $selectedTemplate = $template ?? null;
    $selectedDays = old('days_of_week', $selectedTemplate?->days_of_week_json ?? []);
    $selectedRepeatMode = old('repeat_mode', $selectedTemplate?->repeat_mode ?? 'daily');
    $selectedActive = old('is_active', $selectedTemplate?->is_active ?? true);
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

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <form method="POST"
                  action="{{ $action }}"
                  x-data="{ repeatMode: '{{ $selectedRepeatMode }}' }">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <div class="space-y-5">
                    <div>
                        <label class="block mb-1 font-medium">Nama Template</label>
                        <input type="text"
                               name="nama_template"
                               class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('nama_template', $selectedTemplate?->nama_template) }}"
                               required>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Service</label>
                        <select id="service_idservice"
                                name="service_idservice"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            <option value="">-- pilih service --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->idservice }}"
                                        data-type="{{ $service->tipe_service }}"
                                        data-duration="{{ $service->durasi_menit }}"
                                        @selected(old('service_idservice', $selectedTemplate?->service_idservice) == $service->idservice)>
                                    {{ $service->nama_service }} ({{ $service->tipe_service }}, {{ $service->durasi_menit }} menit)
                                </option>
                            @endforeach
                        </select>
                        <div id="service_duration_hint" class="mt-2 text-xs text-gray-500">
                            Durasi template harus mengikuti durasi service.
                        </div>
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
                                        @selected(old('rooms_idrooms', $selectedTemplate?->rooms_idrooms) == $room->idrooms)>
                                    {{ $room->nama_ruangan }} ({{ $room->tipe_ruangan }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Pola Pengulangan</label>
                        <select name="repeat_mode"
                                x-model="repeatMode"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            @foreach ($repeatModes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="repeatMode === 'custom_days'">
                        <label class="block mb-2 font-medium">Hari Aktif</label>
                        <div class="flex flex-wrap gap-4 text-sm">
                            @foreach ($dayOptions as $value => $label)
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox"
                                           name="days_of_week[]"
                                           value="{{ $value }}"
                                           @checked(in_array($value, array_map('intval', $selectedDays), true))>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 font-medium">Waktu Mulai</label>
                            <input type="time"
                                   name="waktu_mulai"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('waktu_mulai', $selectedTemplate?->waktu_mulai ? substr($selectedTemplate->waktu_mulai, 0, 5) : '') }}"
                                   required>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Waktu Selesai</label>
                            <input type="time"
                                   name="waktu_selesai"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('waktu_selesai', $selectedTemplate?->waktu_selesai ? substr($selectedTemplate->waktu_selesai, 0, 5) : '') }}"
                                   required>
                        </div>
                    </div>

                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" @checked((bool) $selectedActive)>
                            Template aktif
                        </label>
                        <div class="mt-2 text-xs text-gray-500">
                            Template nonaktif disimpan sebagai draft. Sinkronisasi dilakukan dari aksi `Sync` pada daftar template.
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ $submitLabel }}
                        </button>

                        <a href="{{ route('owner.jadwals.templates.index') }}"
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
        const durationHintEl = document.getElementById('service_duration_hint');
        const allRoomOptions = Array.from(roomEl.querySelectorAll('option'));

        const updateHint = () => {
            const selectedService = serviceEl.options[serviceEl.selectedIndex];
            const duration = selectedService?.dataset?.duration;

            if (selectedService?.value && duration) {
                durationHintEl.textContent = `Durasi slot template wajib ${duration} menit untuk service ini.`;
                return;
            }

            durationHintEl.textContent = 'Durasi template harus mengikuti durasi service.';
        };

        const filterRoomsByServiceType = () => {
            const selectedService = serviceEl.options[serviceEl.selectedIndex];
            const serviceType = selectedService?.dataset?.type;

            if (!serviceType) {
                allRoomOptions.forEach((option) => {
                    option.hidden = false;
                });
                roomEl.value = '';
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

        serviceEl.addEventListener('change', filterRoomsByServiceType);

        filterRoomsByServiceType();
    });
</script>
