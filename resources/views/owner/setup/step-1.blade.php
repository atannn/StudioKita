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
            height: 100%;
            overflow: hidden;
        }

        .sk-page {
            font-family: "IBM Plex Sans", sans-serif;
            color: var(--ink);
            background: var(--page-bg);
            overflow: hidden;
        }

        html,
        body {
            min-height: 100%;
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

        .sk-card {
            background: var(--card);
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
            min-height: 720px;
        }

        .sk-pane {
            background: linear-gradient(180deg, rgba(15, 118, 110, 0.95), rgba(16, 185, 129, 0.9));
            border-radius: 24px;
            color: #ffffff;
            padding: 2.75rem 2.5rem;
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

        .sk-step-card {
            position: absolute;
            left: 1.75rem;
            right: 1.75rem;
            bottom: 1.75rem;
        }

        @media (max-width: 1023px) {
            .sk-step-card {
                position: static;
                margin-top: 1rem;
            }
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

        .sk-input[readonly] {
            background: #f8fafc;
            color: #6b7280;
            cursor: not-allowed;
        }

        .sk-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--muted);
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
                <div class="flex flex-col md:flex-row gap-8 items-stretch">
                    <div class="sk-pane sk-pane-wrap sk-pane-tall flex flex-col">
                        <div class="sk-pane-top">
                            <div class="sk-logo sk-pane-logo">
                                <span class="sk-logo-studio">Studio</span><span class="sk-logo-kita">Kita.</span>
                            </div>
                            <div class="sk-title sk-pane-title">Mulai setup studio kamu.</div>
                            <div class="sk-pane-text">
                                Lengkapi informasi studio agar pelanggan mudah menemukan dan melakukan booking.
                            </div>
                        </div>
                        <div class="sk-step-card rounded-2xl bg-white/10 p-4">
                            <div class="text-xs uppercase tracking-widest text-white/70">Step 1 of 3</div>
                            <div class="mt-2 text-base font-semibold">Informasi studio</div>
                            <div class="mt-2 text-sm text-white/75">
                                Tambahkan nama, lokasi, dan kontak studio kamu.
                            </div>
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div>
                                <h2 class="sk-title text-2xl font-semibold">Informasi studio</h2>
                                <p class="text-sm text-[var(--muted)] mt-2">
                                    Data ini akan tampil di halaman studio dan dashboard owner.
                                </p>
                            </div>
                            <div class="sk-steps sk-steps-inline">
                                <span class="sk-step is-active"></span>
                                <span class="sk-step"></span>
                                <span class="sk-step"></span>
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

                        <form class="mt-6 grid gap-4" method="POST" action="{{ route('owner.setup.step1.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div>
                                <label class="sk-label">Logo Studio</label>
                                <input type="file" name="logo" accept="image/*" class="sk-input mt-2">
                                <div class="text-xs text-[var(--muted)] mt-1">PNG/JPG maksimal 2MB.</div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="sk-label">Nama Studio</label>
                                    <input name="nama" class="sk-input mt-2" value="{{ old('nama', $tenant?->nama) }}" required>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="sk-label">Nama Pemilik</label>
                                    <input name="nama_pemilik" class="sk-input mt-2" value="{{ old('nama_pemilik', $tenant?->nama_pemilik ?? $user->name) }}" readonly>
                                </div>
                                <div>
                                    <label class="sk-label">Email</label>
                                    <input type="email" name="email" class="sk-input mt-2" value="{{ old('email', $tenant?->email ?? $user->email) }}" required>
                                </div>
                            </div>

                            <div>
                                <label class="sk-label">No. Telp</label>
                                <input name="no_telp" class="sk-input mt-2" value="{{ old('no_telp', $tenant?->no_telp) }}" required>
                            </div>

                            <div>
                                <label class="sk-label">Alamat</label>
                                <input name="alamat" class="sk-input mt-2" value="{{ old('alamat', $tenant?->alamat) }}">
                            </div>

                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="sk-label">Provinsi</label>
                                    <input name="provinsi" class="sk-input mt-2" value="{{ old('provinsi', $tenant?->provinsi) }}" required>
                                </div>
                                <div>
                                    <label class="sk-label">Kota</label>
                                    <input name="kota" class="sk-input mt-2" value="{{ old('kota', $tenant?->kota) }}" required>
                                </div>
                                <div>
                                    <label class="sk-label">Kecamatan</label>
                                    <input name="kecamatan" class="sk-input mt-2" value="{{ old('kecamatan', $tenant?->kecamatan) }}" required>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="submit" class="sk-btn">Simpan informasi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
