<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\Tenant;
use App\Support\TenantDatabaseManager;
use App\Support\TenantProfileSynchronizer;
use App\Support\TenantVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SetupController extends Controller
{
    public function __construct(
        private readonly TenantDatabaseManager $tenantDbManager,
        private readonly TenantProfileSynchronizer $tenantProfileSynchronizer,
        private readonly TenantVerificationService $tenantVerificationService
    ) {
    }

    public function stepOne()
    {
        $user = Auth::user();

        $tenant = $user->tenants_idTenant
            ? $this->loadTenantWithPhotos($user->tenants_idTenant, false)
            : null;

        return view('owner.setup.step-1', [
            'user' => $user,
            'tenant' => $tenant,
        ]);
    }

    public function storeStepOne(Request $request)
    {
        $user = $request->user();

        $tenant = $user->tenants_idTenant
            ? Tenant::findOrFail($user->tenants_idTenant)
            : null;
        $previousEmail = $tenant?->email;

        $request->validate([
            'nama' => 'required|string|max:45',
            'nama_pemilik' => 'required|string|max:45',
            'email' => [
                'required',
                'email',
                'max:45',
                Rule::unique('tenants', 'email')->ignore($tenant?->idTenant, 'idTenant'),
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'no_telp' => 'required|string|max:45',
            'alamat' => 'nullable|string|max:45',
            'provinsi' => 'required|string|max:100',
            'kota' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'logo' => 'nullable|image|max:2048',
        ]);

        $baseSlug = Str::slug($request->nama);
        $slug = $baseSlug;
        $suffix = 1;
        while (Tenant::where('slug', $slug)
            ->when($tenant, fn($q) => $q->where('idTenant', '!=', $tenant->idTenant))
            ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        if ($tenant) {
            $tenant->update([
                'nama' => $request->nama,
                'slug' => $slug,
                'nama_pemilik' => $request->nama_pemilik,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
                'alamat' => $request->alamat,
                'provinsi' => $request->provinsi,
                'kota' => $request->kota,
                'kecamatan' => $request->kecamatan,
            ]);
        } else {
            $tenant = Tenant::create([
                'nama' => $request->nama,
                'slug' => $slug,
                'nama_pemilik' => $request->nama_pemilik,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
                'alamat' => $request->alamat,
                'provinsi' => $request->provinsi,
                'kota' => $request->kota,
                'kecamatan' => $request->kecamatan,
                'status' => 'inactive',
            ]);
        }

        $shouldSyncUser = false;

        if (!$user->tenants_idTenant) {
            $user->tenants_idTenant = $tenant->idTenant;
            $shouldSyncUser = true;
        }

        if (strcasecmp((string) $user->email, (string) $tenant->email) !== 0) {
            $user->email = $tenant->email;
            $shouldSyncUser = true;
        }

        if ($shouldSyncUser) {
            $user->save();
        }

        $this->tenantDbManager->activateForTenant($tenant);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('tenants', 'public');

            Photo::query()
                ->where('tenants_idTenant', $tenant->idTenant)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            Photo::create([
                'foto_path' => $path,
                'caption' => 'Logo Studio',
                'is_primary' => true,
                'uploaded_at' => now(),
                'status' => 1,
                'tenants_idTenant' => $tenant->idTenant,
            ]);
        }

        $tenant->refresh();

        if ($previousEmail !== null && strcasecmp($previousEmail, (string) $tenant->email) !== 0) {
            $tenant = $this->tenantVerificationService->resetForEmailChange($tenant);
        }

        $tenant = $this->tenantVerificationService->refreshBasicVerification($tenant);
        $this->tenantProfileSynchronizer->sync($tenant);

        return redirect()
            ->route('owner.setup.step2')
            ->with('success', 'Informasi studio berhasil disimpan. Lanjutkan ke langkah berikutnya.');
    }

    public function stepTwo()
    {
        $user = Auth::user();

        if (!$user->tenants_idTenant) {
            return redirect()->route('owner.setup.step1');
        }

        $tenant = Tenant::findOrFail($user->tenants_idTenant);

        return view('owner.setup.step-2', [
            'tenant' => $tenant,
        ]);
    }

    public function storeStepTwo(Request $request)
    {
        $user = $request->user();

        if (!$user->tenants_idTenant) {
            return redirect()->route('owner.setup.step1');
        }

        $request->validate([
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
        ]);

        $tenant = Tenant::findOrFail($user->tenants_idTenant);
        $tenant->update([
            'deskripsi' => $request->deskripsi,
        ]);
        $this->tenantProfileSynchronizer->sync($tenant);
        $this->tenantVerificationService->refreshBasicVerification($tenant);

        return redirect()
            ->route('owner.setup.step3')
            ->with('success', 'Deskripsi studio berhasil disimpan. Lanjutkan ke langkah berikutnya.');
    }

    public function stepThree()
    {
        $user = Auth::user();

        if (!$user->tenants_idTenant) {
            return redirect()->route('owner.setup.step1');
        }

        $tenant = $this->loadTenantWithPhotos($user->tenants_idTenant, true);

        return view('owner.setup.step-3', [
            'tenant' => $tenant,
        ]);
    }

    public function storeStepThree(Request $request)
    {
        $user = $request->user();

        if (!$user->tenants_idTenant) {
            return redirect()->route('owner.setup.step1');
        }

        $tenant = $this->loadTenantWithPhotos($user->tenants_idTenant, true);

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

        return redirect()
            ->route('owner.setup.step3')
            ->with('success', 'Foto ruangan & fasilitas berhasil disimpan.');
    }

    public function welcome()
    {
        $user = Auth::user();

        if (!$user->tenants_idTenant) {
            return redirect()->route('owner.setup.step1');
        }

        $tenant = Tenant::findOrFail($user->tenants_idTenant);

        return view('owner.setup.welcome', [
            'tenant' => $tenant,
        ]);
    }

    private function loadTenantWithPhotos(int $tenantId, bool $withPhotos): Tenant
    {
        $tenant = Tenant::findOrFail($tenantId);

        $this->tenantDbManager->activateForTenant($tenant);

        $primaryPhoto = Photo::query()
            ->where('tenants_idTenant', $tenant->idTenant)
            ->where('is_primary', true)
            ->latest('idfoto')
            ->first();

        $tenant->setRelation('primaryPhoto', $primaryPhoto);

        if ($withPhotos) {
            $photos = Photo::query()
                ->where('tenants_idTenant', $tenant->idTenant)
                ->orderByDesc('is_primary')
                ->orderByDesc('idfoto')
                ->get();

            $tenant->setRelation('photos', $photos);
        }

        return $tenant;
    }
}
