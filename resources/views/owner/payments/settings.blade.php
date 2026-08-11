<x-owner-layout title="Pengajuan Midtrans" subtitle="Kirim data onboarding Midtrans untuk direview developer.">
    <div class="space-y-6">
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-100 text-red-800 rounded">
                <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                <ul class="list-disc ml-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $status = old('status', $submission?->status ?? \App\Models\TenantMidtransSubmission::STATUS_DRAFT);
            $statusLabels = $submissionStatusLabels ?? \App\Models\TenantMidtransSubmission::labels();
            $statusClass = match ($status) {
                \App\Models\TenantMidtransSubmission::STATUS_SUBMITTED => 'bg-blue-100 text-blue-700',
                \App\Models\TenantMidtransSubmission::STATUS_REVISION_NEEDED => 'bg-amber-100 text-amber-700',
                \App\Models\TenantMidtransSubmission::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
                default => 'bg-gray-200 text-gray-700',
            };
            $docsByType = $tenant->verificationDocuments->keyBy('doc_type');
            $dpEnabled = old('dp_enabled', (int) ($paymentAccount?->dp_enabled ?? 1)) == 1;
            $dpPercent = (int) old('dp_percent', (int) ($paymentAccount?->dp_percent ?? 30));
            $cashEnabled = old('cash_enabled', (int) ($paymentAccount?->cash_enabled ?? 0)) == 1;
            $cashInstruction = old('cash_instruction', $paymentAccount?->cash_instruction);
            $paymentConfigured = (bool) ($paymentAccount?->merchant_id ?? false);
            $isConnectionVerified = $isPaymentReady ?? false;
        @endphp

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-6">
            <div class="flex flex-wrap gap-3 items-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                    Provider: MIDTRANS
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                    Status pengajuan: {{ $statusLabels[$status] ?? ucfirst($status) }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $paymentConfigured ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $paymentConfigured ? 'Konfigurasi final tersedia' : 'Konfigurasi final belum tersedia' }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $isConnectionVerified ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                    {{ $isConnectionVerified ? 'Koneksi tervalidasi' : 'Koneksi belum tervalidasi' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="rounded-xl border border-gray-100 p-4">
                    <div class="text-xs text-gray-500">Studio</div>
                    <div class="mt-1 font-semibold text-gray-900">{{ $tenant->nama }}</div>
                    <div class="text-gray-600">{{ $tenant->email }}</div>
                </div>
                <div class="rounded-xl border border-gray-100 p-4">
                    <div class="text-xs text-gray-500">Pengajuan terakhir</div>
                    <div class="mt-1 font-semibold text-gray-900">
                        {{ $submission?->submitted_at?->format('d M Y H:i') ?? '-' }}
                    </div>
                    <div class="text-gray-600">Reviewer: {{ $submission?->reviewer?->name ?? '-' }}</div>
                </div>
                <div class="rounded-xl border border-gray-100 p-4">
                    <div class="text-xs text-gray-500">Review terakhir</div>
                    <div class="mt-1 font-semibold text-gray-900">
                        {{ $submission?->reviewed_at?->format('d M Y H:i') ?? '-' }}
                    </div>
                    <div class="text-gray-600">{{ $submission?->review_notes ?: 'Belum ada catatan review.' }}</div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-gray-700 space-y-2">
                <div class="font-semibold text-gray-900">Cara kerja baru</div>
                <div>1. Owner mengisi data onboarding Midtrans dan mengirim pengajuan.</div>
                <div>2. Developer mereview data, memberi catatan revisi jika perlu, lalu mengatur kredensial akhir Midtrans.</div>
                <div>3. Studio baru bisa aktif setelah konfigurasi Midtrans sudah siap dan koneksi tervalidasi.</div>
            </div>

            <div class="rounded-xl border border-slate-200 p-4">
                <div class="text-sm font-semibold text-gray-900">Ringkasan dokumen verifikasi</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 text-sm">
                    @foreach (\App\Models\TenantVerificationDocument::REQUIRED_DOC_TYPES as $docType)
                        @php
                            $document = $docsByType->get($docType);
                            $docLabel = \App\Models\TenantVerificationDocument::labelForType($docType);
                        @endphp
                        <div class="rounded-lg border border-gray-100 p-3">
                            <div class="text-xs text-gray-500">{{ $docLabel }}</div>
                            <div class="mt-1 font-semibold text-gray-900">
                                {{ $document ? 'Tersedia' : 'Belum ada' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $document?->original_name ?? 'Lengkapi di menu verifikasi tenant.' }}
                            </div>
                        </div>
                    @endforeach
                </div>
                @unless ($hasRequiredDocuments)
                    <div class="mt-3 text-xs text-amber-700">
                        Dokumen belum lengkap. Pengajuan tetap bisa disimpan sebagai draft, tetapi tidak bisa dikirim ke developer sebelum dokumen verifikasi lengkap.
                    </div>
                @endunless
            </div>

            <form method="POST" action="{{ route('owner.payment-settings.update') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Jenis entitas usaha</label>
                        <select name="business_entity_type"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Pilih entitas</option>
                            @foreach (['individu' => 'Individu', 'pt' => 'PT', 'cv' => 'CV', 'yayasan' => 'Yayasan', 'lainnya' => 'Lainnya'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('business_entity_type', $submission?->business_entity_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Nama legal usaha</label>
                        <input name="legal_business_name"
                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('legal_business_name', $submission?->legal_business_name ?? $tenant->nama) }}"
                               placeholder="Sesuai dokumen usaha">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Nama brand</label>
                        <input name="brand_name"
                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('brand_name', $submission?->brand_name ?? $tenant->nama) }}"
                               placeholder="Nama yang dikenal customer">
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Kategori usaha</label>
                        <input name="business_category"
                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('business_category', $submission?->business_category ?? 'studio musik') }}"
                               placeholder="Contoh: studio musik">
                    </div>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Deskripsi singkat usaha</label>
                    <textarea name="business_description_short"
                              rows="4"
                              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Tuliskan ringkasan usaha yang akan dipakai developer saat onboarding Midtrans.">{{ old('business_description_short', $submission?->business_description_short ?? $tenant->deskripsi) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Email bisnis untuk Midtrans</label>
                        <input type="email"
                               name="business_email"
                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('business_email', $submission?->business_email ?? $tenant->email) }}">
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">No. telepon bisnis</label>
                        <input name="business_phone"
                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('business_phone', $submission?->business_phone ?? $tenant->no_telp) }}">
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">URL publik studio</label>
                        <input type="url"
                               name="public_business_url"
                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('public_business_url', $submission?->public_business_url ?? route('studios.show', $tenant->slug)) }}"
                               placeholder="https://...">
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 p-4 space-y-4">
                    <div class="text-sm font-semibold text-gray-900">PIC yang bisa dihubungi developer</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Nama PIC</label>
                            <input name="pic_name"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('pic_name', $submission?->pic_name ?? $tenant->nama_pemilik) }}">
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">No. telepon PIC</label>
                            <input name="pic_phone"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('pic_phone', $submission?->pic_phone ?? $tenant->no_telp) }}">
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Email PIC</label>
                            <input type="email"
                                   name="pic_email"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('pic_email', $submission?->pic_email ?? $tenant->email) }}">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 p-4 space-y-4">
                    <div class="text-sm font-semibold text-gray-900">Data rekening pencairan</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Nama bank</label>
                            <input name="bank_name"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('bank_name', $submission?->bank_name) }}"
                                   placeholder="Contoh: BCA">
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Nomor rekening</label>
                            <input name="bank_account_number"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('bank_account_number', $submission?->bank_account_number) }}"
                                   placeholder="Masukkan nomor rekening">
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Nama pemilik rekening</label>
                            <input name="bank_account_holder_name"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('bank_account_holder_name', $submission?->bank_account_holder_name ?? $tenant->nama_pemilik) }}">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Catatan pengajuan</label>
                    <textarea name="submission_notes"
                              rows="4"
                              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Contoh: akun Midtrans belum ada, mohon dibantu onboarding dari awal.">{{ old('submission_notes', $submission?->submission_notes) }}</textarea>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-white border border-indigo-300 rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Simpan Draft
                    </button>
                    <button type="submit"
                            formaction="{{ route('owner.payment-settings.submit') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Kirim ke Developer
                    </button>
                </div>
            </form>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-6">
            <div class="flex flex-wrap gap-3 items-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $dpEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                    DP {{ $dpEnabled ? 'Aktif' : 'Nonaktif' }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $cashEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                    Cash {{ $cashEnabled ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-gray-700 space-y-1">
                <div class="font-semibold text-gray-900">Preferensi pembayaran studio</div>
                <div>Pengaturan DP dan cash dikelola langsung oleh owner.</div>
                <div>Pengaturan ini tidak ikut dikirim ke developer dan tidak memerlukan approval.</div>
            </div>

            <form method="POST" action="{{ route('owner.payment-settings.preferences') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3 rounded-xl border border-gray-100 p-4">
                        <label class="block font-medium">Preferensi DP</label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="dp_enabled" value="0">
                            <input type="checkbox" name="dp_enabled" value="1" @checked($dpEnabled) class="rounded border-gray-300 text-indigo-600">
                            Izinkan pembayaran DP
                        </label>
                        <div>
                            <label class="block mb-1 text-sm font-medium">Persentase DP</label>
                            <input name="dp_percent"
                                   type="number"
                                   min="1"
                                   max="90"
                                   step="1"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ $dpPercent }}">
                        </div>
                    </div>

                    <div class="space-y-3 rounded-xl border border-gray-100 p-4">
                        <label class="block font-medium">Preferensi cash</label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="cash_enabled" value="0">
                            <input type="checkbox" name="cash_enabled" value="1" @checked($cashEnabled) class="rounded border-gray-300 text-indigo-600">
                            Izinkan pembayaran cash di studio
                        </label>
                        <div>
                            <label class="block mb-1 text-sm font-medium">Instruksi cash</label>
                            <textarea name="cash_instruction"
                                      rows="4"
                                      class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Tampilkan instruksi ini ke customer saat memilih cash.">{{ $cashInstruction }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Simpan Preferensi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-owner-layout>
