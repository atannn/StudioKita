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

        .sk-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.2em;
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

        .sk-feature-card {
            border-radius: 22px;
            padding: 1.6rem 1.5rem;
            border: 1px solid rgba(15, 23, 42, 0.06);
            background: #ffffff;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
        }

        .sk-feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }

        .sk-feature-card:nth-child(1) {
            background: #e7f2ff;
            border-color: rgba(30, 64, 175, 0.08);
        }

        .sk-feature-card:nth-child(2) {
            background: #eaf7f1;
            border-color: rgba(16, 185, 129, 0.12);
        }

        .sk-feature-card:nth-child(3) {
            background: #f3e9ff;
            border-color: rgba(124, 58, 237, 0.12);
        }

        .sk-feature-card:nth-child(4) {
            background: #ffeef0;
            border-color: rgba(244, 63, 94, 0.12);
        }

        .sk-feature-card:nth-child(5) {
            background: #e9f6ff;
            border-color: rgba(14, 165, 233, 0.12);
        }

        .sk-feature-card:nth-child(6) {
            background: #fdf4e3;
            border-color: rgba(249, 115, 22, 0.12);
        }

        .sk-soft {
            background: rgba(15, 118, 110, 0.08);
            border-radius: 18px;
            border: 1px dashed rgba(15, 118, 110, 0.25);
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
        <div class="sk-hero-glow">
            <nav class="max-w-7xl mx-auto px-6 pt-6 pb-4 flex items-center justify-between">
                <a href="{{ route('studios.index') }}" class="flex items-center gap-3">
                    <span class="sk-logo">
                        <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                    </span>
                </a>
                <div class="hidden lg:flex items-center gap-8 text-sm text-[var(--muted)]">
                    <a href="{{ route('studios.index') }}" class="hover:text-[var(--primary-dark)]">Beranda</a>
                    <a href="{{ route('studios.catalog') }}" class="hover:text-[var(--primary-dark)]">Studio</a>
                    <a href="{{ route('studios.how') }}" class="hover:text-[var(--primary-dark)]">Cara kerja</a>
                    @if (!auth()->check() || auth()->user()->role !== 'customer')
                        <a href="{{ route('studios.join') }}" class="text-[var(--primary-dark)] font-semibold">Gabung</a>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="sk-btn">Dashboard</a>
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

            <section class="max-w-7xl mx-auto px-6 pb-14 pt-6">
                <div class="sk-card p-8 lg:p-12 sk-animate-in">
                    <div class="grid lg:grid-cols-[1.2fr_0.8fr] gap-10 items-center">
                        <div>
                            <div class="sk-label">Gabung studio</div>
                            <h1 class="sk-title text-3xl sm:text-4xl lg:text-5xl mt-3 leading-tight">
                                Kelola studio kamu dengan dashboard terpadu StudioKita.
                            </h1>
                            <p class="mt-4 text-base text-[var(--muted)]">
                                Semua kebutuhan studio musikmu ada di satu tempat: manajemen layanan, jadwal,
                                fasilitas, foto, hingga booking pelanggan secara otomatis.
                            </p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('register') }}" class="sk-btn">Daftarkan studio</a>
                                <a href="{{ route('studios.catalog') }}" class="sk-btn-outline">Lihat katalog</a>
                            </div>
                        </div>
                        <div class="sk-soft p-6">
                            <div class="text-sm text-[var(--muted)]">Mulai dari sekarang</div>
                            <div class="sk-title text-3xl font-semibold mt-2">Aktifkan booking online</div>
                            <p class="text-sm text-[var(--muted)] mt-3">
                                Terhubung langsung dengan pelanggan. Kurangi chat manual dan pastikan jadwal rapi.
                            </p>
                            <div class="mt-5 flex flex-wrap gap-2">
                                <span class="sk-badge">Manajemen jadwal</span>
                                <span class="sk-badge">Layanan latihan & rekaman</span>
                                <span class="sk-badge">Upload foto</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section class="max-w-7xl mx-auto px-6 pb-12 pt-4">
            <div class="text-center mb-8">
                <div class="sk-label">Fitur utama</div>
                <h2 class="sk-title text-3xl sm:text-4xl font-semibold mt-3">Semua tools studio dalam satu sistem.</h2>
                <p class="text-[var(--muted)] mt-3">
                    Semua fitur disusun agar owner bisa fokus pada kualitas layanan, bukan administrasi.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="sk-feature-card">
                    <div class="sk-feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="4" y="4" width="16" height="16" rx="3"></rect>
                            <path d="M8 8h8"></path>
                            <path d="M8 12h6"></path>
                            <path d="M8 16h5"></path>
                        </svg>
                    </div>
                    <div class="font-semibold">Kelola layanan & harga</div>
                    <p class="text-sm text-[var(--muted)] mt-2">
                        Atur layanan latihan/rekaman, harga, durasi, dan deskripsi layanan secara fleksibel.
                    </p>
                </div>
                <div class="sk-feature-card">
                    <div class="sk-feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                            <path d="M8 3v4"></path>
                            <path d="M16 3v4"></path>
                            <path d="M7 10h10"></path>
                        </svg>
                    </div>
                    <div class="font-semibold">Jadwal otomatis</div>
                    <p class="text-sm text-[var(--muted)] mt-2">
                        Jadwal tersusun rapi dengan status booking, pembatalan, hingga konfirmasi otomatis.
                    </p>
                </div>
                <div class="sk-feature-card">
                    <div class="sk-feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="3" y="4" width="18" height="14" rx="2"></rect>
                            <path d="M8 20h8"></path>
                            <path d="M12 8v4"></path>
                            <path d="M10 10h4"></path>
                        </svg>
                    </div>
                    <div class="font-semibold">Manajemen ruangan</div>
                    <p class="text-sm text-[var(--muted)] mt-2">
                        Pisahkan ruangan latihan dan rekaman, serta tampilkan detail kapasitas dengan rapi.
                    </p>
                </div>
                <div class="sk-feature-card">
                    <div class="sk-feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="3" y="7" width="18" height="10" rx="2"></rect>
                            <path d="M7 7v10"></path>
                            <path d="M11 7v10"></path>
                            <path d="M15 7v10"></path>
                        </svg>
                    </div>
                    <div class="font-semibold">Fasilitas & alat</div>
                    <p class="text-sm text-[var(--muted)] mt-2">
                        Daftarkan fasilitas dan alat yang tersedia agar pelanggan tahu keunggulan studionya.
                    </p>
                </div>
                <div class="sk-feature-card">
                    <div class="sk-feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="3" y="4" width="18" height="14" rx="2"></rect>
                            <path d="M8 14l2-2 3 3 4-5"></path>
                        </svg>
                    </div>
                    <div class="font-semibold">Foto studio & galeri</div>
                    <p class="text-sm text-[var(--muted)] mt-2">
                        Upload logo, foto ruangan, dan fasilitas untuk meningkatkan kepercayaan pelanggan.
                    </p>
                </div>
                <div class="sk-feature-card">
                    <div class="sk-feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
                        </svg>
                    </div>
                    <div class="font-semibold">Notifikasi booking</div>
                    <p class="text-sm text-[var(--muted)] mt-2">
                        Dapatkan notifikasi pemesanan masuk langsung di dashboard owner.
                    </p>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-6 pb-12">
            <div class="sk-card p-8 lg:p-10">
                <div class="grid lg:grid-cols-[1.2fr_0.8fr] gap-8 items-center">
                    <div>
                        <div class="sk-label">Langkah mudah</div>
                        <h2 class="sk-title text-2xl sm:text-3xl font-semibold mt-3">
                            Mulai dalam 3 langkah cepat.
                        </h2>
                        <div class="mt-5 space-y-4 text-sm text-[var(--muted)]">
                            <div>
                                <div class="font-semibold text-[var(--ink)]">1. Daftarkan studio</div>
                                Lengkapi profil, alamat, dan deskripsi studio.
                            </div>
                            <div>
                                <div class="font-semibold text-[var(--ink)]">2. Tambahkan layanan</div>
                                Atur layanan latihan/rekaman, fasilitas, dan ruangan.
                            </div>
                            <div>
                                <div class="font-semibold text-[var(--ink)]">3. Terima booking</div>
                                Booking otomatis masuk ke dashboard dan siap dikonfirmasi.
                            </div>
                        </div>
                    </div>
                    <div class="sk-soft p-6">
                        <div class="sk-label">Siap go live</div>
                        <div class="sk-title text-2xl font-semibold mt-2">Tingkatkan kepercayaan pelanggan.</div>
                        <p class="text-sm text-[var(--muted)] mt-3">
                            Profil lengkap dan jadwal yang jelas membuat pelanggan lebih cepat booking.
                        </p>
                        <div class="mt-5">
                            <a href="{{ route('register') }}" class="sk-btn">Mulai sekarang</a>
                        </div>
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
