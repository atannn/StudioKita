<x-app-layout :hide-nav="true">
    @php
        $bookingDate = $booking->jadwal?->tanggal;
        $bookingTime = $booking->jadwal
            ? substr((string) $booking->jadwal->waktu_mulai, 0, 5).' - '.substr((string) $booking->jadwal->waktu_selesai, 0, 5)
            : '-';
        $isWeekend = $bookingDate ? \Carbon\Carbon::parse($bookingDate)->isWeekend() : false;
        $dayTypeLabel = $bookingDate ? ($isWeekend ? 'Weekend' : 'Weekday') : '-';
        $serviceBasePrice = $booking->service
            ? (float) ($isWeekend ? $booking->service->weekend_price : $booking->service->weekday_price)
            : (float) $payment->amount;
        $serviceBasePrice = $serviceBasePrice > 0 ? $serviceBasePrice : (float) $payment->amount;
        $bookingTotal = (float) ($bookingTotal ?? $booking->total_harga ?? $payment->amount);
        $paidAmount = (float) ($paidAmount ?? $booking->paid_amount ?? 0);
        $remainingAmount = (float) ($remainingAmount ?? max($bookingTotal - $paidAmount, 0));
        $totalPay = (float) $payment->amount;
        $estimatedRemainingAfterCurrent = max($remainingAmount - $totalPay, 0);
        $isCashPayment = (bool) ($isCashPayment ?? false);
        $cashInstruction = $cashInstruction ?? null;
        $paymentTypeLabel = $paymentTypeLabel ?? 'Pembayaran lunas';
        $bookingPaymentSchemeLabel = $bookingPaymentSchemeLabel ?? 'Lunas';
        $expiresAtIso = $payment->expires_time?->toIso8601String();
        $previewImage = $booking->room?->foto_ruangan
            ? asset('storage/'.$booking->room->foto_ruangan)
            : ($tenant->primaryPhoto?->foto_path ? asset('storage/'.$tenant->primaryPhoto->foto_path) : null);
        $statusLabel = match ($payment->status) {
            'success' => 'Lunas',
            'pending' => $isCashPayment
                ? ((string) $booking->status === 'confirmed' ? 'Booking Terkonfirmasi' : 'Menunggu Konfirmasi Studio')
                : 'Menunggu Pembayaran',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            default => ucfirst((string) $payment->status),
        };
        $statusTone = match ($payment->status) {
            'success' => 'sk-pill-success',
            'pending' => 'sk-pill-pending',
            'failed', 'expired', 'cancelled' => 'sk-pill-danger',
            default => 'sk-pill-neutral',
        };
        $isBypassEnabled = (bool) ($isBypassEnabled ?? false);
        $snapReady = !$isCashPayment && (bool) ($snapClientKey && $payment->snap_token);
        $canCancelBooking = $booking->status === 'pending' && $payment->status !== 'success';
        $payButtonLabel = match ((string) ($payment->payment_type ?? 'full')) {
            'dp' => 'Bayar DP',
            'remaining' => 'Bayar Pelunasan',
            default => 'Lanjutkan Pembayaran',
        };
        $remainingPaymentDeadline = $remainingPaymentDeadline ?? null;
        $remainingPaymentDeadlineLabel = $remainingPaymentDeadline
            ? $remainingPaymentDeadline->format('d M Y H:i')
            : null;
        $isRemainingPaymentWindowClosed = (bool) ($isRemainingPaymentWindowClosed ?? false);
        $checkoutNotice = null;
        $checkoutNoticeTone = 'danger';
        if ($isCashPayment) {
            $checkoutNotice = ((string) $booking->status === 'confirmed'
                    ? 'Booking sudah dikonfirmasi studio. '
                    : 'Pembayaran dilakukan langsung di studio. ')
                .($cashInstruction
                    ? $cashInstruction
                    : 'Datang sesuai jadwal lalu lakukan pembayaran cash ke pemilik/admin studio.');
            $checkoutNoticeTone = 'default';
        } elseif ($isRemainingPaymentWindowClosed && (string) ($payment->payment_type ?? '') === 'remaining' && $payment->status !== 'success') {
            $checkoutNotice = $remainingPaymentDeadlineLabel
                ? 'Batas waktu pelunasan sudah lewat. Pelunasan maksimal sampai '.$remainingPaymentDeadlineLabel.'.'
                : 'Batas waktu pelunasan sudah lewat.';
            $checkoutNoticeTone = 'danger';
        } elseif ($isBypassEnabled) {
            $checkoutNotice = 'Mode bypass aktif. Klik "'.$payButtonLabel.'" untuk menandai pembayaran berhasil tanpa popup Midtrans.';
            $checkoutNoticeTone = 'default';
        } elseif (!$isPaymentConfigured) {
            $checkoutNotice = 'Pembayaran belum bisa dilanjutkan karena studio ini belum mengaktifkan Midtrans.';
        } elseif (!$snapReady && $payment->status !== 'success') {
            $checkoutNotice = 'Checkout Midtrans belum siap. Silakan refresh halaman atau coba lagi beberapa saat.';
        }
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

        .sk-card {
            background: var(--card);
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
        }

        .sk-input {
            width: 100%;
            border: 1px solid rgba(15, 23, 42, 0.15);
            border-radius: 12px;
            padding: 0.75rem 0.85rem;
            color: var(--ink);
            background: #ffffff;
        }

        .sk-btn-main {
            background: linear-gradient(135deg, var(--primary), #10b981);
            color: #ffffff;
            border-radius: 12px;
            padding: 0.78rem 1rem;
            font-weight: 600;
            box-shadow: 0 14px 26px rgba(15, 118, 110, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sk-btn-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 28px rgba(15, 118, 110, 0.3);
        }

        .sk-btn-main:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .sk-btn-soft {
            background: #ffffff;
            color: var(--primary-dark);
            border: 1px solid rgba(15, 118, 110, 0.22);
            border-radius: 12px;
            padding: 0.78rem 1rem;
            font-weight: 600;
        }

        .sk-btn-danger {
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #ffffff;
            border-radius: 12px;
            padding: 0.78rem 1rem;
            font-weight: 600;
            box-shadow: 0 14px 24px rgba(239, 68, 68, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sk-btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 30px rgba(239, 68, 68, 0.28);
        }

        .sk-btn-danger-compact {
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #ffffff;
            border-radius: 10px;
            padding: 0.5rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
            line-height: 1.15;
            box-shadow: 0 10px 18px rgba(239, 68, 68, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sk-btn-danger-compact:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(239, 68, 68, 0.26);
        }

        .sk-booking-main {
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .sk-booking-cancel-wrap {
            margin-top: auto;
            padding-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }

        .sk-hero-glow {
            background:
                radial-gradient(circle at 12% 10%, rgba(16, 185, 129, 0.18), transparent 52%),
                radial-gradient(circle at 88% 10%, rgba(99, 102, 241, 0.16), transparent 46%),
                linear-gradient(120deg, #d9efe2 0%, #f2e6d6 55%, #f6d9c9 100%);
        }

        .sk-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.28rem 0.78rem;
            font-size: 0.74rem;
            font-weight: 700;
        }

        .sk-pill-pending {
            background: rgba(245, 158, 11, 0.16);
            color: #b45309;
        }

        .sk-pill-success {
            background: rgba(16, 185, 129, 0.16);
            color: #047857;
        }

        .sk-pill-danger {
            background: rgba(239, 68, 68, 0.16);
            color: #b91c1c;
        }

        .sk-pill-neutral {
            background: rgba(71, 85, 105, 0.16);
            color: #334155;
        }

        .sk-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #f1f5f9;
            color: #1f2937;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.28rem 0.7rem;
        }

        .sk-photo-wrap {
            width: 136px;
            min-width: 136px;
            height: 108px;
            border-radius: 14px;
            overflow: hidden;
            background: #eef2f7;
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .sk-photo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sk-summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            font-size: 0.9rem;
            color: var(--muted);
        }

        .sk-summary-row strong {
            color: var(--ink);
        }

        .sk-total {
            border-top: 1px dashed rgba(148, 163, 184, 0.4);
            padding-top: 0.85rem;
            margin-top: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sk-total-amount {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--primary-dark);
            font-family: "Space Grotesk", sans-serif;
        }

        .sk-live-status {
            border-radius: 12px;
            padding: 0.7rem 0.85rem;
            font-size: 0.82rem;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: #f8fafc;
            color: #334155;
        }
    </style>

    <div class="sk-page min-h-screen sk-hero-glow">
        <div class="max-w-6xl mx-auto px-5 sm:px-6 py-10">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <div class="text-xs uppercase tracking-[0.3em] text-[var(--muted)]">Payment Checkout</div>
                    <h1 class="sk-title text-3xl sm:text-4xl font-semibold mt-2">Review booking kamu</h1>
                    <p class="text-sm text-[var(--muted)] mt-2">
                        {{ $isCashPayment
                            ? 'Periksa detail booking cash dan ikuti instruksi pembayaran langsung di studio.'
                            : 'Periksa detail booking dan lanjutkan pembayaran dengan Midtrans.' }}
                    </p>
                </div>
                <a href="{{ route('customer.profile') }}" class="sk-btn-soft inline-flex items-center justify-center">
                    Kembali ke profile
                </a>
            </div>

            @if (session('success'))
                <div class="mt-6 p-4 bg-emerald-100 text-emerald-800 rounded-2xl">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 p-4 bg-red-100 text-red-800 rounded-2xl">
                    <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc ml-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-8 grid lg:grid-cols-[1.25fr_0.85fr] gap-6">
                <div class="space-y-6">
                    <div class="sk-card p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm text-[var(--muted)]">Studio: <strong>{{ $tenant->nama }}</strong></div>
                            <span class="sk-pill {{ $statusTone }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="mt-5 flex flex-col sm:flex-row gap-4">
                            <div class="sk-photo-wrap">
                                @if ($previewImage)
                                    <img src="{{ $previewImage }}" alt="Preview studio">
                                @else
                                    <div class="h-full flex items-center justify-center text-xs text-slate-500">
                                        Belum ada foto
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 sk-booking-main">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                    <div>
                                        <h2 class="sk-title text-2xl font-semibold">{{ $booking->service?->nama_service ?? 'Layanan studio' }}</h2>
                                        <div class="text-sm text-[var(--muted)] mt-1">
                                            {{ $booking->room?->nama_ruangan ?? '-' }} - {{ $booking->room?->tipe_ruangan ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <div class="text-xs text-[var(--muted)]">{{ $booking->service?->durasi_menit ?? '-' }} menit</div>
                                        <div class="sk-title text-2xl font-semibold mt-1">
                                            Rp {{ number_format($totalPay, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="sk-chip">{{ ucfirst((string) ($booking->service?->tipe_service ?? '-')) }}</span>
                                    <span class="sk-chip">{{ $dayTypeLabel }}</span>
                                    <span class="sk-chip">Booking #{{ $booking->idbooking }}</span>
                                </div>

                                <div class="mt-4 text-sm text-[var(--muted)]">
                                    <div class="font-semibold text-slate-800">Jadwal:</div>
                                    <div>
                                        {{ $bookingDate ? \Carbon\Carbon::parse($bookingDate)->format('d M Y') : '-' }} | {{ $bookingTime }}
                                    </div>
                                </div>

                                @if ($canCancelBooking)
                                    <div class="sk-booking-cancel-wrap">
                                        <form method="POST"
                                              action="{{ route('studios.payments.cancel-booking', ['tenant' => $tenant->slug, 'paymentId' => $payment->idpayments]) }}"
                                              onsubmit="return confirm('Yakin batalkan booking ini? Slot jadwal akan dibuka kembali.')">
                                            @csrf
                                            <button type="submit" class="sk-btn-danger-compact inline-flex items-center justify-center">
                                                Batalkan booking
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="sk-card p-6">
                        <div class="text-sm font-semibold text-slate-800">Detail transaksi</div>
                        <div class="mt-4 grid sm:grid-cols-2 gap-4 text-sm">
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/70">
                                <div class="text-xs uppercase tracking-wider text-slate-500">{{ $isCashPayment ? 'Referensi Booking' : 'Order ID' }}</div>
                                <div class="font-mono text-[13px] text-slate-900 mt-1 break-all">
                                    {{ $isCashPayment ? ('CASH-BOOKING-'.$booking->idbooking) : ($payment->midtrans_order_id ?: '-') }}
                                </div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/70">
                                <div class="text-xs uppercase tracking-wider text-slate-500">Day Type Harga</div>
                                <div class="font-semibold text-slate-900 mt-1">{{ $dayTypeLabel }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/70">
                                <div class="text-xs uppercase tracking-wider text-slate-500">Skema Booking</div>
                                <div class="font-semibold text-slate-900 mt-1">{{ $bookingPaymentSchemeLabel }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/70">
                                <div class="text-xs uppercase tracking-wider text-slate-500">Jenis Tagihan</div>
                                <div class="font-semibold text-slate-900 mt-1">{{ $paymentTypeLabel }}</div>
                            </div>
                            @if ((string) ($payment->payment_type ?? '') === 'remaining')
                                <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/70">
                                    <div class="text-xs uppercase tracking-wider text-slate-500">Batas Pelunasan</div>
                                    <div class="font-semibold text-slate-900 mt-1">{{ $remainingPaymentDeadlineLabel ?? '-' }}</div>
                                </div>
                            @endif
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/70">
                                <div class="text-xs uppercase tracking-wider text-slate-500">Kontak Studio</div>
                                <div class="font-semibold text-slate-900 mt-1">{{ $tenant->no_telp ?? '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/70">
                                <div class="text-xs uppercase tracking-wider text-slate-500">Status Booking</div>
                                <div class="font-semibold text-slate-900 mt-1">{{ ucfirst((string) $booking->status) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm">
                        {{ $isCashPayment
                            ? 'Booking cash sudah tersimpan. Datang sesuai jadwal dan lakukan pembayaran langsung di studio.'
                            : 'Pembayaran diproses aman lewat Midtrans. Jangan tutup halaman sebelum popup pembayaran selesai.' }}
                    </div>

                    @if ($checkoutNotice)
                        <div class="rounded-2xl p-4 bg-amber-50 border border-amber-200 text-amber-900 text-sm">
                            {{ $checkoutNotice }}
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="sk-card p-6">
                        <div class="text-xl font-semibold sk-title">Checkout Summary</div>

                        <div class="mt-4">
                            <input type="text" class="sk-input text-sm" placeholder="Kode promo (segera hadir)" disabled>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div class="sk-summary-row">
                                <span>Skema booking</span>
                                <strong>{{ $bookingPaymentSchemeLabel }}</strong>
                            </div>
                            <div class="sk-summary-row">
                                <span>Jenis tagihan</span>
                                <strong>{{ $paymentTypeLabel }}</strong>
                            </div>
                            <div class="sk-summary-row">
                                <span>Harga layanan ({{ $dayTypeLabel }})</span>
                                <strong>Rp {{ number_format($serviceBasePrice, 0, ',', '.') }}</strong>
                            </div>
                            <div class="sk-summary-row">
                                <span>Tagihan saat ini</span>
                                <strong>Rp {{ number_format($totalPay, 0, ',', '.') }}</strong>
                            </div>
                            <div class="sk-summary-row">
                                <span>Sudah dibayar</span>
                                <strong>Rp {{ number_format($paidAmount, 0, ',', '.') }}</strong>
                            </div>
                            <div class="sk-summary-row">
                                <span>Sisa saat ini</span>
                                <strong>Rp {{ number_format($remainingAmount, 0, ',', '.') }}</strong>
                            </div>
                            <div class="sk-summary-row">
                                <span>Estimasi sisa setelah ini</span>
                                <strong>Rp {{ number_format($estimatedRemainingAfterCurrent, 0, ',', '.') }}</strong>
                            </div>
                            <div class="sk-summary-row">
                                <span>Status tagihan</span>
                                <strong>{{ $statusLabel }}</strong>
                            </div>
                        </div>

                        <div class="sk-total">
                            <span class="text-sm text-slate-500">Total booking</span>
                            <span class="sk-total-amount">Rp {{ number_format($bookingTotal, 0, ',', '.') }}</span>
                        </div>

                        <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm font-semibold text-slate-800">Metode pembayaran</div>
                            <div class="mt-2 text-sm text-slate-600">
                                {{ $isCashPayment
                                    ? 'Cash langsung di studio'
                                    : 'Midtrans (Transfer bank, e-wallet, QRIS, kartu)' }}
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Batas waktu</div>
                            <div id="payment-countdown" class="text-base font-semibold text-slate-900 mt-1">
                                {{ $isCashPayment
                                    ? 'Bayar saat hadir di studio'
                                    : ($expiresAtIso
                                        ? 'Menghitung waktu...'
                                        : ((string) ($payment->payment_type ?? '') === 'remaining'
                                            ? 'Maksimal 15 menit sebelum jadwal selesai'
                                            : 'Mengikuti batas waktu dari Midtrans')) }}
                            </div>
                        </div>

                        <div class="mt-4 sk-live-status" id="payment-live-status">
                            {{ $isCashPayment
                                ? 'Menunggu konfirmasi pembayaran cash dari studio.'
                                : 'Klik tombol "'.$payButtonLabel.'" untuk membuka popup Midtrans.' }}
                        </div>

                        @if (!$isCashPayment)
                            <button id="pay-button"
                                type="button"
                                class="w-full mt-5 sk-btn-main"
                                @disabled((!$isBypassEnabled && !$snapReady) || $payment->status === 'success' || $isRemainingPaymentWindowClosed)>
                                {{ $payment->status === 'success' ? 'Pembayaran Selesai' : $payButtonLabel }}
                            </button>
                        @endif

                        @if ($canCancelBooking)
                            <form method="POST"
                                  action="{{ route('studios.payments.cancel-booking', ['tenant' => $tenant->slug, 'paymentId' => $payment->idpayments]) }}"
                                  class="mt-3"
                                  onsubmit="return confirm('Yakin batalkan booking ini? Slot jadwal akan dibuka kembali.')">
                                @csrf
                                <button type="submit" class="w-full sk-btn-danger">
                                    Batalkan Booking
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('studios.show', $tenant->slug) }}"
                           class="w-full mt-3 sk-btn-soft inline-flex items-center justify-center">
                            Kembali ke detail studio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($snapReady)
        <script src="{{ $snapJsUrl }}" data-client-key="{{ $snapClientKey }}"></script>
    @endif

    <script>
        (function () {
            const countdownEl = document.getElementById('payment-countdown');
            const liveStatusEl = document.getElementById('payment-live-status');
            const payButton = document.getElementById('pay-button');
            const expiresAt = @json($expiresAtIso);
            const snapToken = @json($payment->snap_token);
            const backUrl = @json(route('studios.show', $tenant->slug));
            const successRedirectUrl = @json(route('customer.profile'));
            const snapReady = @json($snapReady);
            const checkoutNotice = @json($checkoutNotice);
            const checkoutNoticeTone = @json($checkoutNoticeTone);
            const isBypassEnabled = @json($isBypassEnabled);
            const payButtonDefaultLabel = @json($payButtonLabel);
            const isRemainingPaymentWindowClosed = @json($isRemainingPaymentWindowClosed);
            const bypassUrl = @json(route('studios.payments.bypass-success', ['tenant' => $tenant->slug, 'paymentId' => $payment->idpayments]));
            const csrfToken = @json(csrf_token());

            function updateLiveStatus(message, tone) {
                if (!liveStatusEl) return;
                liveStatusEl.textContent = message;
                liveStatusEl.style.borderColor = tone === 'danger'
                    ? 'rgba(239, 68, 68, 0.32)'
                    : (tone === 'success' ? 'rgba(16, 185, 129, 0.32)' : 'rgba(148, 163, 184, 0.3)');
                liveStatusEl.style.background = tone === 'danger'
                    ? 'rgba(254, 242, 242, 1)'
                    : (tone === 'success' ? 'rgba(236, 253, 245, 1)' : '#f8fafc');
                liveStatusEl.style.color = tone === 'danger'
                    ? '#b91c1c'
                    : (tone === 'success' ? '#047857' : '#334155');
            }

            if (checkoutNotice) {
                updateLiveStatus(checkoutNotice, checkoutNoticeTone || 'default');
            }

            function startCountdown(isoDate) {
                if (!countdownEl || !isoDate) return;

                const target = new Date(isoDate);
                if (Number.isNaN(target.getTime())) {
                    countdownEl.textContent = 'Format batas waktu tidak valid';
                    return;
                }

                const render = () => {
                    const diffMs = target.getTime() - Date.now();
                    if (diffMs <= 0) {
                        countdownEl.textContent = 'Waktu pembayaran habis';
                        updateLiveStatus('Waktu pembayaran habis. Buat booking baru untuk melanjutkan.', 'danger');
                        if (payButton) payButton.disabled = true;
                        return false;
                    }

                    const totalSec = Math.floor(diffMs / 1000);
                    const hours = Math.floor(totalSec / 3600);
                    const mins = Math.floor((totalSec % 3600) / 60);
                    const secs = totalSec % 60;
                    countdownEl.textContent = `${hours}j ${mins}m ${secs}d`;
                    return true;
                };

                if (!render()) return;
                const timer = setInterval(() => {
                    if (!render()) clearInterval(timer);
                }, 1000);
            }

            if (payButton) {
                payButton.addEventListener('click', function () {
                    if (isBypassEnabled) {
                        if (isRemainingPaymentWindowClosed) {
                            updateLiveStatus('Batas waktu pelunasan sudah lewat.', 'danger');
                            return;
                        }

                        payButton.disabled = true;
                        payButton.textContent = 'Memproses bypass...';
                        updateLiveStatus('Mode bypass aktif. Menyelesaikan pembayaran...', 'default');

                        fetch(bypassUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ from_checkout: true }),
                        })
                            .then(async (response) => {
                                const payload = await response.json().catch(() => ({}));
                                if (!response.ok) {
                                    throw new Error(payload.message || 'Bypass gagal diproses.');
                                }

                                updateLiveStatus('Bypass berhasil. Anda akan diarahkan ke halaman profil.', 'success');
                                payButton.textContent = 'Pembayaran Selesai';

                                window.location.href = payload.redirect_to || successRedirectUrl;
                            })
                            .catch(function (error) {
                                updateLiveStatus(error.message || 'Bypass gagal. Coba lagi.', 'danger');
                                payButton.disabled = false;
                                payButton.textContent = payButtonDefaultLabel;
                            });

                        return;
                    }

                    if (!snapReady || !snapToken) {
                        updateLiveStatus('Checkout belum siap. Coba refresh halaman atau hubungi studio.', 'danger');
                        return;
                    }
                    if (isRemainingPaymentWindowClosed) {
                        updateLiveStatus('Batas waktu pelunasan sudah lewat.', 'danger');
                        return;
                    }

                    if (!window.snap) {
                        updateLiveStatus('Library Midtrans belum termuat. Coba beberapa detik lagi.', 'danger');
                        return;
                    }

                    payButton.disabled = true;
                    payButton.textContent = 'Membuka Midtrans...';

                    window.snap.pay(snapToken, {
                        onSuccess: function () {
                            updateLiveStatus('Pembayaran berhasil. Anda akan diarahkan ke halaman profil.', 'success');
                            window.location.href = successRedirectUrl;
                        },
                        onPending: function () {
                            updateLiveStatus('Pembayaran masih pending. Selesaikan pembayaran di kanal yang dipilih.', 'default');
                            payButton.disabled = false;
                            payButton.textContent = payButtonDefaultLabel;
                        },
                        onError: function () {
                            updateLiveStatus('Pembayaran gagal. Silakan coba metode lain atau ulangi lagi.', 'danger');
                            payButton.disabled = false;
                            payButton.textContent = 'Coba Bayar Lagi';
                        },
                        onClose: function () {
                            updateLiveStatus('Popup ditutup sebelum selesai. Anda bisa lanjutkan pembayaran kapan saja.', 'default');
                            payButton.disabled = false;
                            payButton.textContent = payButtonDefaultLabel;
                        }
                    });
                });
            }

            startCountdown(expiresAt);
        })();
    </script>
</x-app-layout>
