<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\Tenant;
use App\Support\TenantMidtransService;
use App\Support\TenantProfileSynchronizer;
use App\Support\TenantVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantProfileSynchronizer $tenantProfileSynchronizer,
        private readonly TenantVerificationService $tenantVerificationService,
        private readonly TenantMidtransService $tenantMidtransService
    ) {
    }

    public function edit()
    {
        $tenantId = Auth::user()->tenants_idTenant;
        if (!$tenantId) {
            return redirect()
                ->route('owner.setup.step1')
                ->with('error', 'Lengkapi informasi studio terlebih dahulu.');
        }

        $tenant = Tenant::with(['primaryPhoto'])->findOrFail($tenantId);
        $canActivateStudioByVerification = $this->tenantVerificationService->canActivate($tenant);
        $canActivateStudioByPayment = $this->tenantMidtransService->hasReadyActivationConfiguration($tenant);

        return view('owner.tenant.edit', compact(
            'tenant',
            'canActivateStudioByVerification',
            'canActivateStudioByPayment'
        ));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $owner */
        $owner = Auth::user();
        $tenantId = $owner->tenants_idTenant;
        if (!$tenantId) {
            return back()->withErrors(['tenant' => 'Tenant tidak ditemukan.']);
        }

        $tenant = Tenant::with(['primaryPhoto', 'photos'])->findOrFail($tenantId);
        $previousEmail = $tenant->email;

        $request->validate([
            'nama' => 'required|string|max:45',
            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tenants', 'slug')->ignore($tenant->idTenant, 'idTenant'),
            ],
            'deskripsi' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $words = preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
                    if (!$words || count($words) < 100) {
                        $fail('Deskripsi minimal 100 kata.');
                    }
                },
            ],
            'nama_pemilik' => 'required|string|max:45',
            'email' => [
                'required',
                'email',
                'max:45',
                Rule::unique('tenants', 'email')->ignore($tenant->idTenant, 'idTenant'),
                Rule::unique('users', 'email')->ignore($owner->id),
            ],
            'no_telp' => 'required|string|max:45',
            'alamat' => 'nullable|string|max:45',
            'provinsi' => 'required|string|max:100',
            'kota' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i|after:open_time',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->status === 'active') {
            if (!$this->tenantVerificationService->canActivate($tenant)) {
                return back()
                    ->withErrors([
                        'status' => 'Status Active hanya tersedia untuk studio yang sudah Verified Level 2.',
                    ])
                    ->withInput();
            }

            if (!$this->tenantMidtransService->hasReadyActivationConfiguration($tenant)) {
                return back()
                    ->withErrors([
                        'status' => 'Status Active hanya tersedia setelah pengajuan Midtrans direview developer dan konfigurasi payment tenant sudah aktif serta lulus test koneksi.',
                    ])
                    ->withInput();
            }
        }

        $tenant->update([
            'nama' => $request->nama,
            'slug' => $request->slug,
            'deskripsi' => $request->deskripsi,
            'nama_pemilik' => $request->nama_pemilik,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat,
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'status' => $request->status,
        ]);

        if (strcasecmp((string) $owner->email, (string) $tenant->email) !== 0) {
            $owner->email = $tenant->email;
            $owner->save();
        }

        if (strcasecmp($previousEmail, (string) $tenant->email) !== 0) {
            $tenant = $this->tenantVerificationService->resetForEmailChange($tenant);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('tenants', 'public');

            $tenant->photos()->where('is_primary', true)->update(['is_primary' => false]);

            Photo::create([
                'foto_path' => $path,
                'caption' => 'Logo Studio',
                'is_primary' => true,
                'uploaded_at' => now(),
                'status' => 1,
                'tenants_idTenant' => $tenant->idTenant,
            ]);
        }

        $tenant = $this->tenantVerificationService->refreshBasicVerification($tenant);
        $this->tenantProfileSynchronizer->sync($tenant);

        return back()->with('success', 'Profil studio berhasil diperbarui.');
    }

    public function updatePhotos(Request $request)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        if (!$tenantId) {
            return back()->withErrors(['tenant' => 'Tenant tidak ditemukan.']);
        }

        $tenant = Tenant::with('photos')->findOrFail($tenantId);

        $request->validate([
            'gallery' => 'required|array',
            'gallery.*' => 'image|max:5120',
        ]);

        $files = $request->file('gallery', []);
        $incomingCount = count($files);
        $existingCount = $tenant->photos->where('is_primary', false)->count();
        $totalCount = $existingCount + $incomingCount;

        if ($totalCount < 3) {
            return back()->withErrors([
                'gallery' => 'Total foto ruangan & fasilitas minimal 3.',
            ]);
        }

        if ($totalCount > 8) {
            return back()->withErrors([
                'gallery' => 'Total foto ruangan & fasilitas maksimal 8.',
            ]);
        }

        foreach ($files as $file) {
            $path = $file->store('tenants', 'public');

            Photo::create([
                'foto_path' => $path,
                'caption' => 'Foto ruangan & fasilitas',
                'is_primary' => false,
                'uploaded_at' => now(),
                'status' => 1,
                'tenants_idTenant' => $tenant->idTenant,
            ]);
        }

        $this->tenantVerificationService->refreshBasicVerification($tenant);

        return back()->with('success', 'Foto ruangan & fasilitas berhasil ditambahkan.');
    }

    public function destroyPhoto($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        if (!$tenantId) {
            return back()->withErrors(['tenant' => 'Tenant tidak ditemukan.']);
        }

        $photo = Photo::where('tenants_idTenant', $tenantId)
            ->where('idfoto', $id)
            ->where('is_primary', false)
            ->firstOrFail();

        $photo->delete();

        $tenant = Tenant::query()->findOrFail($tenantId);
        $this->tenantVerificationService->refreshBasicVerification($tenant);

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
