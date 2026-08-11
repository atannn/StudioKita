<x-owner-layout title="Edit Room">
    <div class="max-w-3xl mx-auto space-y-4">
        @if ($errors->any())
            <div class="p-4 bg-red-100 text-red-800 rounded">
                <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                <ul class="list-disc ml-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form method="POST" action="{{ route('owner.rooms.update', $room->idrooms) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <div>
                            <label class="block mb-1 font-medium">Nama Ruangan</label>
                            <input name="nama_ruangan"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('nama_ruangan', $room->nama_ruangan) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Deskripsi</label>
                            <input name="deskripsi"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('deskripsi', $room->deskripsi) }}">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Kapasitas</label>
                            <input type="number" name="kapasitas" min="1"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('kapasitas', $room->kapasitas) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tipe Ruangan</label>
                            <select name="tipe_ruangan"
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                <option value="latihan" @selected(old('tipe_ruangan', $room->tipe_ruangan)==='latihan')>latihan</option>
                                <option value="rekaman" @selected(old('tipe_ruangan', $room->tipe_ruangan)==='rekaman')>rekaman</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Foto Ruangan</label>
                            <input type="file" name="foto_ruangan" accept="image/*"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-2 text-xs text-gray-500">PNG/JPG maksimal 5MB.</p>
                            @if ($room->foto_ruangan)
                                <div class="mt-3">
                                    <img src="{{ asset('storage/'.$room->foto_ruangan) }}"
                                         alt="Foto {{ $room->nama_ruangan }}"
                                         class="h-24 w-32 rounded-lg object-cover border border-gray-200">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status</label>
                            <select name="status"
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                <option value="1" @selected((string)old('status', (string)$room->status)==='1')>Aktif</option>
                                <option value="0" @selected((string)old('status', (string)$room->status)==='0')>Nonaktif</option>
                            </select>
                        </div>

                        @include('owner.rooms.partials.facility-selector', ['facilities' => $facilities, 'room' => $room])

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2
                                       bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest
                                       hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500
                                       focus:ring-offset-2
                                       transition ease-in-out duration-150">
                                Update
                            </button>

                            <a href="{{ route('owner.rooms.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300
                                      rounded-md font-semibold text-xs text-gray-700 uppercase
                                      tracking-widest hover:bg-gray-50
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                      focus:ring-offset-2
                                      transition ease-in-out duration-150">
                                Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-owner-layout>
