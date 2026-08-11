<x-app-layout :hide-nav="true">
    @php
        $statusLabels = [
            'pending' => ['Menunggu konfirmasi', 'bg-amber-100 text-amber-700'],
            'confirmed' => ['Terkonfirmasi', 'bg-emerald-100 text-emerald-700'],
            'completed' => ['Selesai', 'bg-slate-200 text-slate-700'],
            'cancelled' => ['Dibatalkan', 'bg-rose-100 text-rose-700'],
            'no_show' => ['Tidak hadir', 'bg-rose-100 text-rose-700'],
        ];
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
            font-size: 1.7rem;
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

        .sk-btn-flat {
            box-shadow: none;
        }

        .sk-btn-flat:hover {
            box-shadow: none;
        }

        .sk-btn-danger {
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #ffffff;
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
            padding: 0.6rem 0.85rem;
            width: 100%;
            background: #ffffff;
            color: var(--ink);
        }

        .sk-input:focus {
            outline: 2px solid rgba(15, 118, 110, 0.25);
            border-color: rgba(15, 118, 110, 0.4);
        }

        .sk-hero-glow {
            background:
                radial-gradient(circle at 12% 12%, rgba(16, 185, 129, 0.18), transparent 55%),
                radial-gradient(circle at 85% 20%, rgba(249, 115, 22, 0.18), transparent 55%),
                linear-gradient(120deg, #d9efe2 0%, #f2e6d6 55%, #f6d9c9 100%);
        }

        .sk-notif-card {
            height: 430px;
            display: flex;
            flex-direction: column;
        }

        .sk-notif-list {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 1.25rem;
            padding-right: 4px;
        }

        .sk-notif-item {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px 16px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 92px;
        }

        .sk-notif-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sk-action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #065f46;
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            transition: background-color 0.2s ease;
            white-space: nowrap;
        }

        .sk-action-link:hover {
            background: #bbf7d0;
        }
    </style>

    <div class="sk-page min-h-screen sk-hero-glow">
        <div class="relative overflow-x-hidden">
            <nav class="max-w-7xl mx-auto px-6 pt-6 pb-4 grid grid-cols-[1fr_auto_1fr] items-center">
                <div></div>
                <a href="{{ route('studios.index') }}" class="flex items-center gap-3 justify-center">
                    <span class="sk-logo">
                        <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                    </span>
                </a>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('studios.index') }}" class="sk-btn sk-btn-flat">Kembali ke Beranda</a>
                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Apakah anda yakin untuk logout?')">
                        @csrf
                        <button type="submit" class="sk-btn sk-btn-flat sk-btn-danger">Logout</button>
                    </form>
                </div>
            </nav>
        </div>

        <section class="max-w-7xl mx-auto px-6 pb-16 pt-6 space-y-6">
            @if ($errors->any())
                <div class="sk-card p-5 border border-rose-100 bg-rose-50 text-rose-700">
                    <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                    <ul class="list-disc ml-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="sk-card p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-semibold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="sk-title text-2xl font-semibold">Halo, {{ $user->name }}</div>
                            <div class="text-sm text-[var(--muted)]">{{ $user->email }}</div>
                            <div class="text-sm text-[var(--muted)]">{{ $user->no_telp ?? 'Nomor telepon belum diisi.' }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto">
                        <div class="bg-white rounded-2xl px-4 py-3 border border-[var(--line)]">
                            <div class="text-xs text-[var(--muted)]">Total booking</div>
                            <div class="sk-title text-xl font-semibold">{{ $stats['total'] }}</div>
                        </div>
                        <div class="bg-white rounded-2xl px-4 py-3 border border-[var(--line)]">
                            <div class="text-xs text-[var(--muted)]">Booking aktif</div>
                            <div class="sk-title text-xl font-semibold">{{ $stats['upcoming'] }}</div>
                        </div>
                        <div class="bg-white rounded-2xl px-4 py-3 border border-[var(--line)]">
                            <div class="text-xs text-[var(--muted)]">Selesai</div>
                            <div class="sk-title text-xl font-semibold">{{ $stats['completed'] }}</div>
                        </div>
                        <div class="bg-white rounded-2xl px-4 py-3 border border-[var(--line)]">
                            <div class="text-xs text-[var(--muted)]">Total bayar</div>
                            <div class="sk-title text-xl font-semibold">Rp {{ number_format($stats['spent'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6">
                    <div class="sk-card p-6 lg:p-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-[0.2em] text-[var(--muted)]">Riwayat</div>
                                <div class="sk-title text-xl font-semibold mt-2">Riwayat pesanan</div>
                            </div>
                        </div>
                        <div class="mt-5 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs uppercase tracking-widest text-[var(--muted)] border-b border-[var(--line)]">
                                        <th class="py-3">Studio</th>
                                        <th class="py-3">Tanggal</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3">Aksi</th>
                                        <th class="py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($historyBookings as $booking)
                                        @php
                                            $status = $statusLabels[$booking->status] ?? ['Status', 'bg-slate-100 text-slate-600'];
                                        @endphp
                                        <tr class="border-b border-[var(--line)]">
                                            <td class="py-3">
                                                <div class="font-semibold text-[var(--ink)]">{{ $booking->tenant?->nama ?? 'Studio' }}</div>
                                                <div class="text-xs text-[var(--muted)]">
                                                    {{ $booking->service?->nama_service ?? 'Layanan' }}
                                                </div>
                                            </td>
                                            <td class="py-3 text-[var(--muted)]">
                                                {{ \Illuminate\Support\Carbon::parse($booking->tanggal_booking)->translatedFormat('d M Y, H:i') }}
                                            </td>
                                            <td class="py-3">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $status[1] }}">
                                                    {{ $status[0] }}
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                @if (!empty($booking->can_do_payment_action))
                                                    @if (!empty($booking->payment_action_is_post))
                                                        <form method="POST" action="{{ $booking->checkout_url }}">
                                                            @csrf
                                                            <button type="submit" class="sk-action-link">
                                                                {{ $booking->payment_action_label }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ $booking->checkout_url }}" class="sk-action-link">
                                                            {{ $booking->payment_action_label }}
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3 text-right font-semibold text-[var(--primary-dark)]">
                                                Rp {{ number_format($booking->total_harga ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-[var(--muted)]">
                                                Belum ada riwayat booking.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $historyBookings->links() }}
                        </div>
                    </div>

                    <div class="sk-card p-6 lg:p-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-[0.2em] text-[var(--muted)]">Booking aktif</div>
                                <div class="sk-title text-xl font-semibold mt-2">Sedang berjalan / upcoming</div>
                            </div>
                            <span class="text-sm font-semibold text-[var(--primary-dark)]">{{ $upcomingBookings->count() }} booking</span>
                        </div>

                        <div class="mt-5 grid sm:grid-cols-2 gap-4">
                            @forelse ($upcomingBookings as $booking)
                                @php
                                    $status = $statusLabels[$booking->status] ?? ['Status', 'bg-slate-100 text-slate-600'];
                                    $jadwalDate = $booking->jadwal?->tanggal
                                        ? \Illuminate\Support\Carbon::parse($booking->jadwal->tanggal)->translatedFormat('d M Y')
                                        : '-';
                                    $timeRange = ($booking->jadwal?->waktu_mulai && $booking->jadwal?->waktu_selesai)
                                        ? substr((string) $booking->jadwal->waktu_mulai, 0, 5).' - '.substr((string) $booking->jadwal->waktu_selesai, 0, 5)
                                        : '-';
                                @endphp
                                <div class="rounded-2xl border border-[var(--line)] bg-white p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-[var(--ink)]">{{ $booking->tenant?->nama ?? 'Studio' }}</div>
                                            <div class="text-xs text-[var(--muted)] mt-1">{{ $booking->service?->nama_service ?? 'Layanan' }}</div>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $status[1] }}">
                                            {{ $status[0] }}
                                        </span>
                                    </div>
                                    <div class="mt-3 text-sm text-[var(--muted)]">
                                        {{ $jadwalDate }} | {{ $timeRange }}
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-2">
                                        <div class="text-sm font-semibold text-[var(--primary-dark)]">
                                            Rp {{ number_format($booking->total_harga ?? 0, 0, ',', '.') }}
                                        </div>
                                        @if (!empty($booking->can_do_payment_action))
                                            @if (!empty($booking->payment_action_is_post))
                                                <form method="POST" action="{{ $booking->checkout_url }}">
                                                    @csrf
                                                    <button type="submit" class="sk-action-link">
                                                        {{ $booking->payment_action_label }}
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ $booking->checkout_url }}" class="sk-action-link">
                                                    {{ $booking->payment_action_label }}
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="sm:col-span-2 text-sm text-[var(--muted)]">
                                    Belum ada booking aktif saat ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="sk-card p-6 lg:p-8 sk-notif-card">
                        <div class="text-xs uppercase tracking-[0.2em] text-[var(--muted)]">Notifikasi</div>
                        <div class="sk-title text-xl font-semibold mt-2">Aktivitas terbaru</div>
                        <div class="sk-notif-list">
                            @forelse ($latestBookings as $booking)
                                @php
                                    $status = $statusLabels[$booking->status] ?? ['Status', 'bg-slate-100 text-slate-600'];
                                @endphp
                                <div class="sk-notif-item">
                                    <div class="sk-notif-meta">
                                        <div class="text-sm font-semibold">{{ $booking->tenant?->nama ?? 'Studio' }}</div>
                                        <div class="text-xs text-[var(--muted)]">
                                            {{ \Illuminate\Support\Carbon::parse($booking->tanggal_booking)->translatedFormat('d M Y, H:i') }}
                                        </div>
                                    </div>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $status[1] }}">
                                        {{ $status[0] }}
                                    </span>
                                    @if (!empty($booking->can_do_payment_action))
                                        <div>
                                            @if (!empty($booking->payment_action_is_post))
                                                <form method="POST" action="{{ $booking->checkout_url }}">
                                                    @csrf
                                                    <button type="submit" class="sk-action-link">
                                                        {{ $booking->payment_action_label }}
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ $booking->checkout_url }}" class="sk-action-link">
                                                    {{ $booking->payment_action_label }}
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-sm text-[var(--muted)]">Belum ada notifikasi.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="sk-card p-6 lg:p-8">
                        <div class="text-xs uppercase tracking-[0.2em] text-[var(--muted)]">Profil</div>
                        <div class="sk-title text-xl font-semibold mt-2">Edit profil</div>

                        <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="text-sm font-semibold text-[var(--muted)]">Nama</label>
                                <input name="name" class="sk-input mt-2" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-[var(--muted)]">Email</label>
                                <input name="email" type="email" class="sk-input mt-2" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-[var(--muted)]">No. Telp</label>
                                <input name="no_telp" class="sk-input mt-2" value="{{ old('no_telp', $user->no_telp) }}" placeholder="Masukkan nomor telepon">
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="submit" class="sk-btn sk-btn-flat">Simpan profil</button>
                                @if (session('status') === 'profile-updated')
                                    <span class="text-xs text-emerald-600">Profil berhasil diperbarui.</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-[var(--line)] bg-white">
            <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="text-sm text-[var(--muted)]">
                        <span class="sk-logo">
                            <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita</span>
                        </span>
                        - Platform booking studio musik.
                    </div>
                </div>
                <div class="text-sm text-[var(--muted)]">Powered by komunitas studio lokal.</div>
            </div>
        </footer>
    </div>
</x-app-layout>
