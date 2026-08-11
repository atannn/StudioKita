<x-app-layout :hide-nav="true">
    @php
        $fullAddress = collect([
            $tenant->alamat ?? null,
            $tenant->kecamatan ?? null,
            $tenant->kota ?? null,
            $tenant->provinsi ?? null,
        ])->filter()->implode(', ');

        $openTime = $tenant->open_time ? substr($tenant->open_time, 0, 5) : null;
        $closeTime = $tenant->close_time ? substr($tenant->close_time, 0, 5) : null;

        $galleryPhotos = collect();

        if ($tenant->rooms) {
            foreach ($tenant->rooms as $room) {
                if (!empty($room->foto_ruangan)) {
                    $galleryPhotos->push([
                        'path' => $room->foto_ruangan,
                        'alt' => 'Foto ruangan '.$room->nama_ruangan,
                    ]);
                }
            }
        }

        $roomFacilityPhotos = $tenant->photos->filter(fn ($p) => !$p->is_primary && $p->status == 1);
        foreach ($roomFacilityPhotos as $photo) {
            $galleryPhotos->push([
                'path' => $photo->foto_path,
                'alt' => 'Foto ruangan dan fasilitas',
            ]);
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
            --accent: #f97316;
            --card: #ffffff;
            --soft: #fff7ed;
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
            font-size: 1.75rem;
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

        .sk-hero-glow {
            background:
                radial-gradient(circle at 15% 10%, rgba(16, 185, 129, 0.15), transparent 55%),
                radial-gradient(circle at 80% 20%, rgba(249, 115, 22, 0.18), transparent 55%),
                radial-gradient(circle at 40% 80%, rgba(14, 116, 144, 0.14), transparent 50%);
        }

        .sk-card {
            background: var(--card);
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
        }

        .sk-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .sk-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary-dark);
            background: rgba(15, 118, 110, 0.12);
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
        }

        .sk-photo {
            background: rgba(15, 23, 42, 0.04);
            border-radius: 20px;
            overflow: hidden;
        }

        .sk-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sk-table th {
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            background: #f8fafc;
            padding: 0.75rem 0.9rem;
        }

        .sk-table td {
            padding: 0.8rem 0.9rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            color: var(--ink);
            vertical-align: top;
        }

        .sk-table tr:last-child td {
            border-bottom: none;
        }

        .sk-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.2);
        }

        .sk-dot.is-active {
            background: var(--primary);
        }
    </style>

    <div class="sk-page min-h-screen">
        <div class="sk-hero-glow">
            <nav class="max-w-7xl mx-auto px-6 pt-6 pb-4 flex items-center justify-between">
                <a href="{{ route('studios.index') }}" class="flex items-center gap-3">
                    <span class="sk-logo">
                        <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                    </span>
                </a>
                <div class="hidden lg:flex items-center gap-8 text-sm text-[var(--muted)]">
                    <a href="{{ route('studios.index') }}" class="hover:text-[var(--primary-dark)]">Beranda</a>
                    <a href="{{ route('studios.catalog') }}" class="text-[var(--primary-dark)] font-semibold">Studio</a>
                    <a href="{{ route('studios.how') }}" class="hover:text-[var(--primary-dark)]">Cara kerja</a>
                    @if (!auth()->check() || auth()->user()->role !== 'customer')
                        <a href="{{ route('studios.join') }}" class="hover:text-[var(--primary-dark)]">Gabung</a>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        @if (auth()->user()->role === 'customer')
                            <a href="{{ route('customer.profile') }}" class="sk-btn">Profil Saya</a>
                        @elseif (auth()->user()->role === 'owner')
                            <a href="{{ route('owner.dashboard') }}" class="sk-btn">Dashboard Owner</a>
                        @elseif (auth()->user()->role === 'developer')
                            <a href="{{ route('developer.dashboard') }}" class="sk-btn">Dashboard Developer</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="sk-btn">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-[var(--muted)] hover:text-[var(--primary-dark)]">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="sk-btn">Register</a>
                        @endif
                    @endauth
                </div>
            </nav>

            <section class="max-w-7xl mx-auto px-6 pb-10 pt-4">
                <div class="sk-card p-6 lg:p-8">
                    <div class="flex flex-col lg:flex-row gap-8 items-start">
                        <div class="w-full lg:w-[320px]">
                            @if($tenant->primaryPhoto)
                                <div class="sk-photo aspect-square">
                                    <img src="{{ asset('storage/'.$tenant->primaryPhoto->foto_path) }}"
                                         class="w-full h-full object-contain"
                                         alt="logo studio">
                                </div>
                            @else
                                <div class="sk-photo aspect-square flex items-center justify-center text-sm text-[var(--muted)]">
                                    Belum ada logo
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 space-y-4">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                <div>
                                    <div class="sk-label">Profil studio</div>
                                    <h1 class="sk-title text-3xl sm:text-4xl font-semibold mt-2">{{ $tenant->nama }}</h1>
                                    <div class="text-sm text-[var(--muted)] mt-2">
                                        {{ $fullAddress !== '' ? $fullAddress : '-' }}
                                    </div>
                                    <div class="text-sm text-[var(--muted)] mt-2">
                                        Jam operasional:
                                        <span class="text-[var(--primary-dark)] font-semibold">
                                            {{ $openTime && $closeTime ? $openTime.' - '.$closeTime : '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <a href="{{ route('studios.booking.create', $tenant->slug) }}" class="sk-btn">
                                        Booking Studio
                                    </a>
                                </div>
                            </div>
                            <p class="text-sm leading-relaxed text-[var(--muted)]">
                                {{ $tenant->deskripsi ?? 'Deskripsi studio belum tersedia.' }}
                            </p>
                            <div class="flex justify-end">
                                @if (($tenant->verification_level ?? 'none') === 'verified')
                                    <span class="sk-badge">Studio terverifikasi</span>
                                @elseif (($tenant->verification_level ?? 'none') === 'basic_verified')
                                    <span class="sk-badge">Basic verified</span>
                                @else
                                    <span class="sk-badge">Studio aktif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section id="facilities" class="max-w-7xl mx-auto px-6 pb-10 pt-8">
            <div class="sk-card p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-2 mb-4">
                    <div>
                        <div class="sk-label">Foto studio</div>
                        <h2 class="sk-title text-2xl font-semibold mt-1">Ruangan dan fasilitas.</h2>
                    </div>
                    <span class="sk-badge">Galeri studio</span>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.1fr_1.4fr] items-start">
                    <div>
                        @if($galleryPhotos->isEmpty())
                            <div class="sk-photo aspect-[4/3] flex items-center justify-center text-sm text-[var(--muted)]">
                                Belum ada foto ruangan dan fasilitas.
                            </div>
                        @else
                            <div class="relative" x-data="{ index: 0, total: {{ $galleryPhotos->count() }} }">
                                <div class="sk-photo aspect-[4/3] overflow-hidden">
                                    <div class="flex transition-transform duration-500 h-full"
                                         :style="`transform: translateX(-${index * 100}%);`">
                                        @foreach($galleryPhotos as $p)
                                            <div class="min-w-full h-full">
                                                <img src="{{ asset('storage/'.$p['path']) }}"
                                                     class="w-full h-full object-cover"
                                                     alt="{{ $p['alt'] }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <button type="button"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 shadow flex items-center justify-center"
                                        @click="index = (index - 1 + total) % total"
                                        aria-label="Sebelumnya">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 18l-6-6 6-6"></path>
                                    </svg>
                                </button>
                                <button type="button"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 shadow flex items-center justify-center"
                                        @click="index = (index + 1) % total"
                                        aria-label="Berikutnya">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 18l6-6-6-6"></path>
                                    </svg>
                                </button>

                                <div class="mt-3 flex items-center justify-center gap-2">
                                    @for ($i = 0; $i < $galleryPhotos->count(); $i++)
                                        <button type="button"
                                                class="sk-dot"
                                                :class="index === {{ $i }} ? 'is-active' : ''"
                                                @click="index = {{ $i }}"
                                                aria-label="Foto {{ $i + 1 }}"></button>
                                    @endfor
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="lg:pt-1" id="facilitiesContent">
                        @include('studios.partials.facilities', ['facilities' => $facilities])
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-6 pb-10">
            <div class="sk-card p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-6">
                    <div>
                        <div class="sk-label">Layanan studio</div>
                        <h2 class="sk-title text-2xl font-semibold mt-2">Paket layanan.</h2>
                    </div>
                    <span class="sk-badge">Latihan & rekaman</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="sk-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Durasi</th>
                                <th>Harga</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenant->services as $s)
                                @php
                                    $weekdayPrice = $s->weekday_price;
                                    $weekendPrice = $s->weekend_price;
                                @endphp
                                <tr>
                                    <td>{{ $s->nama_service }}</td>
                                    <td class="text-[var(--muted)]">{{ $s->tipe_service }}</td>
                                    <td class="text-[var(--muted)]">{{ $s->durasi_menit }} menit</td>
                                    <td class="text-[var(--primary-dark)] font-semibold">
                                        <div>Weekdays: Rp {{ number_format($weekdayPrice,0,',','.') }}</div>
                                        <div>Weekend: Rp {{ number_format($weekendPrice,0,',','.') }}</div>
                                    </td>
                                    <td class="text-[var(--muted)]">{{ $s->deskripsi ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-[var(--muted)]">Belum ada layanan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-6 pb-16">
            <div class="sk-card p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-6">
                    <div>
                        <div class="sk-label">Ruangan tersedia</div>
                        <h2 class="sk-title text-2xl font-semibold mt-2">Daftar ruangan.</h2>
                    </div>
                    <span class="sk-badge">Latihan & rekaman</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="sk-table">
                        <thead>
                            <tr>
                                <th>Nama Ruangan</th>
                                <th>Tipe</th>
                                <th>Kapasitas</th>
                                <th>Fasilitas / Alat</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenant->rooms as $r)
                                <tr>
                                    <td>{{ $r->nama_ruangan }}</td>
                                    <td class="text-[var(--muted)]">{{ $r->tipe_ruangan }}</td>
                                    <td class="text-[var(--muted)]">{{ $r->kapasitas ?? '-' }}</td>
                                    <td class="text-[var(--muted)]">
                                        @if ($r->facilities->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($r->facilities as $facility)
                                                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-[var(--primary-dark)]">
                                                        {{ $facility->nama_fasilitas }}
                                                        @if (($facility->quantity ?? 1) > 1)
                                                            x{{ $facility->quantity }}
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-[var(--muted)]">{{ $r->deskripsi ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-[var(--muted)]">Belum ada ruangan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('facilitiesContent');
            if (!container) return;

            container.addEventListener('click', async (event) => {
                const link = event.target.closest('a');
                if (!link || !link.href || !link.href.includes('facilities_page')) return;
                event.preventDefault();

                const response = await fetch(link.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) return;
                const payload = await response.json();
                if (payload.html) {
                    container.innerHTML = payload.html;
                }
            });
        });
    </script>
</x-app-layout>
