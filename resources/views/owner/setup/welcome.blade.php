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
        }

        html,
        body {
            min-height: 100%;
            background: var(--page-bg);
        }

        .sk-page {
            font-family: "IBM Plex Sans", sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at top left, rgba(15, 118, 110, 0.08), transparent 45%),
                radial-gradient(circle at top right, rgba(249, 115, 22, 0.12), transparent 45%),
                var(--page-bg);
            overflow: hidden;
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

        .sk-shell {
            max-width: 880px;
            margin: 0 auto;
        }

        .sk-hero-body {
            text-align: center;
            max-width: 640px;
            margin: 0 auto;
        }

        .sk-hero-logo {
            font-size: 3.4rem;
            letter-spacing: 0.02em;
        }

        .sk-hero-logo .sk-logo-studio {
            font-style: italic;
        }

        .sk-hero-logo .sk-logo-kita {
            font-weight: 700;
            color: var(--ink);
        }

        .sk-hero-subtitle {
            color: var(--muted);
        }

        .sk-btn {
            background: linear-gradient(135deg, var(--primary), #10b981);
            color: #ffffff;
            padding: 0.85rem 1.9rem;
            border-radius: 999px;
            font-weight: 600;
            box-shadow: 0 16px 32px rgba(15, 118, 110, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sk-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 38px rgba(15, 118, 110, 0.32);
        }

        .sk-reveal {
            animation: skReveal 0.7s ease both;
        }

        @keyframes skReveal {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="sk-page min-h-screen flex items-center justify-center px-6 py-12">
        <div class="sk-shell w-full">
            <div class="sk-hero-body mt-16 sk-reveal">
                <div class="sk-hero-logo sk-logo">
                    <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                </div>
                <h1 class="sk-title text-3xl md:text-4xl font-semibold mt-3">
                    Selamat datang, {{ $tenant->nama }}!
                </h1>
                <p class="sk-hero-subtitle text-base mt-4">
                    Studio kamu sudah Berhasil Terbuat. Yuk selesaikan langkah-langkah selanjutnya agar Studiomu Aktif.
                </p>

                <div class="mt-3 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ route('owner.dashboard') }}" class="sk-btn">Masuk ke Dashboard Owner</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
