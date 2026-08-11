<x-owner-layout title="Verifikasi Studio" subtitle="Hybrid verification: basic (otomatis) + manual review developer.">
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
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="p-4 rounded-2xl bg-white shadow-sm border border-gray-100">
                <div class="text-xs uppercase tracking-widest text-gray-500">Level verifikasi</div>
                <div class="mt-2 text-lg font-semibold text-gray-900">
                    {{ str_replace('_', ' ', $tenant->verification_level ?? 'none') }}
                </div>
            </div>
            <div class="p-4 rounded-2xl bg-white shadow-sm border border-gray-100">
                <div class="text-xs uppercase tracking-widest text-gray-500">Status manual review</div>
                <div class="mt-2 text-lg font-semibold text-gray-900">
                    {{ $tenant->verification_status ?? 'draft' }}
                </div>
            </div>
            <div class="p-4 rounded-2xl bg-white shadow-sm border border-gray-100">
                <div class="text-xs uppercase tracking-widest text-gray-500">Email studio</div>
                <div class="mt-2 text-lg font-semibold text-gray-900 break-all">
                    {{ $tenant->email ?? '-' }}
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 bg-white shadow-sm rounded-2xl border border-gray-100">
            <div class="text-sm font-semibold text-gray-900">Level 1 - Basic Verified (otomatis)</div>
            <div class="text-xs text-gray-500 mt-1">
                Syarat: OTP email studio valid + profil studio lengkap + foto logo + minimal 3 foto galeri.
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <div class="space-y-3">
                    <div class="text-sm font-semibold text-gray-700">Status checklist</div>
                    <div class="text-sm">
                        @if ($checklist['is_complete'])
                            <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold">
                                Lengkap
                            </span>
                        @else
                            <span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 font-semibold">
                                Belum lengkap
                            </span>
                        @endif
                    </div>

                    <ul class="list-disc ml-5 text-sm text-gray-700 space-y-1">
                        @forelse ($checklist['missing_items'] as $item)
                            <li>{{ $item }}</li>
                        @empty
                            <li>Semua persyaratan basic sudah terpenuhi.</li>
                        @endforelse
                    </ul>

                    <div class="text-xs text-gray-500">
                        Kata deskripsi: {{ $checklist['description_words'] }} kata
                        | Galeri: {{ $checklist['gallery_count'] }} foto
                    </div>
                </div>

                <div class="space-y-4">
                    <form method="POST" action="{{ route('owner.verification.email-otp.send') }}" class="space-y-3">
                        @csrf
                        <div class="text-sm font-semibold text-gray-700">Kirim OTP ke email studio</div>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Kirim OTP Email
                        </button>
                        @if ($latestOtp)
                            <div class="text-xs text-gray-500">
                                OTP terakhir dikirim:
                                {{ $latestOtp->created_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        @endif
                    </form>

                    <form method="POST" action="{{ route('owner.verification.email-otp.verify') }}" class="space-y-3">
                        @csrf
                        <div class="text-sm font-semibold text-gray-700">Verifikasi OTP</div>
                        <input type="text"
                               name="otp_code"
                               maxlength="6"
                               inputmode="numeric"
                               pattern="[0-9]{6}"
                               placeholder="Masukkan 6 digit OTP"
                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                            Verifikasi OTP
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 bg-white shadow-sm rounded-2xl border border-gray-100">
            <div class="text-sm font-semibold text-gray-900">Level 2 - Verified </div>
            <div class="text-xs text-gray-500 mt-1">
                Upload 4 dokumen wajib, lalu kirim untuk di review olehh Studiokita.
            </div>

            @php
                $docLabels = [
                    'owner_ktp' => 'KTP Pemilik',
                    'owner_selfie_ktp' => 'Selfie dengan KTP',
                    'business_address_proof' => 'Bukti Alamat Usaha (tagihan/sewa)',
                    'bank_account_proof' => 'Rekening Bank Penerima',
                ];
            @endphp

            <form method="POST"
                  action="{{ route('owner.verification.manual-submit') }}"
                  enctype="multipart/form-data"
                  class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                @foreach($requiredDocTypes as $docType)
                    @php $existing = $documents->get($docType); @endphp
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $docLabels[$docType] ?? $docType }}
                        </label>
                        <input type="file"
                               name="{{ $docType }}"
                               accept=".jpg,.jpeg,.png,.pdf"
                               class="block w-full text-sm text-gray-600">
                        <div class="text-xs text-gray-500">
                            @if ($existing)
                                Sudah terunggah:
                                <a href="{{ route('owner.verification.documents.download', $existing->id) }}"
                                   class="text-indigo-600 underline">
                                    {{ $existing->original_name ?? 'Lihat dokumen' }}
                                </a>
                                ({{ $existing->status }})
                            @else
                                Belum ada dokumen.
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="md:col-span-2 flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Kirim Dokumen untuk Review
                    </button>
                    @if ($tenant->verification_notes)
                        <div class="text-sm text-amber-700 bg-amber-50 px-3 py-2 rounded-md border border-amber-100">
                            Catatan reviewer: {{ $tenant->verification_notes }}
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-owner-layout>

