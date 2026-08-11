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

        .sk-card {
            background: var(--card);
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
        }

        .sk-hero-glow {
            background:
                radial-gradient(circle at 15% 10%, rgba(16, 185, 129, 0.15), transparent 55%),
                radial-gradient(circle at 80% 20%, rgba(249, 115, 22, 0.18), transparent 55%),
                radial-gradient(circle at 40% 80%, rgba(14, 116, 144, 0.14), transparent 50%);
        }

        .sk-how-card {
            padding: 3rem 2.5rem;
        }

        .sk-how-steps {
            position: relative;
            margin-top: 2.5rem;
        }

        .sk-how-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 220px;
            padding: 0.75rem 1.6rem;
            margin-top: 2.5rem;
        }

        .sk-how-path {
            position: absolute;
            left: 10%;
            right: 10%;
            top: -32px;
            width: 80%;
            height: 110px;
            z-index: 0;
        }

        .sk-how-item {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .sk-how-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            margin: 0 auto 1rem;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f2937;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        }

        .sk-how-title {
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .sk-how-text {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 0.5rem;
        }

        .sk-logo-small {
            font-size: 0.9rem;
        }

        .sk-logo-hero {
            font-size: clamp(3rem, 8vw, 12rem);
            line-height: 1;
        }

        .sk-animate-in {
            animation: skRise 0.7s ease both;
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
        <div class="relative sk-hero-glow overflow-x-hidden min-h-[calc(100vh-96px)]">
            <nav class="max-w-7xl mx-auto px-6 pt-6 pb-4 flex items-center justify-between">
                <a href="{{ route('studios.index') }}" class="flex items-center gap-3">
                    <span class="sk-logo">
                        <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                    </span>
                </a>
                <div class="hidden lg:flex items-center gap-8 text-sm text-[var(--muted)]">
                    <a href="{{ route('studios.index') }}" class="hover:text-[var(--primary-dark)]">Beranda</a>
                    <a href="{{ route('studios.catalog') }}" class="hover:text-[var(--primary-dark)]">Studio</a>
                    <a href="{{ route('studios.how') }}" class="text-[var(--primary-dark)] font-semibold">Cara kerja</a>
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

            <section class="max-w-6xl mx-auto px-6 pb-20 pt-10">
                <div class="sk-card sk-how-card sk-animate-in">
                    <div class="text-center">
                        <div class="text-sm text-black" style="font-size: 1.1em;">How</div>
                        <div class="sk-logo sk-logo-hero font-semibold mt-1">
                            <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                        </div>
                        <div class="text-sm text-black mt-1" style="font-size: 1.1em;">Works</div>
                        <a href="{{ route('studios.catalog') }}" class="sk-btn sk-how-cta">
                            Jelajahi studio
                        </a>
                        <p class="mt-6 text-sm text-[var(--muted)]">
                            Temukan studio, cek fasilitas, dan booking jadwal latihan atau rekaman secara instan.
                        </p>
                    </div>

                    <div class="sk-how-steps">
                        <svg class="sk-how-path" viewBox="0 0 1000 140" preserveAspectRatio="none">
                            <path d="M40 80 C 260 10, 420 130, 600 80 S 840 130, 960 60"
                                  fill="none" stroke="rgba(15,23,42,0.18)" stroke-width="3"
                                  stroke-dasharray="6 10" />
                        </svg>

                        <div class="grid gap-8 md:grid-cols-3">
                            <div class="sk-how-item">
                                <div class="sk-how-icon">
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <path d="M7 9h10"></path>
                                        <path d="M7 13h6"></path>
                                    </svg>
                                </div>
                                <div class="sk-how-title">Pilih Studio</div>
                                <div class="sk-how-text">
                                    Jelajahi studio berdasarkan kota, fasilitas, dan layanan yang kamu butuhkan.
                                </div>
                            </div>
                            <div class="sk-how-item">
                                <div class="sk-how-icon">
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                        <path d="M4 19v-6"></path>
                                        <path d="M10 19V9"></path>
                                        <path d="M16 19V5"></path>
                                        <path d="M22 19V12"></path>
                                    </svg>
                                </div>
                                <div class="sk-how-title">Cek Detail</div>
                                <div class="sk-how-text">
                                    Lihat foto, layanan, harga, dan jadwal yang tersedia di studio pilihanmu.
                                </div>
                            </div>
                            <div class="sk-how-item">
                                <div class="sk-how-icon">
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                        <rect x="4" y="4" width="16" height="16" rx="3"></rect>
                                        <path d="M9 12h6"></path>
                                        <path d="M12 9v6"></path>
                                    </svg>
                                </div>
                                <div class="sk-how-title">Booking Aman</div>
                                <div class="sk-how-text">
                                    Pilih jadwal, lakukan booking, dan dapatkan konfirmasi dari studio.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
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
                <div class="text-sm text-[var(--muted)]">Made With ❤️ by StudioKita</div>
            </div>
        </footer>
    </div>
</x-app-layout>
