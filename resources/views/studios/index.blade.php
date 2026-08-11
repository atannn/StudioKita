<x-app-layout :hide-nav="true">
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

        .sk-btn-outline {
            border: 1px solid rgba(15, 118, 110, 0.35);
            color: var(--primary-dark);
            padding: 0.7rem 1.4rem;
            border-radius: 999px;
            font-weight: 600;
            background: #ffffff;
            transition: border 0.2s ease, transform 0.2s ease;
        }

        .sk-btn-outline:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
        }

        .sk-pill {
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary-dark);
            border-radius: 999px;
            padding: 0.2rem 0.7rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .sk-chip {
            background: #ffffff;
            color: var(--muted);
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            font-size: 0.8rem;
            border: 1px solid var(--line);
        }

        .sk-input-wrap {
            border: 1px solid rgba(15, 23, 42, 0.15);
            border-radius: 16px;
            padding: 0.65rem 0.9rem;
            background: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            width: 100%;
        }

        .sk-input-wrap:focus-within {
            outline: 2px solid rgba(15, 118, 110, 0.25);
            border-color: rgba(15, 118, 110, 0.4);
        }

        .sk-input-field {
            flex: 1;
            outline: none;
            background: transparent;
            font-size: 0.95rem;
            min-width: 0;
            border: none;
            padding: 0;
            box-shadow: none;
            appearance: none;
        }

        .sk-input-field:focus {
            outline: none;
            box-shadow: none;
        }

        .sk-input-icon {
            color: var(--muted);
        }

        .sk-input-button {
            border: none;
            background: transparent;
            color: var(--muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.2rem;
            cursor: pointer;
        }

        .sk-dropdown {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 0.5rem);
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
            max-height: 18rem;
            overflow-y: auto;
            z-index: 40;
        }

        .sk-dropdown-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            color: var(--ink);
            transition: background 0.2s ease;
            width: 100%;
            text-align: left;
            background: transparent;
            border-left: none;
            border-right: none;
            border-top: none;
            cursor: pointer;
        }

        .sk-dropdown-item:last-child {
            border-bottom: none;
        }

        .sk-dropdown-item:hover {
            background: rgba(15, 118, 110, 0.08);
        }

        .sk-item-main {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .sk-item-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.2rem;
            min-width: 72px;
        }

        .sk-tag {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary-dark);
        }

        .sk-chip-link {
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
            font-size: 0.85rem;
            border: 1px solid var(--line);
            color: var(--muted);
            background: #ffffff;
            transition: transform 0.2s ease, border-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .sk-chip-link:hover {
            transform: translateY(-1px);
            border-color: rgba(15, 118, 110, 0.4);
        }

        .sk-chip-link.is-active {
            background: var(--primary);
            color: #ffffff;
            border-color: transparent;
        }

        .sk-card {
            background: var(--card);
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
        }

        .sk-input {
            border: 1px solid rgba(15, 23, 42, 0.15);
            border-radius: 16px;
            padding: 0.75rem 1rem;
            background: #ffffff;
            width: 100%;
        }

        .sk-input:focus {
            outline: 2px solid rgba(15, 118, 110, 0.25);
            border-color: rgba(15, 118, 110, 0.4);
        }

        .sk-soft {
            background: var(--soft);
            border-radius: 18px;
            border: 1px dashed rgba(249, 115, 22, 0.3);
        }

        .sk-hero-glow {
            background:
                radial-gradient(circle at 15% 10%, rgba(16, 185, 129, 0.15), transparent 55%),
                radial-gradient(circle at 80% 20%, rgba(249, 115, 22, 0.18), transparent 55%),
                radial-gradient(circle at 40% 80%, rgba(14, 116, 144, 0.14), transparent 50%);
        }

        .sk-grid {
            background-image: linear-gradient(transparent 90%, rgba(15, 23, 42, 0.04)),
                linear-gradient(90deg, transparent 90%, rgba(15, 23, 42, 0.04));
            background-size: 36px 36px;
        }

        .sk-animate-in {
            animation: skRise 0.7s ease both;
        }

        [x-cloak] {
            display: none !important;
        }

        @keyframes skRise {
            from {
                opacity: 0;
                transform: translateY(22px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="sk-page min-h-screen">
        <div class="relative sk-hero-glow overflow-x-hidden">
            <nav class="max-w-7xl mx-auto px-6 pt-6 pb-4 flex items-center justify-between">
                <a href="{{ route('studios.index') }}" class="flex items-center gap-3">
                    <span class="sk-logo">
                        <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                    </span>
                </a>
                <div class="hidden lg:flex items-center gap-8 text-sm text-[var(--muted)]">
                    <a href="{{ route('studios.index') }}" class="text-[var(--primary-dark)] font-semibold">Beranda</a>
                    <a href="{{ route('studios.catalog') }}" class="hover:text-[var(--primary-dark)]">Studio</a>
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

            <section class="max-w-7xl mx-auto px-6 pb-16 pt-6 grid lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-6 sk-animate-in">
                    <span class="sk-chip">Booking studio musik tanpa ribet</span>
                    <h1 class="sk-title text-4xl sm:text-5xl lg:text-6xl mt-4 leading-tight">
                        Temukan studio latihan dan rekaman yang cocok dengan gaya kamu.
                    </h1>
                    <p class="mt-4 text-lg text-[var(--muted)]">
                        Jelajahi studio pilihan dari owner terpercaya, cek fasilitas, dan pesan jadwal
                        langsung dari satu platform.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#studios" class="sk-btn">Jelajahi studio</a>
                        <a href="{{ route('studios.join') }}" class="sk-btn-outline">Daftarkan studio</a>
                    </div>
                    <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="bg-white rounded-2xl px-4 py-3 shadow-sm">
                            <div class="sk-title text-2xl font-semibold">{{ $totalStudios }}</div>
                            <div class="text-sm text-[var(--muted)]">Studio aktif</div>
                        </div>
                        <div class="bg-white rounded-2xl px-4 py-3 shadow-sm">
                            <div class="sk-title text-2xl font-semibold">2 menit</div>
                            <div class="text-sm text-[var(--muted)]">Rata-rata booking</div>
                        </div>
                        <div class="bg-white rounded-2xl px-4 py-3 shadow-sm">
                            <div class="sk-title text-2xl font-semibold">100%</div>
                            <div class="text-sm text-[var(--muted)]">Konfirmasi digital</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    <div class="sk-card p-6 lg:p-8 sk-animate-in" style="animation-delay: 0.1s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="sk-title text-xl font-semibold">Cari studio impianmu</div>
                                <div class="text-sm text-[var(--muted)]">Masukkan nama studio atau kata kunci.</div>
                            </div>
                            <span class="sk-pill">Live</span>
                        </div>
                        <form method="GET" action="{{ route('studios.catalog') }}" class="mt-5 grid gap-4">
                            <div>
                                <label for="q" class="text-sm font-semibold text-[var(--muted)]">Nama studio</label>
                                <input id="q" type="text" name="q" value="{{ $q }}" placeholder="contoh: Wave Studios"
                                       class="sk-input mt-2" />
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-[var(--muted)]">Kota pilihan</label>
                                <div class="mt-2 relative" x-data="{ open: false, city: @js($kota ?? '') }"
                                     @keydown.escape.window="open = false"
                                     @click.outside="open = false">
                                    <div class="sk-input-wrap">
                                        <svg class="sk-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 22s7-7.2 7-12a7 7 0 1 0-14 0c0 4.8 7 12 7 12z"></path>
                                            <circle cx="12" cy="10" r="2.5"></circle>
                                        </svg>
                                        <input type="text"
                                               name="kota"
                                               class="sk-input-field"
                                               placeholder="Cari kota tujuan"
                                               value="{{ $kota }}"
                                               x-model="city"
                                               autocomplete="off"
                                               @focus="open = true"
                                               @input="open = true">
                                        <button type="button" class="sk-input-button" aria-label="Tampilkan pilihan kota"
                                                @click="open = !open">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M6 9l6 6 6-6"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="sk-dropdown" x-show="open" x-transition x-cloak>
                                        <div class="px-4 pt-3 pb-2 text-xs font-semibold text-[var(--muted)] uppercase tracking-widest">
                                            Popular Destination
                                        </div>
                                        <button type="button" class="sk-dropdown-item"
                                                @click="city = ''; open = false; window.updateStudioResults('')">
                                            <div class="sk-item-main">
                                                <div class="font-semibold">Semua kota</div>
                                                <div class="text-xs text-[var(--muted)]">Lihat semua studio</div>
                                            </div>
                                            <div class="sk-item-meta">
                                                <span class="sk-tag">City</span>
                                            </div>
                                        </button>
                                        @forelse($popularCities as $city)
                                            <button type="button" class="sk-dropdown-item"
                                                    @click="city = '{{ $city->kota }}'; open = false; window.updateStudioResults('{{ $city->kota }}')">
                                                <div class="sk-item-main">
                                                    <div class="font-semibold">{{ $city->kota }}</div>
                                                    <div class="text-xs text-[var(--muted)]">
                                                        {{ $city->provinsi ?? 'Indonesia' }}
                                                    </div>
                                                </div>
                                                <div class="sk-item-meta">
                                                    <div class="sk-tag">City</div>
                                                    <div class="text-xs text-[var(--muted)] mt-1">{{ $city->total }} studio</div>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="px-4 pb-4 text-sm text-[var(--muted)]">Belum ada data kota.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <button class="sk-btn w-full" type="submit">Cari studio</button>
                        </form>
                        <div class="mt-6 grid sm:grid-cols-2 gap-3">
                            <div class="sk-soft px-4 py-3">
                                <div class="text-sm font-semibold text-[var(--primary-dark)]">Kurasi fasilitas</div>
                                <div class="text-sm text-[var(--muted)] mt-1">
                                    Bandingkan fasilitas dan layanan dalam satu tampilan.
                                </div>
                            </div>
                            <div class="sk-soft px-4 py-3">
                                <div class="text-sm font-semibold text-[var(--primary-dark)]">Jadwal fleksibel</div>
                                <div class="text-sm text-[var(--muted)] mt-1">
                                    Pilih waktu latihan atau rekaman yang sesuai jadwalmu.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </div>

        <section id="studios" class="max-w-7xl mx-auto px-6 pb-16 pt-10">
            <div class="flex flex-col lg:flex-row items-start lg:items-end justify-between gap-6">
                <div class="space-y-1">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--muted)]">Pilih studio</div>
                    <h2 class="sk-title text-3xl sm:text-4xl font-semibold leading-tight">Studio pilihan untuk kamu.</h2>
                </div>
                <div id="studioFilters" class="flex flex-col gap-2 lg:text-right">
                    @include('studios.partials.filters', ['q' => $q, 'kota' => $kota])
                </div>
            </div>

            <div id="studioGrid">
                @include('studios.partials.grid', ['studios' => $studios])
            </div>

            <div id="studioPagination" class="mt-8">
                {{ $studios->links() }}
            </div>
        </section>

        <section id="gabung" class="max-w-7xl mx-auto px-6 pb-16">
            <div class="sk-card p-8 md:p-10 sk-grid">
                <div class="grid md:grid-cols-[1.4fr_1fr] gap-8 items-center">
                    <div>
                        <div class="text-sm text-[var(--muted)]">Untuk owner studio</div>
                        <h2 class="sk-title text-3xl font-semibold mt-2">
                            Kelola studio, layanan, dan jadwal dalam satu dashboard.
                        </h2>
                        <p class="text-[var(--muted)] mt-3">
                            StudioKita membantu kamu mengelola fasilitas, foto, layanan, hingga jadwal booking
                            secara rapi dan terstruktur.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('studios.join') }}" class="sk-btn">Mulai sebagai owner</a>
                            <a href="{{ route('login') }}" class="sk-btn-outline">Masuk dashboard</a>
                        </div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm">
                        <div class="sk-title text-lg font-semibold">Highlight sistem</div>
                        <ul class="mt-4 space-y-3 text-sm text-[var(--muted)]">
                            <li>Manajemen layanan latihan & rekaman.</li>
                            <li>Upload foto studio, ruangan, dan fasilitas.</li>
                            <li>Kelola jadwal otomatis dan notifikasi booking.</li>
                            <li>Dashboard owner yang fokus dan rapi.</li>
                        </ul>
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
                <div class="text-sm text-[var(--muted)]">Made With ❤️ by StudioKita.</div>
            </div>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const grid = document.getElementById('studioGrid');
            const pagination = document.getElementById('studioPagination');
            const filters = document.getElementById('studioFilters');
            const queryInput = document.getElementById('q');
            const baseUrl = @json(route('studios.index'));

            window.updateStudioResults = async (city) => {
                const params = new URLSearchParams();
                const queryValue = queryInput?.value?.trim();

                if (queryValue) {
                    params.set('q', queryValue);
                }
                if (city) {
                    params.set('kota', city);
                }

                const url = params.toString() ? `${baseUrl}?${params}` : baseUrl;
                history.replaceState({}, '', url);

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                if (filters && payload.filters) {
                    filters.innerHTML = payload.filters;
                }
                if (grid && payload.grid) {
                    grid.innerHTML = payload.grid;
                }
                if (pagination && payload.pagination) {
                    pagination.innerHTML = payload.pagination;
                }
            };
        });
    </script>
</x-app-layout>
