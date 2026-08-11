<x-owner-layout title="Manajemen Fasilitas">
    <x-slot name="actions">
        <a href="{{ route('owner.facilities.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            + Tambah Fasilitas
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
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Deskripsi</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Jumlah</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Dipakai di Ruangan</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2">Status</th>
                            <th class="border border-gray-200 dark:border-gray-700 p-2 w-48">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($facilities as $facility)
                            <tr>
                                @php
                                    $quantity = max(1, (int) ($facility->quantity ?? 1));
                                @endphp
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $facility->nama_fasilitas }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $facility->deskripsi ?? '-' }}</td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    {{ $quantity }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    @if ($facility->rooms->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($facility->rooms as $room)
                                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
                                                    {{ $room->nama_ruangan }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">Belum ditempatkan</span>
                                    @endif
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    {{ $facility->status ? 'Aktif' : 'Nonaktif' }}
                                </td>
                                <td class="border border-gray-200 dark:border-gray-700 p-2">
                                    <a class="text-blue-600 underline"
                                       href="{{ route('owner.facilities.edit', $facility->idfasiltas) }}">
                                        Edit
                                    </a>

                                    <form class="inline"
                                          method="POST"
                                          action="{{ route('owner.facilities.destroy', $facility->idfasiltas) }}"
                                          onsubmit="return confirm('Hapus fasilitas ini?')">
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
                                <td class="border border-gray-200 dark:border-gray-700 p-2 text-center" colspan="6">
                                    Belum ada fasilitas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-owner-layout>
