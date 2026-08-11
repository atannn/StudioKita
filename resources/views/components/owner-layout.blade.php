@props([
    'title' => null,
    'subtitle' => null,
])

@php
    $tenant = auth()->user()?->tenant;
    $tenantName = $tenant?->nama ?? 'Studio Kita';
    $tenantOwnerName = $tenant?->nama_pemilik ?: auth()->user()?->name;
    $tenantLogo = $tenant?->primaryPhoto;
    $tenantId = auth()->user()?->tenants_idTenant;
    $pendingQuery = $tenantId
        ? \App\Models\Booking::where('tenants_idTenant', $tenantId)->where('status', 'pending')
        : null;
    $pendingCount = $pendingQuery ? (clone $pendingQuery)->count() : 0;
    $pendingBookings = $pendingQuery
        ? $pendingQuery->with(['user', 'service'])->orderBy('created_at', 'desc')->limit(5)->get()
        : collect();
    $announcement = \App\Models\Announcement::query()
        ->where('is_active', true)
        ->where(function ($q) {
            $q->where('target_role', 'owner')->orWhere('target_role', 'all');
        })
        ->latest()
        ->first();
    $activeClass = 'flex gap-4 px-4 py-3 w-full text-white bg-indigo-500 rounded-2xl';
    $inactiveClass = 'flex gap-4 items-center py-3 pl-4 w-full rounded-2xl text-neutral-500 hover:bg-indigo-50';
@endphp

