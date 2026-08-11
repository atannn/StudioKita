<x-owner-layout title="Template Jadwal" subtitle="Pola jadwal berulang untuk generate slot sampai akhir bulan depan.">
    <x-slot name="actions">
        <form method="POST" action="{{ route('owner.jadwals.templates.sync-all') }}">
            @csrf
            <button type="submit" class="px-4 py-2 border border-emerald-200 text-emerald-700 bg-white rounded">
                Sync s.d. Akhir Bulan Depan
            </button>
        </form>
        <a href="{{ route('owner.jadwals.templates.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded">
            + Tambah Template
        </a>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('template'))
            <div class="p-3 bg-red-100 text-red-800 rounded">
                {{ $errors->first('template') }}
            </div>
        @endif

        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
            Template menghasilkan slot dari hari ini sampai akhir bulan depan. Slot manual tetap dipertahankan. Jika ada bentrok dengan slot yang sudah ada, template akan di-skip pada tanggal tersebut.
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <table class="w-full border border-gray-200 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/40">
                            <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Template</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Service</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Room</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Pola</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Jam</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Aktif</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2 text-left w-56">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            @php
                                $repeatLabel = match ($template->repeat_mode) {
                                    'daily' => 'Setiap hari',
                                    'weekdays' => 'Weekdays',
                                    'weekends' => 'Weekend',
                                    'custom_days' => 'Custom: '.collect($template->days_of_week_json ?? [])
                                        ->map(fn ($day) => [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'][(int) $day] ?? $day)
                                        ->implode(', '),
                                    default => $template->repeat_mode,
                                };
                            @endphp
                            <tr>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $template->nama_template }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $template->service?->nama_service ?? '-' }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $template->room?->nama_ruangan ?? '-' }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $repeatLabel }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ substr($template->waktu_mulai, 0, 5) }} - {{ substr($template->waktu_selesai, 0, 5) }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    @if ($template->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a href="{{ route('owner.jadwals.templates.edit', $template->id) }}" class="text-blue-600 underline">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('owner.jadwals.templates.toggle-active', $template->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="{{ $template->is_active ? 'text-amber-600' : 'text-emerald-600' }} underline">
                                                {{ $template->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('owner.jadwals.templates.sync', $template->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-emerald-600 underline">
                                                Sync
                                            </button>
                                        </form>
                                        <form method="POST"
                                              action="{{ route('owner.jadwals.templates.destroy', $template->id) }}"
                                              class="inline"
                                              onsubmit="return confirm('Hapus template ini? Slot template sampai akhir bulan depan yang belum dibooking akan dibersihkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="border p-4 text-center text-gray-500" colspan="7">
                                    Belum ada template jadwal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($templates->hasPages())
                    <div class="mt-4">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('owner.jadwals.index') }}"
               class="inline-flex items-center px-5 py-3 border border-indigo-200 bg-white text-indigo-700 rounded-lg font-semibold hover:bg-indigo-50 transition">
                Kembali ke Jadwal
            </a>
        </div>
    </div>
</x-owner-layout>
