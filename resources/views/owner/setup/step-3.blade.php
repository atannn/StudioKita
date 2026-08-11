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

        html,
        body {
            min-height: 100%;
            background: var(--page-bg);
        }

        .sk-page {
            font-family: "IBM Plex Sans", sans-serif;
            color: var(--ink);
            background: var(--page-bg);
            overflow: hidden;
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

        .sk-btn.is-disabled {
            background: #cbd5f5;
            color: #f8fafc;
            box-shadow: none;
            cursor: not-allowed;
            pointer-events: none;
        }

        .sk-card {
            background: var(--card);
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
            min-height: 720px;
            display: flex;
            flex-direction: column;
        }

        .sk-card-body {
            flex: 1;
            display: flex;
        }

        .sk-pane {
            background: linear-gradient(180deg, rgba(15, 118, 110, 0.95), rgba(16, 185, 129, 0.9));
            border-radius: 24px;
            color: #ffffff;
            padding: 2.75rem 2.5rem;
            padding-bottom: 7rem;
            position: relative;
            overflow: hidden;
        }

        .sk-pane::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            right: -80px;
            top: -80px;
        }

        .sk-pane-top {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .sk-pane-logo {
            font-size: 1.4rem;
        }

        .sk-pane-title {
            font-size: 2rem;
            line-height: 1.25;
            font-weight: 600;
        }

        .sk-pane-text {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.5;
            max-width: 22rem;
        }

        .sk-step-card {
            position: absolute;
            left: 1.75rem;
            right: 1.75rem;
            bottom: 1.75rem;
        }

        @media (max-width: 1023px) {
            .sk-step-card {
                position: static;
                margin-top: 2rem;
            }
        }

        .sk-pane-wrap {
            flex: 0 0 34%;
            max-width: 34%;
        }

        .sk-pane-tall {
            min-height: 100%;
        }

        @media (max-width: 1023px) {
            .sk-pane-wrap {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .sk-steps {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .sk-steps-inline {
            margin: 0;
            justify-content: flex-end;
        }

        .sk-header {
            margin-bottom: 0.5rem;
        }

        .sk-form-col {
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .sk-form {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .sk-form-body {
            margin-top: 1.5rem;
        }

        .sk-actions {
            margin-top: auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            padding-top: 1.25rem;
        }

        .sk-step {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.15);
        }

        .sk-step.is-active {
            background: var(--primary);
        }

        .sk-input {
            border: 1px solid rgba(15, 23, 42, 0.15);
            border-radius: 14px;
            padding: 0.65rem 0.9rem;
            background: #ffffff;
            width: 100%;
        }

        .sk-input:focus {
            outline: 2px solid rgba(15, 118, 110, 0.25);
            border-color: rgba(15, 118, 110, 0.4);
        }

        .sk-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--muted);
        }

        .sk-upload {
            border: 1px dashed rgba(15, 23, 42, 0.2);
            border-radius: 18px;
            padding: 1.25rem;
            background: #f8fafc;
        }

        .sk-thumb {
            border-radius: 16px;
            background: #eef2f7;
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .sk-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sk-placeholder-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .sk-placeholder-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .sk-placeholder {
            border: 1px dashed rgba(15, 23, 42, 0.18);
            border-radius: 16px;
            background: #f8fafc;
            color: rgba(15, 23, 42, 0.45);
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
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

    <div class="sk-page min-h-screen flex items-center">
        <div class="w-full max-w-6xl mx-auto px-6 py-6">
            <div class="sk-card p-6 lg:p-8 sk-animate-in">
                <div class="sk-card-body flex flex-col md:flex-row gap-8 items-stretch">
                    <div class="sk-pane sk-pane-wrap sk-pane-tall flex flex-col">
                        <div class="sk-pane-top">
                            <div class="sk-logo sk-pane-logo">
                                <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                            </div>
                            <div class="sk-title sk-pane-title">Unggah foto studio.</div>
                            <div class="sk-pane-text">
                                Tampilkan suasana ruangan dan fasilitas agar pelanggan makin yakin.
                            </div>
                        </div>
                        <div class="sk-step-card rounded-2xl bg-white/10 p-4">
                            <div class="text-xs uppercase tracking-widest text-white/70">Step 3 of 3</div>
                            <div class="mt-2 text-base font-semibold">Foto ruangan & fasilitas</div>
                            <div class="mt-2 text-sm text-white/75">
                                Unggah minimal 3 foto, maksimal 8 foto.
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 sk-form-col">
                        <div class="sk-header flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div>
                                <h2 class="sk-title text-2xl font-semibold">Foto ruangan & fasilitas</h2>
                                <p class="text-sm text-[var(--muted)] mt-2">
                                    Foto ini tampil di halaman studio dan katalog.
                                </p>
                            </div>
                            <div class="sk-steps sk-steps-inline">
                                <span class="sk-step"></span>
                                <span class="sk-step"></span>
                                <span class="sk-step is-active"></span>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="mt-4 p-3 bg-emerald-50 text-emerald-800 rounded-md">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mt-4 p-4 bg-red-50 text-red-700 rounded-md">
                                <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                                <ul class="list-disc ml-5 space-y-1 text-sm">
                                    @foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @php
                            $galleryCount = $tenant->photos->where('is_primary', false)->count();
                            $readyToStart = $galleryCount >= 3;
                            $placeholderSlots = max(0, min(6, 8 - $galleryCount));
                        @endphp
                        <form class="sk-form" method="POST" action="{{ route('owner.setup.step3.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="sk-form-body space-y-4">
                                <div class="sk-upload">
                                    <label class="sk-label">Pilih foto utama studio kamu</label>
                                    <input type="file" name="gallery[]" accept="image/*" multiple class="sk-input mt-3">
                                    <div class="text-xs text-[var(--muted)] mt-2">
                                        Minimal 3 foto, maksimal 8 foto. Ukuran file maksimal 5MB per foto.
                                    </div>
                                </div>

                                @if ($galleryCount)
                                    <div>
                                        <div class="text-sm font-semibold mb-3">Foto yang sudah diunggah</div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                            @foreach($tenant->photos->where('is_primary', false) as $photo)
                                                <div class="sk-thumb aspect-[4/3]">
                                                    <img src="{{ asset('storage/'.$photo->foto_path) }}" alt="Foto studio">
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($placeholderSlots > 0)
                                            <div class="mt-4">
                                                <div class="text-xs text-[var(--muted)] mb-2">Slot foto tersisa</div>
                                                <div class="sk-placeholder-grid">
                                                    @for ($i = 0; $i < $placeholderSlots; $i++)
                                                        <div class="sk-placeholder aspect-[4/3]">Slot foto</div>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div>
                                        <div class="text-sm font-semibold mb-3">Preview galeri</div>
                                        <div class="sk-placeholder-grid">
                                            @for ($i = 0; $i < 6; $i++)
                                                <div class="sk-placeholder aspect-[4/3]">Slot foto</div>
                                            @endfor
                                        </div>
                                        <div class="text-xs text-[var(--muted)] mt-2">
                                            Foto-foto utama studio kamu.
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="sk-actions">
                                <a href="{{ route('owner.setup.step2') }}" class="text-sm text-[var(--muted)] hover:text-[var(--primary-dark)]">
                                    Kembali ke step 2
                                </a>
                                <button type="submit" class="sk-btn">Simpan foto</button>
                                @if ($readyToStart)
                                    <a href="{{ route('owner.welcome') }}" class="sk-btn">Mulai Studio</a>
                                @else
                                    <span class="sk-btn is-disabled" aria-disabled="true">Mulai Studio</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
