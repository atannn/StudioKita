<x-owner-layout title="Edit Fasilitas">
    <div class="max-w-3xl mx-auto space-y-4">
        @if ($errors->any())
            <div class="p-4 bg-red-100 text-red-800 rounded dark:bg-red-900/30 dark:text-red-200">
                <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                <ul class="list-disc ml-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <form method="POST" action="{{ route('owner.facilities.update', $facility->idfasiltas) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <div>
                            <label class="block mb-1 font-medium">Nama Fasilitas</label>
                            <input name="nama_fasilitas"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('nama_fasilitas', $facility->nama_fasilitas) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Deskripsi</label>
                            <input name="deskripsi"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('deskripsi', $facility->deskripsi) }}">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Jumlah Tersedia</label>
                            <input type="number"
                                   name="quantity"
                                   min="1"
                                   max="999"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('quantity', $facility->quantity ?? 1) }}"
                                   required>
                            <p class="mt-2 text-xs text-gray-500">
                                Jumlah alat/fasilitas ini dikelola di data fasilitas, bukan di form ruangan.
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status</label>
                            <select name="status"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                <option value="1" @selected((string)old('status', (string)$facility->status)==='1')>Aktif</option>
                                <option value="0" @selected((string)old('status', (string)$facility->status)==='0')>Nonaktif</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2
                                       bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest
                                       hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500
                                       focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                       transition ease-in-out duration-150">
                                Update
                            </button>

                            <a href="{{ route('owner.facilities.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300
                                      rounded-md font-semibold text-xs text-gray-700 uppercase
                                      tracking-widest hover:bg-gray-50 dark:border-gray-600
                                      dark:text-gray-300 dark:hover:bg-gray-700
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                      focus:ring-offset-2 dark:focus:ring-offset-gray-800
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
