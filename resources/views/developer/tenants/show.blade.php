<x-app-layout :hide-nav="true">
    @php
        $activeClass = 'flex gap-4 px-4 py-3 w-full text-white bg-indigo-500 rounded-2xl';
        $inactiveClass = 'flex gap-4 items-center py-3 pl-4 w-full rounded-2xl text-neutral-500 hover:bg-indigo-50';
        $user = auth()->user();
        $primaryPhoto = $tenant->primaryPhoto;
        $gallery = $tenant->photos->where('is_primary', false);
        $isSelfTenant = (int) ($user->tenants_idTenant ?? 0) === (int) $tenant->idTenant;
        $paymentSubmission = $tenant->midtransSubmission;
        $paymentStatus = $paymentSubmission?->status ?? \App\Models\TenantMidtransSubmission::STATUS_DRAFT;
        $paymentStatusClass = match ($paymentStatus) {
            \App\Models\TenantMidtransSubmission::STATUS_SUBMITTED => 'bg-blue-100 text-blue-700',
            \App\Models\TenantMidtransSubmission::STATUS_REVISION_NEEDED => 'bg-amber-100 text-amber-700',
            \App\Models\TenantMidtransSubmission::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
            default => 'bg-gray-200 text-gray-700',
        };
        $paymentStatusLabel = $paymentSubmissionStatusLabels[$paymentStatus] ?? ucfirst($paymentStatus);
    @endphp

    <div class="px-4 py-6 md:px-8">
        <div class="overflow-hidden rounded-3xl bg-slate-50">
            <div class="flex flex-col gap-5 lg:flex-row">
                <aside class="w-full lg:w-[19%]">
                    <div class="flex flex-col items-start px-6 pt-4 pb-6 mx-auto w-full bg-white rounded-3xl">
                        <div class="flex gap-2 text-3xl font-semibold text-black">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 3a4 4 0 0 1 4 4c0 2.2-1.8 4-4 4s-4-1.8-4-4a4 4 0 0 1 4-4z"></path>
                                    <path d="M4 20a8 8 0 0 1 16 0"></path>
                                </svg>
                            </div>
                            <div class="my-auto">
                                Developer
                            </div>
                        </div>

                        <nav class="mt-14 w-full text-base font-medium space-y-3">
                            <a href="{{ route('developer.dashboard') }}"
                               class="{{ request()->routeIs('developer.dashboard') ? $activeClass : $inactiveClass }}">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 4h7v7H4z"></path>
                                    <path d="M13 4h7v7h-7z"></path>
                                    <path d="M4 13h7v7H4z"></path>
                                    <path d="M13 13h7v7h-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </nav>

                        <div class="flex items-center justify-between mt-16 w-full">
                            <div class="flex gap-3 items-center">
                                <div class="flex w-10 h-10 rounded-lg bg-neutral-200 shadow-[0px_2px_20px_rgba(0,0,0,0.15)]"></div>
                                <div>
                                    <div class="text-sm font-semibold text-zinc-900">{{ $user->name }}</div>
                                    <div class="text-xs text-stone-500">{{ $user->role }}</div>
                                </div>
                            </div>
                        </div>

                        <form method="POST"
                              action="{{ route('logout') }}"
                              class="w-full mt-4"
                              onsubmit="return confirm('Apakah anda yakin untuk logout?')">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 rounded-2xl hover:bg-red-100">
                                Log Out
                            </button>
                        </form>
                    </div>
                </aside>

                <main class="w-full lg:w-[81%]">
                    <div class="py-6 mx-auto w-full bg-violet-50 rounded-[32px]">
                        <div class="flex flex-wrap gap-6 justify-between items-center px-6 lg:px-8">
                            <div>
                                <div class="text-xs uppercase tracking-widest text-gray-500">Detail tenant</div>
                                <div class="text-2xl font-semibold text-gray-900">{{ $tenant->nama }}</div>
                                <div class="text-sm text-gray-600 mt-1">{{ $tenant->email }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if (!$isSelfTenant)
                                    <form method="POST"
                                          action="{{ route('developer.tenants.destroy', $tenant->slug) }}"
                                          onsubmit="return confirm('Hapus studio {{ $tenant->nama }} secara permanen? Aksi ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-6 py-2.5 rounded-full bg-rose-600 text-white font-semibold shadow hover:bg-rose-700">
                                            Hapus Studio
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('developer.dashboard') }}" class="px-6 py-2.5 rounded-full bg-indigo-500 text-white font-semibold shadow">
                                    Kembali
                                </a>
                            </div>
                        </div>

                        <div class="mx-6 lg:mx-8 mt-8 space-y-6">
                            @if (session('success'))
                                <div class="p-3 bg-emerald-100 text-emerald-800 rounded-xl">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('test_success'))
                                <div class="p-3 bg-blue-100 text-blue-800 rounded-xl">
                                    {{ session('test_success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="p-4 bg-rose-100 text-rose-800 rounded-xl">
                                    <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                                    <ul class="list-disc ml-5 space-y-1 text-sm">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="flex flex-col md:flex-row gap-4 items-start">
                                <div class="overflow-hidden bg-white rounded-2xl p-4 flex items-center justify-center w-40 h-40 flex-shrink-0">
                                    @if ($primaryPhoto)
                                        <img src="{{ asset('storage/'.$primaryPhoto->foto_path) }}"
                                             alt="Logo Studio"
                                             class="w-20 h-20 object-contain rounded-xl">
                                    @else
                                        <div class="w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 text-xs">
                                            Logo
                                        </div>
                                    @endif
                                </div>
                                <div class="overflow-hidden bg-white rounded-2xl p-4 w-full h-40 flex flex-col justify-center">
                                    <div class="grid gap-4 md:grid-cols-2 text-sm">
                                        <div>
                                            <div class="text-xs uppercase tracking-widest text-gray-500">Owner</div>
                                            <div class="mt-2 text-sm font-semibold text-gray-900">{{ $tenant->nama_pemilik ?? '-' }}</div>
                                            <div class="text-sm text-gray-600">{{ $tenant->email }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-widest text-gray-500">Kontak</div>
                                            <div class="mt-2 text-sm font-semibold text-gray-900">{{ $tenant->no_telp ?? '-' }}</div>
                                            <div class="text-sm text-gray-600">{{ $tenant->alamat ?? '-' }}</div>
                                            <div class="text-sm text-gray-600">
                                                {{ $tenant->kecamatan ?? '-' }}, {{ $tenant->kota ?? '-' }}, {{ $tenant->provinsi ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-4">
                                <div class="overflow-hidden bg-white rounded-2xl p-5">
                                    <div class="text-xs font-medium text-gray-500">Ruangan</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['rooms'] }}</div>
                                </div>
                                <div class="overflow-hidden bg-white rounded-2xl p-5">
                                    <div class="text-xs font-medium text-gray-500">Layanan</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['services'] }}</div>
                                </div>
                                <div class="overflow-hidden bg-white rounded-2xl p-5">
                                    <div class="text-xs font-medium text-gray-500">Fasilitas</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['facilities'] }}</div>
                                </div>
                                <div class="overflow-hidden bg-white rounded-2xl p-5">
                                    <div class="text-xs font-medium text-gray-500">Foto galeri</div>
                                    <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['photos'] }}</div>
                                </div>
                            </div>

                            <div class="overflow-hidden bg-white rounded-2xl p-5 space-y-4">
                                <div class="text-xs uppercase tracking-widest text-gray-500">Verifikasi Studio</div>
                                <div class="grid gap-4 md:grid-cols-3 text-sm">
                                    <div>
                                        <div class="text-xs text-gray-500">Level</div>
                                        <div class="mt-1 font-semibold text-gray-900">
                                            {{ str_replace('_', ' ', $tenant->verification_level ?? 'none') }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Status review</div>
                                        <div class="mt-1 font-semibold text-gray-900">
                                            {{ $tenant->verification_status ?? 'draft' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">OTP email</div>
                                        <div class="mt-1 font-semibold text-gray-900">
                                            {{ $tenant->email_otp_verified_at ? 'Verified' : 'Belum verified' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-sm text-gray-700">
                                    @if ($tenant->verification_notes)
                                        Catatan reviewer terakhir: {{ $tenant->verification_notes }}
                                    @else
                                        Belum ada catatan review.
                                    @endif
                                </div>

                                <div class="grid gap-3 md:grid-cols-2">
                                    @foreach ($requiredDocTypes as $docType)
                                        @php
                                            $document = $tenant->verificationDocuments->firstWhere('doc_type', $docType);
                                            $docLabel = \App\Models\TenantVerificationDocument::labelForType($docType);
                                        @endphp
                                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="text-xs text-gray-500">{{ $docLabel }}</div>
                                            <div class="mt-1 text-sm text-gray-900">
                                                @if ($document)
                                                    <a href="{{ route('developer.verification.documents.download', $document->id) }}"
                                                       class="text-indigo-600 underline">
                                                        {{ $document->original_name ?? 'Lihat dokumen' }}
                                                    </a>
                                                    <span class="text-xs text-gray-500">({{ $document->status }})</span>
                                                @else
                                                    <span class="text-gray-500">Belum diunggah</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if (($tenant->verification_status ?? 'draft') === 'pending')
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <form method="POST"
                                              action="{{ route('developer.tenants.verification.approve', $tenant->slug) }}"
                                              class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 space-y-3">
                                            @csrf
                                            @method('PATCH')
                                            <div class="text-sm font-semibold text-emerald-800">Setujui verifikasi</div>
                                            <textarea name="verification_notes"
                                                      rows="3"
                                                      class="w-full rounded-md border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500"
                                                      placeholder="Opsional: catatan persetujuan"></textarea>
                                            <button type="submit"
                                                    class="px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"
                                                    onclick="return confirm('Setujui verifikasi studio ini?')">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('developer.tenants.verification.reject', $tenant->slug) }}"
                                              class="p-4 rounded-xl bg-rose-50 border border-rose-100 space-y-3">
                                            @csrf
                                            @method('PATCH')
                                            <div class="text-sm font-semibold text-rose-800">Tolak verifikasi</div>
                                            <textarea name="verification_notes"
                                                      rows="3"
                                                      required
                                                      class="w-full rounded-md border-rose-200 focus:border-rose-500 focus:ring-rose-500"
                                                      placeholder="Wajib isi alasan penolakan"></textarea>
                                            <button type="submit"
                                                    class="px-4 py-2 rounded-md bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700"
                                                    onclick="return confirm('Tolak verifikasi studio ini?')">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-600">
                                        Aksi approve/reject hanya tersedia saat status verifikasi <strong>pending</strong>.
                                    </div>
                                @endif

                                @if ($tenant->verificationLogs->count())
                                    <div>
                                        <div class="text-xs uppercase tracking-widest text-gray-500 mb-2">Audit log terbaru</div>
                                        <div class="space-y-2">
                                            @foreach ($tenant->verificationLogs as $log)
                                                <div class="text-xs text-gray-600 p-2 rounded bg-slate-50 border border-slate-100">
                                                    <span class="font-semibold text-gray-800">{{ $log->action }}</span>
                                                    - {{ $log->created_at?->format('d M Y H:i') }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="overflow-hidden bg-white rounded-2xl p-5 space-y-5">
                                <div class="flex flex-wrap gap-3 items-center">
                                    <div class="text-xs uppercase tracking-widest text-gray-500">Payment Midtrans</div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $paymentStatusClass }}">
                                        {{ $paymentStatusLabel }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $paymentConfigActive ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                                        {{ $paymentConfigActive ? 'Konfigurasi aktif' : 'Konfigurasi belum aktif' }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $paymentConfigReady ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $paymentConfigReady ? 'Test koneksi lolos' : 'Test koneksi belum lolos' }}
                                    </span>
                                </div>

                                @if ($paymentSubmission)
                                    <div class="grid gap-4 md:grid-cols-3 text-sm">
                                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                            <div class="text-xs text-gray-500">Entitas usaha</div>
                                            <div class="mt-1 font-semibold text-gray-900">{{ strtoupper($paymentSubmission->business_entity_type ?? '-') }}</div>
                                            <div class="text-gray-600">{{ $paymentSubmission->legal_business_name ?: '-' }}</div>
                                        </div>
                                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                            <div class="text-xs text-gray-500">Kontak bisnis</div>
                                            <div class="mt-1 font-semibold text-gray-900">{{ $paymentSubmission->business_email ?: '-' }}</div>
                                            <div class="text-gray-600">{{ $paymentSubmission->business_phone ?: '-' }}</div>
                                        </div>
                                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                            <div class="text-xs text-gray-500">Review terakhir</div>
                                            <div class="mt-1 font-semibold text-gray-900">{{ $paymentSubmission->reviewed_at?->format('d M Y H:i') ?? '-' }}</div>
                                            <div class="text-gray-600">{{ $paymentSubmission->reviewer?->name ?? '-' }}</div>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2 text-sm">
                                        <div class="rounded-xl border border-slate-100 p-4 space-y-2">
                                            <div>
                                                <div class="text-xs text-gray-500">URL publik</div>
                                                <div class="mt-1 font-semibold text-gray-900 break-all">
                                                    {{ $paymentSubmission->public_business_url ?: '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">PIC</div>
                                                <div class="mt-1 font-semibold text-gray-900">{{ $paymentSubmission->pic_name ?: '-' }}</div>
                                                <div class="text-gray-600">{{ $paymentSubmission->pic_phone ?: '-' }} | {{ $paymentSubmission->pic_email ?: '-' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">Rekening</div>
                                                <div class="mt-1 font-semibold text-gray-900">{{ $paymentSubmission->bank_name ?: '-' }}</div>
                                                <div class="text-gray-600">{{ $paymentSubmission->bank_account_holder_name ?: '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-slate-100 p-4 space-y-2">
                                            <div>
                                                <div class="text-xs text-gray-500">Catatan owner</div>
                                                <div class="mt-1 text-gray-900">{{ $paymentSubmission->submission_notes ?: '-' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">Catatan developer</div>
                                                <div class="mt-1 text-gray-900">{{ $paymentSubmission->review_notes ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 md:grid-cols-2">
                                        <form method="POST"
                                              action="{{ route('developer.tenants.payment-submission.review', $tenant->slug) }}"
                                              class="p-4 rounded-xl bg-amber-50 border border-amber-100 space-y-3">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="revision_needed">
                                            <div class="text-sm font-semibold text-amber-800">Minta revisi data owner</div>
                                            <textarea name="review_notes"
                                                      rows="4"
                                                      required
                                                      class="w-full rounded-md border-amber-200 focus:border-amber-500 focus:ring-amber-500"
                                                      placeholder="Jelaskan data yang perlu diperbaiki owner."></textarea>
                                            <button type="submit"
                                                    class="px-4 py-2 rounded-md bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700"
                                                    onclick="return confirm('Kembalikan pengajuan Midtrans untuk direvisi?')">
                                                Minta Revisi
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('developer.tenants.payment-submission.review', $tenant->slug) }}"
                                              class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 space-y-3">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <div class="text-sm font-semibold text-emerald-800">Setujui pengajuan</div>
                                            <textarea name="review_notes"
                                                      rows="4"
                                                      class="w-full rounded-md border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500"
                                                      placeholder="Opsional: catatan approval."></textarea>
                                            <div class="text-xs text-emerald-700">
                                                Approval hanya bisa dilakukan jika konfigurasi Midtrans tenant sudah aktif dan test koneksi berhasil.
                                            </div>
                                            <button type="submit"
                                                    class="px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"
                                                    onclick="return confirm('Setujui pengajuan Midtrans tenant ini?')">
                                                Approve
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-600">
                                        Owner belum mengirim pengajuan Midtrans. Developer belum bisa melakukan review status.
                                    </div>
                                @endif

                                <form method="POST"
                                      action="{{ route('developer.tenants.payment-settings.update', $tenant->slug) }}"
                                      class="space-y-5 border-t border-slate-100 pt-5">
                                    @csrf
                                    @method('PUT')

                                    <div class="text-sm font-semibold text-gray-900">Konfigurasi akhir oleh developer</div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block mb-1 font-medium">Merchant ID</label>
                                            <input name="merchant_id"
                                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                   value="{{ old('merchant_id', $paymentAccount?->merchant_id) }}"
                                                   placeholder="Contoh: G123456789">
                                        </div>
                                        <div class="space-y-3">
                                            <label class="block font-medium">Mode & Status</label>
                                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                                <input type="hidden" name="is_production" value="0">
                                                <input type="checkbox" name="is_production" value="1" @checked(old('is_production', (int) ($paymentAccount?->is_production ?? 0)) == 1) class="rounded border-gray-300 text-indigo-600">
                                                Gunakan mode Production
                                            </label>
                                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', (int) ($paymentAccount?->is_active ?? 0)) == 1) class="rounded border-gray-300 text-indigo-600">
                                                Aktifkan Midtrans tenant ini
                                            </label>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block mb-1 font-medium">Client Key Midtrans</label>
                                            <input name="midtrans_client_key"
                                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                   value="{{ old('midtrans_client_key') }}"
                                                   placeholder="SB-Mid-client-... atau Mid-client-..."
                                                   autocomplete="off">
                                        </div>
                                        <div>
                                            <label class="block mb-1 font-medium">Server Key Midtrans</label>
                                            <input type="password"
                                                   name="midtrans_server_key"
                                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                   value=""
                                                   placeholder="SB-Mid-server-... atau Mid-server-..."
                                                   autocomplete="new-password">
                                        </div>
                                    </div>

                                    @if ($paymentAccount?->midtrans_last_tested_at)
                                        <div class="text-xs text-gray-500 -mt-2">
                                            Test koneksi terakhir: {{ $paymentAccount->midtrans_last_tested_at->format('d M Y H:i') }}
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-3">
                                        <button type="submit"
                                                formaction="{{ route('developer.tenants.payment-settings.test', $tenant->slug) }}"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-indigo-300 rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Test Koneksi
                                        </button>
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Simpan Konfigurasi
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="overflow-hidden bg-white rounded-2xl p-5">
                                <div class="text-xs uppercase tracking-widest text-gray-500">Deskripsi</div>
                                <div class="mt-3 text-sm text-gray-700 leading-relaxed">
                                    {{ $tenant->deskripsi ?: 'Belum ada deskripsi.' }}
                                </div>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2">
                                <div class="overflow-hidden bg-white rounded-2xl p-5">
                                    <div class="text-xs uppercase tracking-widest text-gray-500">Layanan</div>
                                    <div class="mt-3 space-y-2">
                                        @forelse ($tenant->services as $service)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="font-semibold text-gray-900">{{ $service->nama_service }}</span>
                                                <span class="text-gray-600">
                                                    WD Rp {{ number_format($service->weekday_price ?? 0, 0, ',', '.') }}
                                                    | WE Rp {{ number_format($service->weekend_price ?? 0, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="text-sm text-gray-500">Belum ada layanan.</div>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="overflow-hidden bg-white rounded-2xl p-5">
                                    <div class="text-xs uppercase tracking-widest text-gray-500">Fasilitas</div>
                                    <div class="mt-3 space-y-2">
                                        @forelse ($tenant->facilities as $facility)
                                            <div class="text-sm font-semibold text-gray-900">{{ $facility->nama_fasilitas }}</div>
                                        @empty
                                            <div class="text-sm text-gray-500">Belum ada fasilitas.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-hidden bg-white rounded-2xl p-5">
                                <div class="text-xs uppercase tracking-widest text-gray-500">Galeri ruangan</div>
                                @if ($gallery->count())
                                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                        @foreach ($gallery as $photo)
                                            <img src="{{ asset('storage/'.$photo->foto_path) }}"
                                                 alt="Foto ruangan"
                                                 class="w-full aspect-[4/3] object-cover rounded-xl">
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-3 text-sm text-gray-500">Belum ada foto galeri.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
