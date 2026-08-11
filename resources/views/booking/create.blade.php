<x-app-layout>
    @php
        $dpEnabled = (bool) ($dpPolicy['enabled'] ?? true);
        $dpPercent = (int) ($dpPolicy['percent'] ?? 30);
        $cashEnabled = (bool) ($cashPolicy['enabled'] ?? false);
        $cashInstruction = $cashPolicy['instruction'] ?? null;
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Buat Booking
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded dark:bg-red-900/30 dark:text-red-200">
                    <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc ml-5 space-y-1">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded dark:bg-green-900/30 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('booking.store') }}">
                        @csrf

                        <div class="space-y-5">

                            {{-- ✅ SERVICE (DI ATAS) --}}
                            <div>
                                <label class="block mb-1 font-medium">Service</label>
                                <select id="service_idservice" name="service_idservice"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="">-- pilih service --</option>
                                    @foreach($services as $s)
                                        @php
                                            $weekdayPrice = $s->weekday_price;
                                            $weekendPrice = $s->weekend_price;
                                        @endphp
                                        <option value="{{ $s->idservice }}"
                                                data-type="{{ $s->tipe_service }}"
                                                @selected(old('service_idservice') == $s->idservice)>
                                            {{ $s->nama_service }}
                                            - Weekday Rp {{ number_format($weekdayPrice,0,',','.') }}
                                            / Weekend Rp {{ number_format($weekendPrice,0,',','.') }}
                                            ({{ $s->durasi_menit }} menit)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ✅ ROOM (TERFILTER BERDASARKAN SERVICE) --}}
                            <div>
                                <label class="block mb-1 font-medium">Room</label>
                                <select id="rooms_idrooms" name="rooms_idrooms"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="">-- pilih room --</option>
                                    @foreach($rooms as $r)
                                        @php
                                            $roomFacilities = $r->facilities->map(fn ($facility) => [
                                                'name' => $facility->nama_fasilitas,
                                                'description' => $facility->deskripsi ?: '-',
                                                'quantity' => (int) ($facility->quantity ?? 1),
                                            ])->values();
                                        @endphp
                                        <option value="{{ $r->idrooms }}"
                                                data-type="{{ $r->tipe_ruangan }}"
                                                data-facilities='@json($roomFacilities)'
                                                @selected(old('rooms_idrooms') == $r->idrooms)>
                                            {{ $r->nama_ruangan }} ({{ $r->tipe_ruangan }})
                                        </option>
                                    @endforeach
                                </select>
                                <div id="room_facilities_hint" class="mt-3 hidden"></div>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Tanggal</label>
                                <input id="tanggal" type="date" name="tanggal"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                       value="{{ old('tanggal') }}"
                                       required>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Metode Pembayaran</label>
                                <select id="payment_method" name="payment_method"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="midtrans" @selected(old('payment_method', 'midtrans') === 'midtrans')>Midtrans</option>
                                    @if ($cashEnabled)
                                        <option value="cash" @selected(old('payment_method') === 'cash')>Cash di studio</option>
                                    @endif
                                </select>
                                @if ($cashEnabled)
                                    <div id="cash_method_hint" class="text-xs text-gray-500 mt-2 {{ old('payment_method') === 'cash' ? '' : 'hidden' }}">
                                        {{ $cashInstruction ?: 'Pembayaran cash dilakukan langsung di studio saat customer hadir.' }}
                                    </div>
                                @endif
                            </div>

                            <div id="payment_scheme_wrap">
                                <label class="block mb-1 font-medium">Skema Pembayaran</label>
                                <select id="payment_scheme" name="payment_scheme"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="full" @selected(old('payment_scheme', 'full') === 'full')>Lunas</option>
                                    @if ($dpEnabled)
                                        <option value="dp" @selected(old('payment_scheme') === 'dp')>DP</option>
                                    @endif
                                </select>
                                @if (!$dpEnabled)
                                    <div class="text-xs text-gray-500 mt-2">DP sedang dinonaktifkan oleh tenant ini.</div>
                                @endif
                            </div>

                            <div id="dp_percent_wrap" class="{{ $dpEnabled && old('payment_scheme') === 'dp' ? '' : 'hidden' }}">
                                <label class="block mb-1 font-medium">Persentase DP</label>
                                <input type="hidden" id="dp_percent" name="dp_percent" value="{{ $dpPercent }}">
                                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                                    DP ditetapkan tenant sebesar <span class="font-semibold">{{ $dpPercent }}%</span>.
                                </div>
                                <div class="text-xs text-gray-500 mt-2">Sisa pembayaran dibuat sebagai tagihan pelunasan.</div>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Slot Jadwal (Available)</label>
                                <select id="Jadwal_idJadwal" name="Jadwal_idJadwal"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="">-- pilih service, room & tanggal dulu --</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                    Booking
                                </button>

                                <a href="{{ route('dashboard') }}"
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-50
                                          dark:border-gray-600 dark:hover:bg-gray-700">
                                    Batal
                                </a>
                            </div>

                        </div>
                    </form>

                    <script>
                        const serviceEl = document.getElementById('service_idservice');
                        const roomEl = document.getElementById('rooms_idrooms');
                        const tanggalEl = document.getElementById('tanggal');
                        const paymentMethodEl = document.getElementById('payment_method');
                        const paymentSchemeWrap = document.getElementById('payment_scheme_wrap');
                        const paymentSchemeEl = document.getElementById('payment_scheme');
                        const dpPercentWrap = document.getElementById('dp_percent_wrap');
                        const dpEnabled = @json($dpEnabled);
                        const cashEnabled = @json($cashEnabled);
                        const cashMethodHint = document.getElementById('cash_method_hint');
                        const slotEl = document.getElementById('Jadwal_idJadwal');
                        const roomFacilitiesHint = document.getElementById('room_facilities_hint');

                        const allRoomOptions = Array.from(roomEl.querySelectorAll('option'));

                        function resetSlots(message = '-- pilih service, room & tanggal dulu --') {
                            slotEl.innerHTML = `<option value="">${message}</option>`;
                        }

                        function toggleDpFields() {
                            const isCashPayment = cashEnabled && paymentMethodEl.value === 'cash';

                            if (!dpEnabled && paymentSchemeEl.value !== 'full') {
                                paymentSchemeEl.value = 'full';
                            }

                            if (isCashPayment) {
                                paymentSchemeEl.value = 'full';
                            }

                            const isDp = !isCashPayment && dpEnabled && paymentSchemeEl.value === 'dp';
                            paymentSchemeWrap.classList.toggle('hidden', isCashPayment);
                            dpPercentWrap.classList.toggle('hidden', !isDp);
                            if (cashMethodHint) {
                                cashMethodHint.classList.toggle('hidden', !isCashPayment);
                            }
                        }

                        function updateRoomFacilitiesHint() {
                            const roomOpt = roomEl.options[roomEl.selectedIndex];
                            const facilities = parseRoomFacilities(roomOpt);

                            if (!roomFacilitiesHint) {
                                return;
                            }

                            if (roomOpt?.value) {
                                roomFacilitiesHint.innerHTML = renderFacilitiesTable(facilities);
                                roomFacilitiesHint.classList.remove('hidden');
                            } else {
                                roomFacilitiesHint.innerHTML = '';
                                roomFacilitiesHint.classList.add('hidden');
                            }
                        }

                        function parseRoomFacilities(roomOpt) {
                            if (!roomOpt?.dataset?.facilities) {
                                return [];
                            }

                            try {
                                const parsed = JSON.parse(roomOpt.dataset.facilities);
                                return Array.isArray(parsed) ? parsed : [];
                            } catch (error) {
                                return [];
                            }
                        }

                        function escapeHtml(value) {
                            return String(value ?? '')
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        }

                        function renderFacilitiesTable(facilities) {
                            if (!facilities.length) {
                                return '<div class="text-xs text-gray-500">Fasilitas ruangan belum diatur.</div>';
                            }

                            const rows = facilities.map((facility) => `
                                <tr>
                                    <td class="border border-gray-200 px-3 py-2 font-semibold">${escapeHtml(facility.name)}</td>
                                    <td class="border border-gray-200 px-3 py-2 text-gray-600">${escapeHtml(facility.description || '-')}</td>
                                    <td class="border border-gray-200 px-3 py-2 text-center">${escapeHtml(facility.quantity || 1)}</td>
                                </tr>
                            `).join('');

                            return `
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fasilitas ruangan</div>
                                <div class="mt-2 overflow-x-auto">
                                    <table class="w-full border-collapse text-sm">
                                        <thead>
                                            <tr class="bg-gray-50">
                                                <th class="border border-gray-200 px-3 py-2 text-left text-xs uppercase tracking-wide text-gray-500">Nama</th>
                                                <th class="border border-gray-200 px-3 py-2 text-left text-xs uppercase tracking-wide text-gray-500">Deskripsi</th>
                                                <th class="border border-gray-200 px-3 py-2 text-center text-xs uppercase tracking-wide text-gray-500">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>${rows}</tbody>
                                    </table>
                                </div>
                            `;
                        }

                        function filterRoomsByServiceType() {
                            const selectedServiceOpt = serviceEl.options[serviceEl.selectedIndex];
                            const serviceType = selectedServiceOpt?.dataset?.type; // 'rekaman' / 'latihan'

                            // Belum pilih service → tampilkan semua room
                            if (!serviceType) {
                                allRoomOptions.forEach(opt => opt.hidden = false);
                                roomEl.value = '';
                                resetSlots();
                                updateRoomFacilitiesHint();
                                return;
                            }

                            // Tampilkan room yang tipe_ruangan == tipe_service
                            allRoomOptions.forEach(opt => {
                                if (!opt.value) { opt.hidden = false; return; } // placeholder
                                opt.hidden = (opt.dataset.type !== serviceType);
                            });

                            // Reset pilihan room jika tidak sesuai
                            const selectedRoomOpt = roomEl.options[roomEl.selectedIndex];
                            const currentRoomType = selectedRoomOpt?.dataset?.type;

                            if (!roomEl.value || currentRoomType !== serviceType) {
                                roomEl.value = '';
                            }

                            resetSlots('-- pilih room & tanggal --');
                            updateRoomFacilitiesHint();
                        }

                        async function loadSlots() {
                            const serviceOpt = serviceEl.options[serviceEl.selectedIndex];
                            const serviceType = serviceOpt?.dataset?.type;
                            const serviceId = serviceEl.value;

                            const roomId = roomEl.value;
                            const tgl = tanggalEl.value;

                            if (!serviceType || !serviceId) {
                                resetSlots('-- pilih service dulu --');
                                return;
                            }
                            if (!roomId || !tgl) {
                                resetSlots('-- pilih room & tanggal --');
                                return;
                            }

                            slotEl.innerHTML = '<option value="">-- loading... --</option>';

                            const url = `{{ route('booking.slots') }}?service_idservice=${encodeURIComponent(serviceId)}&rooms_idrooms=${encodeURIComponent(roomId)}&tanggal=${encodeURIComponent(tgl)}`;
                            const res = await fetch(url);
                            const data = await res.json();

                            if (!Array.isArray(data) || data.length === 0) {
                                resetSlots('-- slot kosong --');
                                return;
                            }

                            slotEl.innerHTML = '<option value="">-- pilih slot --</option>';
                            data.forEach(s => {
                                const opt = document.createElement('option');
                                opt.value = s.idJadwal;
                                opt.textContent = `${String(s.waktu_mulai).slice(0,5)} - ${String(s.waktu_selesai).slice(0,5)}`;
                                slotEl.appendChild(opt);
                            });
                        }

                        // Event binding
                        serviceEl.addEventListener('change', () => {
                            filterRoomsByServiceType();
                        });

                        roomEl.addEventListener('change', () => {
                            updateRoomFacilitiesHint();
                            loadSlots();
                        });

                        tanggalEl.addEventListener('change', () => {
                            loadSlots();
                        });

                        paymentMethodEl.addEventListener('change', () => {
                            toggleDpFields();
                        });

                        paymentSchemeEl.addEventListener('change', () => {
                            toggleDpFields();
                        });

                        // Initial on load (support old() values)
                        filterRoomsByServiceType();
                        toggleDpFields();
                        updateRoomFacilitiesHint();
                        // kalau old room + old tanggal ada, auto load slot
                        if (roomEl.value && tanggalEl.value) {
                            loadSlots();
                        }
                    </script>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
