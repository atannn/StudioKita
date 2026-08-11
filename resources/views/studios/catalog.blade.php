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

        .sk-pill {
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary-dark);
            border-radius: 999px;
            padding: 0.2rem 0.7rem;
            font-size: 0.75rem;
            font-weight: 600;
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

        .sk-filter-pill {
            border-radius: 999px;
            padding: 0.4rem 0.95rem;
            font-size: 0.85rem;
            border: 1px solid var(--line);
            color: var(--muted);
            background: #ffffff;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .sk-filter-pill:hover {
            transform: translateY(-1px);
            border-color: rgba(15, 118, 110, 0.4);
        }

        .sk-filter-pill.is-active {
            background: var(--primary);
            color: #ffffff;
            border-color: transparent;
        }

        .sk-select {
            border: 1px solid rgba(15, 23, 42, 0.15);
            border-radius: 999px;
            padding: 0.45rem 1rem;
            background: #ffffff;
            font-size: 0.85rem;
            color: var(--muted);
        }

        .sk-select:focus {
            outline: 2px solid rgba(15, 118, 110, 0.25);
            border-color: rgba(15, 118, 110, 0.4);
        }

        .sk-searchbar {
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 999px;
            padding: 0.4rem 0.75rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }

        .sk-search-input {
            border: none;
            background: transparent;
            padding: 0.45rem 0.6rem;
            font-size: 0.9rem;
            min-width: 220px;
            flex: 1;
            color: var(--ink);
        }

        .sk-search-input:focus {
            outline: none;
        }

        .sk-search-select {
            border: none;
            background: transparent;
            padding: 0.45rem 0.6rem;
            font-size: 0.85rem;
            color: var(--muted);
            min-width: 150px;
        }

        .sk-search-select:focus {
            outline: none;
        }

        .sk-search-button {
            border: none;
            background: var(--primary);
            color: #ffffff;
            padding: 0.45rem 1.1rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 12px 24px rgba(15, 118, 110, 0.2);
        }

        .sk-search-reset {
            border: 1px solid var(--line);
            color: var(--muted);
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            font-size: 0.85rem;
            background: #ffffff;
        }

        .sk-search-divider {
            width: 1px;
            height: 24px;
            background: rgba(15, 23, 42, 0.12);
            display: none;
        }

        @media (min-width: 1024px) {
            .sk-search-divider {
                display: block;
            }
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

    <div class="sk-page min-h-screen flex flex-col">
        <div class="flex-1">
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
                <div class="sk-card p-5 lg:p-6 mb-8">
                    @php
                        $popularCityNames = $popularCities->pluck('kota')->all();
                        $filterBase = array_filter([
                            'q' => $q,
                            'kota' => $kota,
                            'sort' => $sort,
                        ], fn($value) => $value !== null && $value !== '');
                    @endphp
                    <form id="studioFilterForm" method="GET" action="{{ route('studios.catalog') }}" class="sk-searchbar">
                        <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Cari studio..."
                               class="sk-search-input" />
                        <input type="hidden" name="tipe" value="{{ $tipe }}">
                        <span class="sk-search-divider"></span>
                        <select name="kota" class="sk-search-select">
                            <option value="">Semua kota</option>
                            @if($kota && !in_array($kota, $popularCityNames, true))
                                <option value="{{ $kota }}" selected>{{ $kota }}</option>
                            @endif
                            @foreach($popularCities as $city)
                                <option value="{{ $city->kota }}" @selected($kota === $city->kota)>{{ $city->kota }}</option>
                            @endforeach
                        </select>
                        <span class="sk-search-divider"></span>
                        <select name="sort" class="sk-search-select" onchange="document.getElementById('studioFilterForm').submit()">
                            <option value="name" @selected($sort === 'name')>Nama A-Z</option>
                            <option value="name_desc" @selected($sort === 'name_desc')>Nama Z-A</option>
                            <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                        </select>
                        <button class="sk-search-button" type="submit">Filter</button>
                        <a class="sk-search-reset" href="{{ route('studios.catalog') }}">Reset</a>
                    </form>

                    <div class="mt-5">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('studios.catalog', $filterBase) }}"
                               class="sk-filter-pill @if(empty($tipe)) is-active @endif">Semua</a>
                            <a href="{{ route('studios.catalog', array_merge($filterBase, ['tipe' => 'latihan'])) }}"
                               class="sk-filter-pill @if($tipe === 'latihan') is-active @endif">Latihan</a>
                            <a href="{{ route('studios.catalog', array_merge($filterBase, ['tipe' => 'rekaman'])) }}"
                               class="sk-filter-pill @if($tipe === 'rekaman') is-active @endif">Rekaman</a>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section id="studioList" class="max-w-7xl mx-auto px-6 pb-16 pt-4">
            <div class="flex flex-col lg:flex-row items-start lg:items-end justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--muted)]">Studio tersedia</div>
                    <h2 class="sk-title text-3xl sm:text-4xl font-semibold mt-2">Temukan studio favoritmu.</h2>
                </div>
                <div class="text-sm text-[var(--muted)]">
                    @include('studios.partials.filters', ['q' => $q, 'kota' => $kota])
                </div>
            </div>

            @include('studios.partials.catalog-grid', ['studios' => $studios])

            <div class="mt-8">
                {{ $studios->links() }}
            </div>
        </section>
        </div>

        <footer class="border-t border-[var(--line)] bg-white mt-auto">
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
