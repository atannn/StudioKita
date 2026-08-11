<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Booking - {{ $tenant->nama }}
                </h2>
                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    {{ $tenant->alamat ?? '-' }}
                </div>
            </div>
            <a href="{{ route('studios.show', $tenant->slug) }}"
               class="px-4 py-2 rounded border border-gray-300 dark:border-gray-700">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded dark:bg-red-900/30 dark:text-red-200">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('studios.booking.store', $tenant->slug) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block mb-1 font-medium">Service</label>
                            <select id="serviceSelect" name="service_idservice"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="">-- Pilih Service --</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->idservice }}">
                                        {{ $s->nama_service }} ({{ $s->tipe_service }})
                                        - WD Rp {{ number_format($s->weekday_price ?? 0,0,',','.') }}
                                        / WE Rp {{ number_format($s->weekend_price ?? 0,0,',','.') }}
                                    </option>
                                @endforeach
                            </select>
                            <p id="serviceInfo" class="mt-1 text-sm text-gray-500 dark:text-gray-400"></p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Room</label>
                            <select id="roomSelect" name="rooms_idrooms"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    required disabled>
                                <option value="">-- Pilih Service dulu --</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tanggal</label>
                            <input id="tanggalInput" type="date"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-2 font-medium">Slot Jadwal</label>
                            <div id="slotBox" class="space-y-2">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Pilih service, room, dan tanggal untuk melihat slot tersedia.
                                </div>
                            </div>
                            <input type="hidden" name="Jadwal_idJadwal" id="jadwalIdInput">
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                Buat Booking
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <script>
        const serviceSelect = document.getElementById('serviceSelect');
        const roomSelect = document.getElementById('roomSelect');
        const tanggalInput = document.getElementById('tanggalInput');
        const slotBox = document.getElementById('slotBox');
        const jadwalIdInput = document.getElementById('jadwalIdInput');
        const serviceInfo = document.getElementById('serviceInfo');

        async function loadRooms() {
            const serviceId = serviceSelect.value;
            roomSelect.disabled = true;
            roomSelect.innerHTML = `<option value="">Loading...</option>`;
            slotBox.innerHTML = `<div class="text-sm text-gray-500 dark:text-gray-400">Pilih room & tanggal untuk melihat slot.</div>`;
            jadwalIdInput.value = '';

            if (!serviceId) {
                roomSelect.innerHTML = `<option value="">-- Pilih Service dulu --</option>`;
                serviceInfo.textContent = '';
                return;
            }

            const url = `{{ route('studios.booking.rooms', $tenant->slug) }}?service_id=${serviceId}`;
            const res = await fetch(url);
            const data = await res.json();

            serviceInfo.textContent = `Durasi: ${data.service.durasi_menit} menit | Tipe: ${data.service.tipe_service}`;

            roomSelect.innerHTML = `<option value="">-- Pilih Room --</option>`;
            data.rooms.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.idrooms;
                opt.textContent = `${r.nama_ruangan} (kap: ${r.kapasitas ?? '-'})`;
                roomSelect.appendChild(opt);
            });

            roomSelect.disabled = false;
        }

        async function loadSlots() {
            const roomId = roomSelect.value;
            const tanggal = tanggalInput.value;
            jadwalIdInput.value = '';

            if (!roomId || !tanggal) {
                slotBox.innerHTML = `<div class="text-sm text-gray-500 dark:text-gray-400">Pilih room & tanggal untuk melihat slot.</div>`;
                return;
            }

            slotBox.innerHTML = `<div class="text-sm text-gray-500 dark:text-gray-400">Loading slot...</div>`;

            const url = `{{ route('studios.booking.slots', $tenant->slug) }}?room_id=${roomId}&tanggal=${tanggal}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.slots || data.slots.length === 0) {
                slotBox.innerHTML = `<div class="text-sm text-gray-500 dark:text-gray-400">Tidak ada slot available.</div>`;
                return;
            }

            slotBox.innerHTML = '';
            data.slots.forEach(s => {
                const wrap = document.createElement('div');
                wrap.className = "flex items-center gap-2";

                const radio = document.createElement('input');
                radio.type = "radio";
                radio.name = "slot_radio";
                radio.value = s.idJadwal;
                radio.addEventListener('change', () => jadwalIdInput.value = s.idJadwal);

                const label = document.createElement('label');
                label.className = "cursor-pointer";
                label.textContent = `${s.tanggal} (${String(s.waktu_mulai).slice(0,5)} - ${String(s.waktu_selesai).slice(0,5)})`;

                wrap.appendChild(radio);
                wrap.appendChild(label);
                slotBox.appendChild(wrap);
            });
        }

        serviceSelect.addEventListener('change', async () => {
            await loadRooms();
            await loadSlots();
        });
        roomSelect.addEventListener('change', loadSlots);
        tanggalInput.addEventListener('change', loadSlots);
    </script>
</x-app-layout>
