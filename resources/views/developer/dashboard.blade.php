<x-app-layout :hide-nav="true">
    @php
        $activeClass = 'flex gap-4 px-4 py-3 w-full text-white bg-indigo-500 rounded-2xl';
        $inactiveClass = 'flex gap-4 items-center py-3 pl-4 w-full rounded-2xl text-neutral-500 hover:bg-indigo-50';
        $user = auth()->user();
    @endphp

    <div class="px-4 py-6 md:px-8">
        <div class="overflow-hidden rounded-3xl bg-slate-50">
            <div class="flex flex-col gap-5 lg:flex-row">
                <aside class="w-full lg:w-[19%]">
                    <div class="flex flex-col items-start px-6 pt-4 pb-6 mx-auto w-full bg-white rounded-3xl">
                        <div class="flex gap-2 text-3xl font-semibold text-black">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 3a4 4 0 0 1 4 4c0 2.2-1.8 4-4 4s-4-1.8-4-4a4 4 0 0 1 4-4z"></path>
                                    <path d="M4 20a8 8 0 0 1 16 0"></path>
                                </svg>
                            </div>
                            <div class="my-auto">
                                Developer
                            </div>
                        </div>

                        <nav class="mt-14 w-full text-base font-medium space-y-3">
                            <a href="{{ route('developer.dashboard') }}"
                               class="{{ request()->routeIs('developer.dashboard') ? $activeClass : $inactiveClass }}">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 4h7v7H4z"></path>
                                    <path d="M13 4h7v7h-7z"></path>
                                    <path d="M4 13h7v7H4z"></path>
                                    <path d="M13 13h7v7h-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </nav>

                        <div class="flex items-center justify-between mt-16 w-full">
                            <div class="flex gap-3 items-center">
                                <div class="flex w-10 h-10 rounded-lg bg-neutral-200 shadow-[0px_2px_20px_rgba(0,0,0,0.15)]"></div>
                                <div>
                                    <div class="text-sm font-semibold text-zinc-900">{{ $user->name }}</div>
                                    <div class="text-xs text-stone-500">{{ $user->role }}</div>
                                </div>
                            </div>
                        </div>

                        <form method="POST"
                              action="{{ route('logout') }}"
                              class="w-full mt-4"
                              onsubmit="return confirm('Apakah anda yakin untuk logout?')">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 rounded-2xl hover:bg-red-100">
                                Log Out
                            </button>
                        </form>
                    </div>
                </aside>

                <main class="w-full lg:w-[81%]">
                    <div class="py-6 mx-auto w-full bg-violet-50 rounded-[32px]">
                        <div class="flex flex-wrap gap-6 justify-between items-center px-6 lg:px-8">
                            <div class="flex gap-2 items-center text-lg font-semibold text-indigo-600">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 6v6l3 2"></path>
                                    <circle cx="12" cy="12" r="9"></circle>
                                </svg>
                                {{ now()->translatedFormat('l, d M Y') }}
                            </div>
                            <div class="flex items-center gap-4 w-full lg:w-auto">
                                <div class="w-full lg:w-80">
                                    <input type="text"
                                           name="q"
                                           form="developer-filter-form"
                                           value="{{ $filters['q'] ?? '' }}"
                                           class="w-full px-4 py-2.5 rounded-xl bg-zinc-300/30 text-stone-600"
                                           placeholder="Cari studio / owner / email">
                                </div>
                            </div>
                        </div>

                        <div class="mx-6 lg:mx-8 mt-8 space-y-6">
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                                <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                    <div class="text-xs font-medium text-gray-500">Total tenant</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $totalTenants }}</div>
                                </div>
                                <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                    <div class="text-xs font-medium text-gray-500">Tenant aktif</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $activeTenants }}</div>
                                </div>
                                <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                    <div class="text-xs font-medium text-gray-500">Owner terdaftar</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $ownerCount }}</div>
                                </div>
                                <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                    <div class="text-xs font-medium text-gray-500">Booking bulan ini</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $bookingThisMonth }}</div>
                                </div>
                                <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                    <div class="text-xs font-medium text-gray-500">Pending verifikasi</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $pendingManualVerifications }}</div>
                                </div>
                            </div>

                            <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                <div class="flex flex-wrap gap-4 justify-between">
                                    <div>
                                        <div class="text-xs uppercase tracking-widest text-gray-500">Studio lengkap setup</div>
                                        <div class="mt-2 text-xl font-semibold text-gray-900">{{ $completeStudios }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-widest text-gray-500">Belum isi deskripsi</div>
                                        <div class="mt-2 text-xl font-semibold text-gray-900">{{ $missingDescription }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-widest text-gray-500">Belum ada foto/ruangan</div>
                                        <div class="mt-2 text-xl font-semibold text-gray-900">{{ $missingPhotos + $missingRooms }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                <form id="developer-filter-form" class="grid gap-3 md:grid-cols-5">
                                    <input class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-stone-600"
                                           name="q"
                                           value="{{ $filters['q'] ?? '' }}"
                                           placeholder="Cari studio / owner / email">
                                    <select class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-stone-600" name="status">
                                        <option value="">Semua status</option>
                                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                                    </select>
                                    <select class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-stone-600" name="kota">
                                        <option value="">Semua kota</option>
                                        @foreach ($cities as $city)
                                            <option value="{{ $city }}" @selected(($filters['kota'] ?? '') === $city)>{{ $city }}</option>
                                        @endforeach
                                    </select>
                                    <select class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-stone-600" name="verification_status">
                                        <option value="">Semua verifikasi</option>
                                        <option value="draft" @selected(($filters['verification_status'] ?? '') === 'draft')>Draft</option>
                                        <option value="pending" @selected(($filters['verification_status'] ?? '') === 'pending')>Pending</option>
                                        <option value="approved" @selected(($filters['verification_status'] ?? '') === 'approved')>Approved</option>
                                        <option value="rejected" @selected(($filters['verification_status'] ?? '') === 'rejected')>Rejected</option>
                                    </select>
                                    <button class="px-6 py-2.5 rounded-full bg-indigo-500 text-white font-semibold shadow">
                                        Filter
                                    </button>
                                </form>
                            </div>

                            <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-left border-b border-zinc-200 text-xs uppercase tracking-widest text-gray-500">
                                                <th class="py-3">Studio</th>
                                                <th class="py-3">Owner</th>
                                                <th class="py-3">Kota</th>
                                                <th class="py-3">Status</th>
                                                <th class="py-3">Verifikasi</th>
                                                <th class="py-3">Terdaftar</th>
                                                <th class="py-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($tenants as $tenant)
                                                <tr class="border-b border-zinc-200">
                                                    <td class="py-3 font-semibold text-gray-900">
                                                        {{ $tenant->nama }}
                                                        <div class="text-xs text-gray-500">{{ $tenant->email }}</div>
                                                    </td>
                                                    <td class="py-3 text-gray-700">
                                                        {{ $tenant->nama_pemilik ?? '-' }}
                                                        <div class="text-xs text-gray-500">{{ $tenant->email }}</div>
                                                    </td>
                                                    <td class="py-3 text-gray-700">{{ $tenant->kota ?? '-' }}, {{ $tenant->provinsi ?? '-' }}</td>
                                                    <td class="py-3">
                                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">
                                                            {{ $tenant->status ?? 'inactive' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3">
                                                        <div class="space-y-1">
                                                            <span class="inline-flex px-2 py-1 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700">
                                                                {{ str_replace('_', ' ', $tenant->verification_level ?? 'none') }}
                                                            </span>
                                                            <div>
                                                                <span class="inline-flex px-2 py-1 rounded-full text-[11px] font-semibold
                                                                    {{ ($tenant->verification_status ?? 'draft') === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                                                    {{ ($tenant->verification_status ?? 'draft') === 'approved' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                                    {{ ($tenant->verification_status ?? 'draft') === 'rejected' ? 'bg-rose-100 text-rose-700' : '' }}
                                                                    {{ ($tenant->verification_status ?? 'draft') === 'draft' ? 'bg-gray-200 text-gray-600' : '' }}">
                                                                    {{ $tenant->verification_status ?? 'draft' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 text-gray-700">
                                                        @if ($tenant->createdAt)
                                                            {{ \Illuminate\Support\Carbon::parse($tenant->createdAt)->format('d M Y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="py-3 text-right">
                                                        @php
                                                            $canActivateStudio = ($tenant->verification_level ?? null) === 'verified'
                                                                && ($tenant->verification_status ?? null) === 'approved';
                                                        @endphp
                                                        <div class="flex justify-end items-center gap-2">
                                                            <a href="{{ route('developer.tenants.show', $tenant->slug) }}" class="px-4 py-2 rounded-full bg-indigo-500 text-white text-xs font-semibold shadow">
                                                                Detail
                                                            </a>
                                                            @if ($tenant->status === 'active')
                                                                <form method="POST" action="{{ route('developer.tenants.status', $tenant->slug) }}"
                                                                      onsubmit="return confirm('Nonaktifkan studio ini?')">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit"
                                                                            class="px-4 py-2 rounded-full text-xs font-semibold shadow bg-rose-100 text-rose-700 hover:bg-rose-200">
                                                                        Nonaktifkan
                                                                    </button>
                                                                </form>
                                                            @elseif ($canActivateStudio)
                                                                <form method="POST" action="{{ route('developer.tenants.status', $tenant->slug) }}"
                                                                      onsubmit="return confirm('Aktifkan studio ini?')">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit"
                                                                            class="px-4 py-2 rounded-full text-xs font-semibold shadow bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                                                                        Aktifkan
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <button type="button"
                                                                        class="px-4 py-2 rounded-full text-xs font-semibold shadow bg-gray-100 text-gray-400 cursor-not-allowed"
                                                                        title="Studio harus Verified Level 2 untuk dapat diaktifkan."
                                                                        disabled>
                                                                    Belum bisa aktif
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="py-6 text-center text-gray-500">
                                                        Belum ada tenant yang terdaftar.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4">
                                    {{ $tenants->links() }}
                                </div>
                            </div>

                            <div class="overflow-hidden p-5 bg-white rounded-2xl">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div>
                                        <div class="text-xs uppercase tracking-widest text-gray-500">Broadcast</div>
                                        <div class="mt-2 text-xl font-semibold text-gray-900">Kirim pengumuman ke owner</div>
                                    </div>
                                </div>

                                @if (session('success'))
                                    <div class="mt-4 p-3 bg-emerald-50 text-emerald-800 rounded-md">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('developer.announcements.store') }}" class="mt-5 grid gap-4">
                                    @csrf
                                    <div class="grid gap-4 md:grid-cols-3">
                                        <div class="md:col-span-2">
                                            <label class="text-xs font-semibold text-gray-600">Judul</label>
                                            <input name="title" class="w-full mt-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-stone-600"
                                                   placeholder="Contoh: Update fitur terbaru" required>
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-600">Target</label>
                                            <select name="target_role" class="w-full mt-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-stone-600">
                                                <option value="owner">Owner studio</option>
                                                <option value="all">Semua user</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Isi pengumuman</label>
                                        <textarea name="body" rows="4"
                                                  class="w-full mt-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-stone-600"
                                                  placeholder="Tulis pesan singkat untuk owner studio."
                                                  required></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-6 py-2.5 rounded-full bg-indigo-500 text-white font-semibold shadow">
                                            Kirim Pengumuman
                                        </button>
                                    </div>
                                </form>

                                @if ($announcements->count())
                                    <div class="mt-6 border-t border-gray-100 pt-5">
                                        <div class="text-xs uppercase tracking-widest text-gray-500">Terbaru</div>
                                        <div class="mt-3 space-y-3">
                                            @foreach ($announcements as $announcement)
                                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                                                    <div class="flex items-center justify-between">
                                                        <div class="text-sm font-semibold text-gray-900">{{ $announcement->title }}</div>
                                                        <div class="text-xs text-gray-500">{{ $announcement->created_at->format('d M Y') }}</div>
                                                    </div>
                                                    <div class="text-sm text-gray-600 mt-2">{{ $announcement->body }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
