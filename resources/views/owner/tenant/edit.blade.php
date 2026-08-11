<x-owner-layout title="Pengaturan Studio" subtitle="Kelola profil studio Anda.">
    <div class="space-y-6">
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

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

        <form method="POST" action="{{ route('owner.tenant.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div class="space-y-5">
                        <div class="text-sm font-semibold text-gray-900">Informasi Studio</div>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 rounded-2xl bg-gray-100 overflow-hidden flex items-center justify-center">
                                @if ($tenant?->primaryPhoto)
                                    <img src="{{ asset('storage/'.$tenant->primaryPhoto->foto_path) }}"
                                         alt="Logo Studio"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs text-gray-400">No Logo</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="block mb-1 font-medium">Logo Studio</label>
                                <input type="file" name="logo" accept="image/*"
                                       class="block w-full text-sm text-gray-600">
                                <div class="text-xs text-gray-500 mt-1">PNG/JPG maksimal 2MB.</div>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Nama Studio</label>
                            <input name="nama"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('nama', $tenant->nama) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Slug</label>
                            <input name="slug"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('slug', $tenant->slug) }}"
                                   required>
                            <div class="text-xs text-gray-500 mt-1">Digunakan untuk URL studio.</div>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Nama Pemilik</label>
                            <input name="nama_pemilik"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('nama_pemilik', $tenant->nama_pemilik) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Email</label>
                            <input type="email" name="email"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('email', $tenant->email) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">No. Telp</label>
                            <input name="no_telp"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('no_telp', $tenant->no_telp) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Alamat</label>
                            <input name="alamat"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('alamat', $tenant->alamat) }}">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Provinsi</label>
                            <input name="provinsi"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('provinsi', $tenant->provinsi) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Kota</label>
                            <input name="kota"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('kota', $tenant->kota) }}"
                                   required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Kecamatan</label>
                            <input name="kecamatan"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('kecamatan', $tenant->kecamatan) }}"
                                   required>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block mb-1 font-medium">Jam Buka</label>
                                <input type="time" name="open_time"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('open_time', $tenant->open_time ? substr($tenant->open_time, 0, 5) : '') }}">
                            </div>
                            <div>
                                <label class="block mb-1 font-medium">Jam Tutup</label>
                                <input type="time" name="close_time"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('close_time', $tenant->close_time ? substr($tenant->close_time, 0, 5) : '') }}">
                            </div>
                        </div>

                        @php
                            $canActivateStudioByVerification = $canActivateStudioByVerification
                                ?? (($tenant->verification_level ?? null) === 'verified'
                                && ($tenant->verification_status ?? null) === 'approved');
                            $canActivateStudioByPayment = $canActivateStudioByPayment ?? false;
                            $canActivateStudio = $canActivateStudioByVerification && $canActivateStudioByPayment;
                        @endphp

                        <div>
                            <label class="block mb-1 font-medium">Status</label>
                            <select name="status"
                                     class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                     required>
                                <option value="active"
                                        @selected(old('status', $tenant->status)==='active')
                                        @disabled(!$canActivateStudio)>
                                    Active
                                </option>
                                <option value="inactive" @selected(old('status', $tenant->status)==='inactive')>Inactive</option>
                            </select>
                            @unless($canActivateStudio)
                                <div class="text-xs text-amber-700 mt-1">
                                    Status Active hanya tersedia setelah studio mencapai Verified Level 2 dan pengajuan Midtrans telah direview dan di terima oleh developer. Buka menu <a href="{{ route('owner.payment-settings.edit') }}" class="underline font-semibold">Payment</a> untuk melihat status pengajuannya.
                                </div>
                            @endunless
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
                                Simpan Informasi Studio
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-2xl space-y-3">
                    <div class="text-sm font-semibold text-gray-900">Deskripsi Studio</div>
                    <textarea name="deskripsi" rows="6"
                              class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Tuliskan deskripsi studio minimal 100 kata."
                              required>{{ old('deskripsi', $tenant->deskripsi) }}</textarea>
                    <div class="text-xs text-gray-500">Minimal 100 kata.</div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2
                                   bg-indigo-600 border border-transparent rounded-md
                                   font-semibold text-xs text-white uppercase tracking-widest
                                   hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                   transition ease-in-out duration-150">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="space-y-4">
                <div class="text-sm font-semibold text-gray-900">Foto Utama Studio Anda</div>
                <div class="text-xs text-gray-500">Minimal 3 foto, maksimal 8 foto. Ukuran maksimal 5MB per foto.</div>

                <form method="POST" action="{{ route('owner.tenant.photos') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <input type="file" name="gallery[]" accept="image/*" multiple
                               class="block w-full text-sm text-gray-600">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2
                                       bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest
                                       hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500
                                       focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                       transition ease-in-out duration-150">
                            Upload
                        </button>
                    </div>
                </form>

                @php
                    $galleryPhotos = $tenant?->photos?->filter(fn ($p) => !$p->is_primary && $p->status == 1) ?? collect();
                @endphp

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse ($galleryPhotos as $photo)
                        <div class="relative w-full overflow-hidden rounded-xl bg-gray-100 aspect-[4/3]">
                            <img src="{{ asset('storage/'.$photo->foto_path) }}"
                                 alt="Foto ruangan dan fasilitas"
                                 class="absolute inset-0 w-full h-full object-cover">
                            <form method="POST"
                                  action="{{ route('owner.tenant.photos.destroy', $photo->idfoto) }}"
                                  class="absolute top-2 right-2"
                                  onsubmit="return confirm('Hapus foto ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-2 py-1 text-xs font-semibold text-white bg-red-500/90 rounded-md hover:bg-red-600">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">
                            Belum ada foto ruangan dan fasilitas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-owner-layout>
