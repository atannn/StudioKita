<x-owner-layout title="Manajemen Rooms">
    <x-slot name="actions">
        <a href="{{ route('owner.rooms.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded">
            + Tambah Room
        </a>
    </x-slot>

    <div class="space-y-4">
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <table class="w-full border border-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-200 p-2">Nama Ruangan</th>
                            <th class="border border-gray-200 p-2">Foto</th>
                            <th class="border border-gray-200 p-2">Tipe</th>
                            <th class="border border-gray-200 p-2">Kapasitas</th>
                            <th class="border border-gray-200 p-2">Fasilitas / Alat</th>
                            <th class="border border-gray-200 p-2">Status</th>
                            <th class="border border-gray-200 p-2 w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                            <tr>
                                <td class="border border-gray-200 p-2">{{ $room->nama_ruangan }}</td>
                                <td class="border border-gray-200 p-2 text-center">
                                    @if ($room->foto_ruangan)
                                        <img src="{{ asset('storage/'.$room->foto_ruangan) }}"
                                             alt="Foto {{ $room->nama_ruangan }}"
                                             class="h-24 w-24 object-cover rounded-lg border border-gray-200 mx-auto">
                                    @else
                                        <span class="text-xs text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="border border-gray-200 p-2">{{ $room->tipe_ruangan }}</td>
                                <td class="border border-gray-200 p-2">{{ $room->kapasitas }}</td>
                                <td class="border border-gray-200 p-2">
                                    @if ($room->facilities->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($room->facilities as $facility)
                                                <span class="rounded-full bg-indigo-50 px-2 py-1 text-xs text-indigo-700">
                                                    {{ $facility->nama_fasilitas }}
                                                    @if (($facility->quantity ?? 1) > 1)
                                                        x{{ $facility->quantity }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="border border-gray-200 p-2">
                                    {{ $room->status ? 'Aktif' : 'Nonaktif' }}
                                </td>
                                <td class="border border-gray-200 p-2">
                                    <a class="text-blue-600 underline"
                                       href="{{ route('owner.rooms.edit', $room->idrooms) }}">
                                        Edit
                                    </a>

                                    <form class="inline"
                                          method="POST"
                                          action="{{ route('owner.rooms.destroy', $room->idrooms) }}"
                                          onsubmit="return confirm('Hapus room ini?')">
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
                                <td class="border p-2 text-center" colspan="7">
                                    Belum ada room.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-owner-layout>