<x-app-layout :hide-nav="true">
    <div class="px-4 py-6 md:px-8">
        <div class="overflow-hidden rounded-3xl bg-slate-50">
            <div class="flex flex-col gap-5 lg:flex-row">
                <aside class="w-full lg:w-[19%]">
                    <div class="flex flex-col items-start px-6 pt-4 pb-6 mx-auto w-full bg-white rounded-3xl">
                        <div class="flex gap-2 text-3xl font-semibold text-black">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 overflow-hidden">
                                @if ($tenantLogo)
                                    <img src="{{ asset('storage/'.$tenantLogo->foto_path) }}"
                                         alt="Logo Studio"
                                         class="w-full h-full object-cover">
                                @else
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M12 3a4 4 0 0 1 4 4c0 2.2-1.8 4-4 4s-4-1.8-4-4a4 4 0 0 1 4-4z"></path>
                                        <path d="M4 20a8 8 0 0 1 16 0"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="my-auto">
                                {{ $tenantName }}
                            </div>
                        </div>

                        <nav class="mt-14 w-full text-base font-medium space-y-3">
                            <a href="{{ route('owner.dashboard') }}"
                               class="{{ request()->routeIs('owner.dashboard') ? $activeClass : $inactiveClass }}">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 4h7v7H4z"></path>
                                    <path d="M13 4h7v7h-7z"></path>
                                    <path d="M4 13h7v7H4z"></path>
                                    <path d="M13 13h7v7h-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('owner.rooms.index') }}"
                               class="{{ request()->routeIs('owner.rooms.*') ? $activeClass : $inactiveClass }}">
                                Rooms
                            </a>
                            <a href="{{ route('owner.services.index') }}"
                               class="{{ request()->routeIs('owner.services.*') ? $activeClass : $inactiveClass }}">
                                Services
                            </a>
                            <a href="{{ route('owner.facilities.index') }}"
                               class="{{ request()->routeIs('owner.facilities.*') ? $activeClass : $inactiveClass }}">
                                Fasilitas
                            </a>
                            <a href="{{ route('owner.jadwals.index') }}"
                               class="{{ request()->routeIs('owner.jadwals.*') ? $activeClass : $inactiveClass }}">
                                Jadwal
                            </a>
                            <a href="{{ route('owner.bookings.index') }}"
                               class="{{ request()->routeIs('owner.bookings.*') ? $activeClass : $inactiveClass }}">
                                Booking
                            </a>
                            <a href="{{ route('owner.payment-settings.edit') }}"
                               class="{{ request()->routeIs('owner.payment-settings.*') ? $activeClass : $inactiveClass }}">
                                Payment
                            </a>
                            <a href="{{ route('owner.verification.index') }}"
                               class="{{ request()->routeIs('owner.verification.*') ? $activeClass : $inactiveClass }}">
                                Verifikasi
                            </a>
                        </nav>

                        <div class="flex items-center justify-between mt-16 w-full">
                            <div class="flex gap-3 items-center">
                                <div class="flex w-10 h-10 rounded-lg bg-neutral-200 shadow-[0px_2px_20px_rgba(0,0,0,0.15)]"></div>
                                <div>
                                    <div class="text-sm font-semibold text-zinc-900">{{ $tenantOwnerName }}</div>
                                    <div class="text-xs text-stone-500">{{ auth()->user()->role }}</div>
                                </div>
                            </div>
                            <a href="{{ route('owner.tenant.edit') }}"
                               class="flex w-8 h-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100"
                               aria-label="Pengaturan studio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h0a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v0a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"></path>
                                </svg>
                            </a>
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
                            <div class="flex gap-4 items-center w-full lg:w-auto" x-data="{ openNoti: false }">
                                <a href="{{ route('owner.tenant.edit') }}"
                                   class="flex w-11 h-11 items-center justify-center bg-white rounded-xl shadow"
                                   aria-label="Pengaturan studio">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h0a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v0a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"></path>
                                    </svg>
                                </a>
                                <div class="relative">
                                    <button class="flex w-11 h-11 items-center justify-center bg-white rounded-xl shadow"
                                            type="button"
                                            aria-label="Notifikasi"
                                            @click="openNoti = !openNoti">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"></path>
                                            <path d="M13.7 21a2 2 0 0 1-3.4 0"></path>
                                        </svg>
                                        @if ($pendingCount > 0)
                                            <span class="absolute -top-2 -right-2 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center">
                                                {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                                            </span>
                                        @endif
                                    </button>

                                    <div x-show="openNoti"
                                         x-transition
                                         @click.outside="openNoti = false"
                                         class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-lg border border-gray-100 z-30">
                                        <div class="px-4 py-3 border-b border-gray-100 text-sm font-semibold text-gray-900">
                                            Notifikasi
                                        </div>
                                        <div class="max-h-80 overflow-y-auto">
                                            @forelse ($pendingBookings as $booking)
                                                <a href="{{ route('owner.bookings.index', ['status' => 'pending']) }}"
                                                   class="flex gap-3 px-4 py-3 hover:bg-gray-50">
                                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-semibold">
                                                        {{ strtoupper(substr($booking->user?->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div class="text-sm">
                                                        <div class="text-gray-900 font-semibold">
                                                            {{ $booking->user?->name ?? 'Customer' }}
                                                        </div>
                                                        <div class="text-gray-500">
                                                            Booking {{ $booking->service?->nama_service ?? 'layanan' }}
                                                        </div>
                                                        <div class="text-xs text-gray-400">
                                                            {{ \Carbon\Carbon::parse($booking->tanggal_booking)->format('d M Y H:i') }}
                                                        </div>
                                                    </div>
                                                </a>
                                            @empty
                                                <div class="px-4 py-4 text-sm text-gray-500">
                                                    Belum ada pesanan masuk.
                                                </div>
                                            @endforelse
                                        </div>
                                        <div class="border-t border-gray-100 px-4 py-3 text-center">
                                            <a href="{{ route('owner.bookings.index', ['status' => 'pending']) }}"
                                               class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                                Semua Notifikasi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mx-6 lg:mx-8 mt-8">
                            @if ($announcement)
                                <div class="mb-6">
                                    <div id="owner-announcement"
                                         data-announcement-id="{{ $announcement->id }}"
                                         class="flex items-start gap-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold text-emerald-900">{{ $announcement->title }}</div>
                                            <div class="text-sm text-emerald-800 mt-1">{{ $announcement->body }}</div>
                                        </div>
                                        <button type="button"
                                                class="text-emerald-700 hover:text-emerald-900"
                                                aria-label="Hapus notifikasi"
                                                onclick="dismissOwnerAnnouncement()">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M18 6L6 18"></path>
                                                <path d="M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                            <script>
                                (function () {
                                    const el = document.getElementById('owner-announcement');
                                    if (!el) return;
                                    const id = el.getAttribute('data-announcement-id');
                                    const key = 'owner_announcement_dismissed_' + id;
                                    if (localStorage.getItem(key)) {
                                        el.style.display = 'none';
                                    }
                                    window.dismissOwnerAnnouncement = function () {
                                        localStorage.setItem(key, '1');
                                        el.style.display = 'none';
                                    };
                                })();
                            </script>
                            @if ($title || $subtitle || isset($actions))
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                                    <div>
                                        @if ($title)
                                            <h2 class="text-xl font-semibold text-gray-900">{{ $title }}</h2>
                                        @endif
                                        @if ($subtitle)
                                            <div class="text-sm text-gray-500">{{ $subtitle }}</div>
                                        @endif
                                    </div>
                                    @isset($actions)
                                        <div class="flex items-center gap-2">
                                            {{ $actions }}
                                        </div>
                                    @endisset
                                </div>
                            @endif

                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
