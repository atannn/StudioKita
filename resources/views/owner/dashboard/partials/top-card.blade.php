<div class="overflow-hidden p-5 bg-white rounded-3xl">
    <div class="flex items-center justify-between">
        <div class="text-lg font-medium text-gray-800">Top layanan & ruangan</div>
        <form method="GET" action="{{ route('owner.dashboard') }}" class="flex items-center gap-2 text-xs" data-top-form>
            <input type="hidden" name="start" value="{{ $filterStart ?? now()->subDays(29)->toDateString() }}">
            <input type="hidden" name="end" value="{{ $filterEnd ?? now()->toDateString() }}">
            <select name="top_month"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-xs text-gray-600">
                @php
                    $monthOptions = collect(range(0, 5))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
                @endphp
                @foreach ($monthOptions as $month)
                    <option value="{{ $month->format('Y-m') }}" @selected(($topMonth ?? '') === $month->format('Y-m'))>
                        {{ $month->translatedFormat('F Y') }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="mt-4 space-y-5">
        <div class="space-y-3">
            <div class="text-xs uppercase tracking-[0.2em] text-gray-500">Top layanan</div>
            @forelse ($topServices as $service)
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $service->label }}</div>
                        <div class="text-xs text-gray-500">{{ $service->total }} booking</div>
                    </div>
                    <div class="text-sm font-semibold text-emerald-600">
                        Rp {{ number_format($service->revenue ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500">Belum ada data layanan.</div>
            @endforelse
        </div>

        <div class="space-y-3">
            <div class="text-xs uppercase tracking-[0.2em] text-gray-500">Top ruangan</div>
            @forelse ($topRooms as $room)
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $room->label }}</div>
                        <div class="text-xs text-gray-500">{{ $room->total }} booking</div>
                    </div>
                    <div class="text-sm font-semibold text-emerald-600">
                        Rp {{ number_format($room->revenue ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500">Belum ada data ruangan.</div>
            @endforelse
        </div>
    </div>
</div>
