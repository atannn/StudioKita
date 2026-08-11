<div class="overflow-hidden p-5 bg-white rounded-2xl">
    <div class="flex flex-wrap gap-6 justify-between items-start">
        <div class="flex flex-col justify-center">
            <div class="text-xl font-medium text-gray-950">Dashboard analytics</div>
            <div class="mt-1 text-sm text-gray-600">Ringkasan booking berdasarkan periode pilihan.</div>
        </div>
        @php
            $activeRange = request('range');
        @endphp
        <form method="GET" action="{{ route('owner.dashboard') }}" class="flex flex-wrap gap-3 items-end" data-analytics-form>
            <input type="hidden" name="top_month" value="{{ $topMonth ?? now()->format('Y-m') }}">
            <div>
                <label class="block text-xs text-gray-500">Mulai</label>
                <input type="date" name="start" value="{{ $filterStart ?? now()->subDays(29)->toDateString() }}"
                       class="mt-1 px-3 py-2 rounded-lg border border-gray-200 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Sampai</label>
                <input type="date" name="end" value="{{ $filterEnd ?? now()->toDateString() }}"
                       class="mt-1 px-3 py-2 rounded-lg border border-gray-200 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold">
                Terapkan
            </button>
            <div class="flex gap-2 text-xs">
                <a href="{{ route('owner.dashboard', ['range' => 'today']) }}"
                   data-range-link
                   class="px-3 py-2 rounded-lg {{ $activeRange === 'today' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    Hari ini
                </a>
                <a href="{{ route('owner.dashboard', ['range' => '7d']) }}"
                   data-range-link
                   class="px-3 py-2 rounded-lg {{ $activeRange === '7d' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    7 hari
                </a>
                <a href="{{ route('owner.dashboard', ['range' => '30d']) }}"
                   data-range-link
                   class="px-3 py-2 rounded-lg {{ $activeRange === '30d' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    30 hari
                </a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mt-6">
        <div class="rounded-xl border border-gray-100 p-4">
            <div class="text-xs text-gray-500">Total booking</div>
            <div class="text-lg font-semibold text-gray-900">{{ $metrics['total'] }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 p-4">
            <div class="text-xs text-gray-500">Booking aktif</div>
            <div class="text-lg font-semibold text-gray-900">{{ $metrics['active'] }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 p-4">
            <div class="text-xs text-gray-500">Selesai</div>
            <div class="text-lg font-semibold text-gray-900">{{ $metrics['completed'] }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 p-4">
            <div class="text-xs text-gray-500">Dibatalkan</div>
            <div class="text-lg font-semibold text-gray-900">{{ $metrics['cancelled'] }}</div>
        </div>
        <div class="rounded-xl border border-gray-100 p-4">
            <div class="text-xs text-gray-500">Pendapatan</div>
            <div class="text-lg font-semibold text-gray-900">Rp {{ number_format($metrics['revenue'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="mt-6">
        <div class="text-base font-semibold text-gray-800">Visualisasi utama</div>
        <div class="grid md:grid-cols-2 gap-5 mt-4">
            <div class="border border-gray-100 rounded-2xl p-4">
                <div class="text-sm font-semibold text-gray-700">Total booking per tipe</div>
                <div class="mt-3 h-48">
                    <canvas class="sk-bar-chart"
                            data-labels='@json($bookingByTypeLabels ?? [])'
                            data-values='@json($bookingByTypeCounts ?? [])'
                            data-color="#6366f1"></canvas>
                </div>
            </div>
            <div class="border border-gray-100 rounded-2xl p-4">
                <div class="text-sm font-semibold text-gray-700">Total booking per ruangan</div>
                <div class="mt-3 h-48">
                    <canvas class="sk-bar-chart"
                            data-labels='@json($bookingByRoomLabels ?? [])'
                            data-values='@json($bookingByRoomCounts ?? [])'
                            data-color="#22c55e"></canvas>
                </div>
            </div>
            <div class="border border-gray-100 rounded-2xl p-4">
                <div class="text-sm font-semibold text-gray-700">Pendapatan per tipe (jt)</div>
                <div class="mt-3 h-48">
                    <canvas class="sk-bar-chart"
                            data-labels='@json($revenueByTypeLabels ?? [])'
                            data-values='@json($revenueByTypeValues ?? [])'
                            data-color="#f59e0b"></canvas>
                </div>
            </div>
            <div class="border border-gray-100 rounded-2xl p-4">
                <div class="text-sm font-semibold text-gray-700">Pendapatan per ruangan (jt)</div>
                <div class="mt-3 h-48">
                    <canvas class="sk-bar-chart"
                            data-labels='@json($revenueByRoomLabels ?? [])'
                            data-values='@json($revenueByRoomValues ?? [])'
                            data-color="#fb7185"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
