<x-app-layout :hide-nav="true">
    @php
        $dpEnabled = (bool) ($dpPolicy['enabled'] ?? true);
        $dpPercent = (int) ($dpPolicy['percent'] ?? 30);
        $cashEnabled = (bool) ($cashPolicy['enabled'] ?? false);
        $cashInstruction = $cashPolicy['instruction'] ?? null;
    @endphp

    <style>
        @import url('https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700&display=swap');
        @import url('https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600&display=swap');
        @import url('https://fonts.bunny.net/css?family=be-vietnam-pro:600,700&display=swap');

        :root {
            --page-bg: #f6f1ea;
            --ink: #0f172a;
            --muted: #4b5563;
            --primary: #0f766e;
            --primary-dark: #0b5f58;
            --card: #ffffff;
            --line: #e5e7eb;
        }

        .sk-page {
            font-family: "IBM Plex Sans", sans-serif;
            color: var(--ink);
            background: var(--page-bg);
        }

        .sk-title {
            font-family: "Space Grotesk", sans-serif;
        }

        .sk-logo {
            font-size: 1.6rem;
            line-height: 1;
        }

        .sk-logo-studio {
            font-family: "Times New Roman", Times, serif;
            font-style: italic;
            font-weight: 400;
            margin-right: 1px;
        }

        .sk-logo-kita {
            font-family: "Be Vietnam Pro", sans-serif;
            font-weight: 700;
        }

        .sk-btn {
            background: linear-gradient(135deg, var(--primary), #10b981);
            color: #ffffff;
            padding: 0.7rem 1.4rem;
            border-radius: 999px;
            font-weight: 600;
            box-shadow: 0 14px 30px rgba(15, 118, 110, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sk-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(15, 118, 110, 0.3);
        }

        .sk-btn-muted {
            background: #ffffff;
            color: var(--primary-dark);
            border: 1px solid rgba(15, 118, 110, 0.2);
            box-shadow: none;
        }

        .sk-btn-muted:hover {
            box-shadow: none;
        }

        .sk-card {
            background: var(--card);
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
        }

        .sk-input {
            border: 1px solid rgba(15, 23, 42, 0.15);
            border-radius: 14px;
            padding: 0.7rem 0.9rem;
            width: 100%;
            background: #ffffff;
            color: var(--ink);
        }

        .sk-input:focus {
            outline: 2px solid rgba(15, 118, 110, 0.25);
            border-color: rgba(15, 118, 110, 0.45);
        }

        .sk-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--muted);
        }

        .sk-hero-glow {
            background:
                radial-gradient(circle at 12% 12%, rgba(16, 185, 129, 0.18), transparent 55%),
                radial-gradient(circle at 85% 20%, rgba(249, 115, 22, 0.18), transparent 55%),
                linear-gradient(120deg, #d9efe2 0%, #f2e6d6 55%, #f6d9c9 100%);
        }

        .sk-step {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .sk-step-indicator {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary-dark);
        }

        .sk-step-active .sk-step-indicator {
            background: var(--primary);
            color: #ffffff;
        }

        .sk-summary-card {
            background: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 18px;
            padding: 18px;
        }

        .sk-room-preview {
            border: 1px dashed rgba(15, 23, 42, 0.18);
            border-radius: 18px;
            padding: 14px;
            background: #f8fafc;
        }

        .sk-room-preview img {
            width: 100%;
            max-height: 220px;
            object-fit: cover;
            border-radius: 14px;
        }
    </style>

    <div class="sk-page min-h-screen sk-hero-glow">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <div class="text-xs uppercase tracking-[0.3em] text-[var(--muted)]">Booking Studio</div>
                    <h1 class="sk-title text-3xl md:text-4xl font-semibold mt-2">Booking - {{ $tenant->nama }}</h1>
                    <p class="text-sm text-[var(--muted)] mt-2">Pilih layanan, ruangan, tanggal, lalu slot waktu yang tersedia.</p>
                </div>
                <a href="{{ route('studios.show', $tenant->slug) }}" class="sk-btn sk-btn-muted">Kembali ke studio</a>
            </div>

            @if ($errors->any())
                <div class="mt-6 p-4 bg-red-100 text-red-800 rounded-2xl">
                    <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc ml-5 space-y-1 text-sm">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mt-6 p-4 bg-emerald-100 text-emerald-800 rounded-2xl">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mt-8 grid lg:grid-cols-[260px_minmax(0,1fr)] gap-6">
                <div class="sk-card p-5">
                    <div class="text-xs uppercase tracking-[0.2em] text-[var(--muted)]">Langkah</div>
                    <ol class="mt-5 space-y-4 text-sm">
                        <li class="sk-step sk-step-active">
                            <span class="sk-step-indicator">1</span>
                            <div>
                                <div class="font-semibold">Pilih layanan</div>
                                <div class="text-xs text-[var(--muted)]">Tentukan jenis latihan/rekaman.</div>
                            </div>
                        </li>
                        <li class="sk-step">
                            <span class="sk-step-indicator">2</span>
                            <div>
                                <div class="font-semibold">Pilih ruangan</div>
                                <div class="text-xs text-[var(--muted)]">Ruangan akan sesuai tipe layanan.</div>
                            </div>
                        </li>
                        <li class="sk-step">
                            <span class="sk-step-indicator">3</span>
                            <div>
                                <div class="font-semibold">Pilih tanggal</div>
                                <div class="text-xs text-[var(--muted)]">Cari hari yang kamu mau.</div>
                            </div>
                        </li>
                        <li class="sk-step">
                            <span class="sk-step-indicator">4</span>
                            <div>
                                <div class="font-semibold">Pilih slot</div>
                                <div class="text-xs text-[var(--muted)]">Slot tersedia akan muncul otomatis.</div>
                            </div>
                        </li>
                    </ol>
                    <div class="mt-6 p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-800">
                        Tip: pilih layanan dulu agar daftar ruangan langsung tersaring.
                    </div>
                </div>

                <div class="sk-card p-6 lg:p-8">
                    <form method="POST" action="{{ route('studios.booking.store', $tenant->slug) }}" class="grid lg:grid-cols-[1.1fr_0.9fr] gap-6">
                        @csrf

                        <div class="space-y-5">
                            <div>
                                <label class="sk-label">Service</label>
                                <select id="service_idservice" name="service_idservice" class="sk-input mt-2" required>
                                    <option value="">-- pilih service --</option>
                                    @foreach($services as $s)
                                        @php
                                            $weekdayPrice = $s->weekday_price;
                                            $weekendPrice = $s->weekend_price;
                                        @endphp
                                        <option value="{{ $s->idservice }}"
                                                data-type="{{ $s->tipe_service }}"
                                                data-weekday-price="{{ $weekdayPrice }}"
                                                data-weekend-price="{{ $weekendPrice }}"
                                                data-duration="{{ $s->durasi_menit }}"
                                                @selected(old('service_idservice') == $s->idservice)>
                                            {{ $s->nama_service }}
                                            - Weekday Rp {{ number_format($weekdayPrice,0,',','.') }}
                                            / Weekend Rp {{ number_format($weekendPrice,0,',','.') }}
                                            ({{ $s->durasi_menit }} menit)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="sk-label">Room</label>
                                <select id="rooms_idrooms" name="rooms_idrooms" class="sk-input mt-2" required>
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
                                                data-photo="{{ $r->foto_ruangan ? asset('storage/'.$r->foto_ruangan) : '' }}"
                                                data-facilities='@json($roomFacilities)'
                                                @selected(old('rooms_idrooms') == $r->idrooms)>
                                            {{ $r->nama_ruangan }} ({{ $r->tipe_ruangan }})
                                        </option>
                                    @endforeach
                                </select>
                                <div id="roomPreviewWrap" class="sk-room-preview mt-3 hidden">
                                    <div class="text-xs text-[var(--muted)] mb-2">Preview ruangan</div>
                                    <img id="roomPreviewImage" src="" alt="Preview ruangan">
                                    <div id="roomPreviewPlaceholder" class="text-sm text-[var(--muted)] hidden">
                                        Belum ada foto ruangan.
                                    </div>
                                    <div id="roomFacilitiesPreview" class="mt-4 hidden"></div>
                                </div>
                            </div>

                            <div>
                                <label class="sk-label">Tanggal</label>
                                <input id="tanggal" type="date" name="tanggal" class="sk-input mt-2"
                                       value="{{ old('tanggal') }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                            </div>

                            <div>
                                <label class="sk-label">Metode pembayaran</label>
                                <select id="payment_method" name="payment_method" class="sk-input mt-2" required>
                                    <option value="midtrans" @selected(old('payment_method', 'midtrans') === 'midtrans')>Midtrans</option>
                                    @if ($cashEnabled)
                                        <option value="cash" @selected(old('payment_method') === 'cash')>Cash di studio</option>
                                    @endif
                                </select>
                                @if ($cashEnabled)
                                    <div id="cashMethodHint" class="mt-2 text-xs text-[var(--muted)] {{ old('payment_method') === 'cash' ? '' : 'hidden' }}">
                                        {{ $cashInstruction ?: 'Jika memilih cash, pembayaran dilakukan langsung di studio saat hadir.' }}
                                    </div>
                                @endif
                            </div>

                            <div id="paymentSchemeWrap">
                                <label class="sk-label">Skema pembayaran</label>
                                <select id="payment_scheme" name="payment_scheme" class="sk-input mt-2" required>
                                    <option value="full" @selected(old('payment_scheme', 'full') === 'full')>Lunas</option>
                                    @if ($dpEnabled)
                                        <option value="dp" @selected(old('payment_scheme') === 'dp')>DP</option>
                                    @endif
                                </select>
                                @if (!$dpEnabled)
                                    <div class="text-xs text-[var(--muted)] mt-2">
                                        DP sedang dinonaktifkan oleh studio ini.
                                    </div>
                                @endif
                            </div>

                            <div id="dpPercentWrap" class="{{ $dpEnabled && old('payment_scheme') === 'dp' ? '' : 'hidden' }}">
                                <label class="sk-label">Persentase DP</label>
                                <input type="hidden" id="dp_percent" name="dp_percent" value="{{ $dpPercent }}">
                                <div class="mt-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                                    DP ditetapkan studio sebesar <span class="font-semibold">{{ $dpPercent }}%</span>.
                                </div>
                                <div class="text-xs text-[var(--muted)] mt-2">
                                    Sisa pembayaran akan dibuatkan tagihan pelunasan terpisah.
                                </div>
                            </div>

                            <div>
                                <label class="sk-label">Slot Jadwal (Available)</label>
                                <select id="Jadwal_idJadwal" name="Jadwal_idJadwal" class="sk-input mt-2" required>
                                    <option value="">-- pilih service, room & tanggal dulu --</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="sk-summary-card">
                                <div class="text-xs uppercase tracking-[0.2em] text-[var(--muted)]">Ringkasan</div>
                                <div class="mt-4 space-y-3 text-sm">
                                    <div>
                                        <div class="text-xs text-[var(--muted)]">Service</div>
                                        <div id="summaryService" class="font-semibold">-</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-xs text-[var(--muted)]">Tipe</div>
                                            <div id="summaryType" class="font-semibold">-</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--muted)]">Durasi</div>
                                            <div id="summaryDuration" class="font-semibold">-</div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-[var(--muted)]">Ruangan</div>
                                        <div id="summaryRoom" class="font-semibold">-</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-xs text-[var(--muted)]">Tanggal</div>
                                            <div id="summaryDate" class="font-semibold">-</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--muted)]">Slot</div>
                                            <div id="summarySlot" class="font-semibold">-</div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-xs text-[var(--muted)]">Metode bayar</div>
                                            <div id="summaryPaymentMethod" class="font-semibold">Midtrans</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--muted)]">Skema bayar</div>
                                            <div id="summaryPaymentPlan" class="font-semibold">Lunas</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-[var(--muted)]">Total layanan</div>
                                            <div id="summaryServicePrice" class="font-semibold">-</div>
                                        </div>
                                    </div>
                                    <div class="pt-3 border-t border-dashed border-slate-200">
                                        <div class="text-xs text-[var(--muted)]">Tagihan sekarang</div>
                                        <div id="summaryPrice" class="text-lg font-semibold text-[var(--primary-dark)]">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-[var(--muted)]">
                                Pastikan data sudah benar sebelum melakukan booking.
                            </div>
                        </div>

                        <div class="lg:col-span-2 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2">
                            <div class="text-xs text-[var(--muted)]">
                                Slot akan dikunci setelah booking berhasil.
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('studios.show', $tenant->slug) }}" class="sk-btn sk-btn-muted">Batal</a>
                                <button type="submit" class="sk-btn">Booking sekarang</button>
                            </div>
                        </div>
                    </form>

                    <script>
                        const serviceEl = document.getElementById('service_idservice');
                        const roomEl = document.getElementById('rooms_idrooms');
                        const tanggalEl = document.getElementById('tanggal');
                        const paymentMethodEl = document.getElementById('payment_method');
                        const paymentSchemeWrap = document.getElementById('paymentSchemeWrap');
                        const paymentSchemeEl = document.getElementById('payment_scheme');
                        const dpPercentWrap = document.getElementById('dpPercentWrap');
                        const defaultDpPercent = @json($dpPercent);
                        const dpEnabled = @json($dpEnabled);
                        const cashEnabled = @json($cashEnabled);
                        const cashMethodHint = document.getElementById('cashMethodHint');
                        const slotEl = document.getElementById('Jadwal_idJadwal');
                        const roomPreviewWrap = document.getElementById('roomPreviewWrap');
                        const roomPreviewImage = document.getElementById('roomPreviewImage');
                        const roomPreviewPlaceholder = document.getElementById('roomPreviewPlaceholder');
                        const roomFacilitiesPreview = document.getElementById('roomFacilitiesPreview');

                        const summaryService = document.getElementById('summaryService');
                        const summaryType = document.getElementById('summaryType');
                        const summaryDuration = document.getElementById('summaryDuration');
                        const summaryRoom = document.getElementById('summaryRoom');
                        const summaryDate = document.getElementById('summaryDate');
                        const summarySlot = document.getElementById('summarySlot');
                        const summaryPaymentMethod = document.getElementById('summaryPaymentMethod');
                        const summaryPaymentPlan = document.getElementById('summaryPaymentPlan');
                        const summaryServicePrice = document.getElementById('summaryServicePrice');
                        const summaryPrice = document.getElementById('summaryPrice');

                        const allRoomOptions = Array.from(roomEl.querySelectorAll('option'));

                        function formatRupiah(value) {
                            if (!value) return '-';
                            const number = parseInt(value, 10);
                            return `Rp ${number.toLocaleString('id-ID')}`;
                        }

                        function formatDate(value) {
                            if (!value) return '-';
                            const parts = value.split('-').map(Number);
                            if (parts.length !== 3) return value;
                            const date = new Date(parts[0], parts[1] - 1, parts[2]);
                            return new Intl.DateTimeFormat('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            }).format(date);
                        }

                        function resetSlots(message = '-- pilih service, room & tanggal dulu --') {
                            slotEl.innerHTML = `<option value="">${message}</option>`;
                        }

                        function updateSummary() {
                            const serviceOpt = serviceEl.options[serviceEl.selectedIndex];
                            const roomOpt = roomEl.options[roomEl.selectedIndex];
                            const dateValue = tanggalEl.value;
                            const paymentMethod = paymentMethodEl.value;
                            const paymentScheme = paymentSchemeEl.value;
                            const selectedDpPercent = Number.parseInt(defaultDpPercent || 0, 10);

                            summaryService.textContent = serviceOpt?.value ? serviceOpt.textContent : '-';
                            summaryType.textContent = serviceOpt?.dataset?.type ?? '-';
                            summaryDuration.textContent = serviceOpt?.dataset?.duration ? `${serviceOpt.dataset.duration} menit` : '-';
                            let selectedPrice = null;
                            if (serviceOpt?.value && dateValue) {
                                const day = new Date(`${dateValue}T00:00:00`).getDay();
                                const isWeekend = day === 0 || day === 6;
                                const basePrice = isWeekend
                                    ? serviceOpt.dataset.weekendPrice
                                    : serviceOpt.dataset.weekdayPrice;
                                selectedPrice = basePrice ? Number.parseFloat(basePrice) : null;
                            }

                            summaryServicePrice.textContent = selectedPrice ? formatRupiah(selectedPrice) : '-';

                            let payableNow = selectedPrice;
                            let paymentPlanLabel = 'Lunas';
                            if (paymentMethod === 'cash') {
                                paymentPlanLabel = 'Lunas';
                            } else if (paymentScheme === 'dp') {
                                paymentPlanLabel = selectedDpPercent > 0 ? `DP ${selectedDpPercent}%` : 'DP';
                                payableNow = selectedPrice && selectedDpPercent > 0
                                    ? Math.max(1, Math.round((selectedPrice * selectedDpPercent) / 100))
                                    : null;
                            }

                            summaryPaymentMethod.textContent = paymentMethod === 'cash' ? 'Cash di studio' : 'Midtrans';
                            summaryPaymentPlan.textContent = paymentPlanLabel;
                            summaryPrice.textContent = payableNow ? formatRupiah(payableNow) : '-';
                            summaryRoom.textContent = roomOpt?.value ? roomOpt.textContent : '-';
                            summaryDate.textContent = formatDate(tanggalEl.value);
                            summarySlot.textContent = slotEl.value ? slotEl.options[slotEl.selectedIndex].textContent : '-';
                        }

                        function updatePaymentFieldsVisibility() {
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

                        function updateRoomPreview() {
                            const roomOpt = roomEl.options[roomEl.selectedIndex];
                            const photoUrl = roomOpt?.dataset?.photo;
                            const facilities = parseRoomFacilities(roomOpt);

                            if (roomOpt?.value && photoUrl) {
                                roomPreviewImage.src = photoUrl;
                                roomPreviewImage.classList.remove('hidden');
                                roomPreviewPlaceholder.classList.add('hidden');
                                roomPreviewWrap.classList.remove('hidden');
                            } else {
                                roomPreviewImage.src = '';
                                roomPreviewImage.classList.add('hidden');
                                if (roomOpt?.value) {
                                    roomPreviewPlaceholder.classList.remove('hidden');
                                    roomPreviewWrap.classList.remove('hidden');
                                } else {
                                    roomPreviewPlaceholder.classList.add('hidden');
                                    roomPreviewWrap.classList.add('hidden');
                                }
                            }

                            if (roomOpt?.value && roomFacilitiesPreview) {
                                roomFacilitiesPreview.innerHTML = renderFacilitiesTable(facilities);
                                roomFacilitiesPreview.classList.remove('hidden');
                            } else if (roomFacilitiesPreview) {
                                roomFacilitiesPreview.innerHTML = '';
                                roomFacilitiesPreview.classList.add('hidden');
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
                                return '<div class="text-sm text-[var(--muted)]">Fasilitas ruangan belum diatur.</div>';
                            }

                            const rows = facilities.map((facility) => `
                                <tr>
                                    <td class="border border-slate-200 px-3 py-2 font-semibold text-slate-800">${escapeHtml(facility.name)}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-600">${escapeHtml(facility.description || '-')}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-center text-slate-700">${escapeHtml(facility.quantity || 1)}</td>
                                </tr>
                            `).join('');

                            return `
                                <div class="text-xs uppercase tracking-[0.16em] text-[var(--muted)]">Fasilitas ruangan</div>
                                <div class="mt-2 overflow-x-auto">
                                    <table class="w-full border-collapse text-sm">
                                        <thead>
                                            <tr class="bg-slate-50">
                                                <th class="border border-slate-200 px-3 py-2 text-left text-xs uppercase tracking-wide text-slate-500">Nama</th>
                                                <th class="border border-slate-200 px-3 py-2 text-left text-xs uppercase tracking-wide text-slate-500">Deskripsi</th>
                                                <th class="border border-slate-200 px-3 py-2 text-center text-xs uppercase tracking-wide text-slate-500">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>${rows}</tbody>
                                    </table>
                                </div>
                            `;
                        }

                        function filterRoomsByServiceType() {
                            const selectedServiceOpt = serviceEl.options[serviceEl.selectedIndex];
                            const serviceType = selectedServiceOpt?.dataset?.type;

                            if (!serviceType) {
                                allRoomOptions.forEach(opt => opt.hidden = false);
                                roomEl.value = '';
                                resetSlots();
                                updateRoomPreview();
                                updateSummary();
                                return;
                            }

                            allRoomOptions.forEach(opt => {
                                if (!opt.value) { opt.hidden = false; return; }
                                opt.hidden = (opt.dataset.type !== serviceType);
                            });

                            const selectedRoomOpt = roomEl.options[roomEl.selectedIndex];
                            const currentRoomType = selectedRoomOpt?.dataset?.type;

                            if (!roomEl.value || currentRoomType !== serviceType) {
                                roomEl.value = '';
                            }

                            resetSlots('-- pilih room & tanggal --');
                            updateRoomPreview();
                            updateSummary();
                        }

                        async function loadSlots() {
                            const serviceOpt = serviceEl.options[serviceEl.selectedIndex];
                            const serviceType = serviceOpt?.dataset?.type;
                            const serviceId = serviceEl.value;
                            const roomId = roomEl.value;
                            const tgl = tanggalEl.value;

                            if (!serviceType || !serviceId) {
                                resetSlots('-- pilih service dulu --');
                                updateSummary();
                                return;
                            }
                            if (!roomId || !tgl) {
                                resetSlots('-- pilih room & tanggal --');
                                updateSummary();
                                return;
                            }

                            slotEl.innerHTML = '<option value="">-- loading... --</option>';

                            const url = `{{ route('studios.booking.slots', $tenant->slug) }}?service_idservice=${encodeURIComponent(serviceId)}&rooms_idrooms=${encodeURIComponent(roomId)}&tanggal=${encodeURIComponent(tgl)}`;
                            const res = await fetch(url);
                            const data = await res.json();

                            if (!Array.isArray(data) || data.length === 0) {
                                resetSlots('-- slot kosong --');
                                updateSummary();
                                return;
                            }

                            slotEl.innerHTML = '<option value="">-- pilih slot --</option>';
                            data.forEach(s => {
                                const opt = document.createElement('option');
                                opt.value = s.idJadwal;
                                opt.textContent = `${String(s.waktu_mulai).slice(0,5)} - ${String(s.waktu_selesai).slice(0,5)}`;
                                slotEl.appendChild(opt);
                            });
                            updateSummary();
                        }

                        serviceEl.addEventListener('change', () => {
                            filterRoomsByServiceType();
                        });

                        roomEl.addEventListener('change', () => {
                            updateRoomPreview();
                            loadSlots();
                        });

                        tanggalEl.addEventListener('change', () => {
                            loadSlots();
                            updateSummary();
                        });

                        slotEl.addEventListener('change', () => {
                            updateSummary();
                        });

                        paymentMethodEl.addEventListener('change', () => {
                            updatePaymentFieldsVisibility();
                            updateSummary();
                        });

                        paymentSchemeEl.addEventListener('change', () => {
                            updatePaymentFieldsVisibility();
                            updateSummary();
                        });

                        filterRoomsByServiceType();
                        updatePaymentFieldsVisibility();
                        updateRoomPreview();
                        updateSummary();
                        if (roomEl.value && tanggalEl.value) {
                            loadSlots();
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
