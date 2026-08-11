<x-owner-layout title="Manajemen Services">
    <x-slot name="actions">
        <a href="{{ route('owner.services.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            + Tambah Service
        </a>
    </x-slot>

    <div class="space-y-4">
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded dark:bg-green-900/30 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <table class="w-full border border-gray-200 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/40">
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Nama</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Tipe</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Durasi</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Harga Weekdays</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Harga Weekend</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Status</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2 w-48">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($services as $s)
                            @php
                                $weekdayPrice = $s->weekday_price;
                                $weekendPrice = $s->weekend_price;
                            @endphp
                            <tr>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $s->nama_service }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $s->tipe_service }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $s->durasi_menit }} menit</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    Rp {{ number_format($weekdayPrice, 0, ',', '.') }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    Rp {{ number_format($weekendPrice, 0, ',', '.') }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    {{ $s->status ? 'Aktif' : 'Nonaktif' }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    <a class="text-blue-600 underline"
                                       href="{{ route('owner.services.edit', $s->idservice) }}">
                                        Edit
                                    </a>

                                    <form class="inline"
                                          method="POST"
                                          action="{{ route('owner.services.destroy', $s->idservice) }}"
                                          onsubmit="return confirm('Hapus service ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 underline ml-3" type="submit">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="border border-gray-200 dark:border-gray-700 p-2 text-center" colspan="7">
                                    Belum ada service.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-owner-layout>
