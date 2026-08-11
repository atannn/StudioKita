<x-owner-layout title="Tambah Jadwal">
    <div class="max-w-3xl mx-auto space-y-4">
        @if ($errors->any())
            <div class="p-4 bg-red-100 text-red-800 rounded dark:bg-red-900/30 dark:text-red-200">
                <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                <ul class="list-disc ml-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <form method="POST" action="{{ route('owner.jadwals.store') }}" x-data="{ mode: '{{ old('mode', 'single') }}' }">
                    @csrf

                    <div class="space-y-5">
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
                                            @selected(old('service_idservice') == $service->idservice)>
                                        {{ $service->nama_service }} ({{ $service->tipe_service }}, {{ $service->durasi_menit }} menit)
                                    </option>
                                @endforeach
                            </select>
                            <div id="service_duration_hint" class="mt-2 text-xs text-gray-500">
                                Pilih service terlebih dahulu. Durasi slot harus sama dengan durasi service.
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
                                            @selected(old('rooms_idrooms') == $room->idrooms)>
                                        {{ $room->nama_ruangan }} ({{ $room->tipe_ruangan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2 font-medium">Mode Tanggal</label>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="mode" value="single" x-model="mode">
                                    Satu hari
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="mode" value="range" x-model="mode">
                                    Rentang tanggal
                                </label>
                            </div>
                        </div>

                        <div x-show="mode === 'single'">
                            <label class="block mb-1 font-medium">Tanggal</label>
                            <input type="date" name="tanggal"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('tanggal') }}"
                                   :required="mode === 'single'">
                        </div>

                        <div x-show="mode === 'range'" class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block mb-1 font-medium">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('tanggal_mulai') }}"
                                       :required="mode === 'range'">
                            </div>
                            <div>
                                <label class="block mb-1 font-medium">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('tanggal_selesai') }}"
                                       :required="mode === 'range'">
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('waktu_mulai') }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Waktu Selesai</label>
                            <input type="time" name="waktu_selesai"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('waktu_selesai') }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status</label>
                            <select name="status"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                <option value="available" @selected(old('status','available') === 'available')>Tersedia</option>
                                <option value="booked" @selected(old('status') === 'booked')>Dibooking</option>
                                <option value="blocked" @selected(old('status') === 'blocked')>Diblokir</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Simpan
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
            const durationHintEl = document.getElementById('service_duration_hint');
            const allRoomOptions = Array.from(roomEl.querySelectorAll('option'));

            const updateHint = () => {
                const selectedService = serviceEl.options[serviceEl.selectedIndex];
                const duration = selectedService?.dataset?.duration;

                if (selectedService?.value && duration) {
                    durationHintEl.textContent = `Durasi slot wajib ${duration} menit untuk service ini.`;
                    return;
                }

                durationHintEl.textContent = 'Pilih service terlebih dahulu. Durasi slot harus sama dengan durasi service.';
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
</x-owner-layout>
