<?php

namespace App\Support;

use App\Models\Photo;
use App\Models\Tenant;
use App\Models\TenantEmailOtp;
use App\Models\TenantVerificationDocument;
use App\Models\TenantVerificationLog;
use App\Models\User;
use Illuminate\Support\Str;

class TenantVerificationService
{
    public function __construct(
        private readonly TenantDatabaseManager $tenantDbManager
    ) {
    }

    public function getBasicChecklist(Tenant $tenant): array
    {
        $tenant = Tenant::query()->findOrFail($tenant->idTenant);

        $missing = [];
        $requiredTextFields = [
            'nama' => 'Nama studio',
            'nama_pemilik' => 'Nama pemilik',
            'email' => 'Email studio',
            'no_telp' => 'No telp studio',
            'alamat' => 'Alamat studio',
            'provinsi' => 'Provinsi',
            'kota' => 'Kota',
            'kecamatan' => 'Kecamatan',
        ];

        foreach ($requiredTextFields as $field => $label) {
            if (trim((string) $tenant->{$field}) === '') {
                $missing[] = $label.' belum diisi.';
            }
        }

        if (!$tenant->open_time || !$tenant->close_time) {
            $missing[] = 'Jam operasional studio belum lengkap.';
        }

        $descriptionWords = str_word_count(strip_tags((string) $tenant->deskripsi));
        if ($descriptionWords < 100) {
            $missing[] = 'Deskripsi studio minimal 100 kata.';
        }

        $hasPrimaryLogo = false;
        $galleryCount = 0;

        try {
            $this->tenantDbManager->runForTenant($tenant, function () use (&$hasPrimaryLogo, &$galleryCount, $tenant) {
                $hasPrimaryLogo = Photo::query()
                    ->where('tenants_idTenant', $tenant->idTenant)
                    ->where('is_primary', true)
                    ->where('status', 1)
                    ->exists();

                $galleryCount = Photo::query()
                    ->where('tenants_idTenant', $tenant->idTenant)
                    ->where('is_primary', false)
                    ->where('status', 1)
                    ->count();
            });
        } catch (\Throwable $e) {
            $missing[] = 'Gagal membaca data foto studio.';
        }

        if (!$hasPrimaryLogo) {
            $missing[] = 'Logo studio belum diunggah.';
        }

        if ($galleryCount < 3) {
            $missing[] = 'Foto ruangan/fasilitas minimal 3 foto.';
        }

        $otpVerified = (bool) $tenant->email_otp_verified_at;
        if (!$otpVerified) {
            $missing[] = 'OTP email studio belum diverifikasi.';
        }

        return [
            'otp_verified' => $otpVerified,
            'description_words' => $descriptionWords,
            'has_primary_logo' => $hasPrimaryLogo,
            'gallery_count' => $galleryCount,
            'is_complete' => count($missing) === 0,
            'missing_items' => $missing,
        ];
    }

    public function canActivate(Tenant $tenant): bool
    {
        return $tenant->verification_level === 'verified'
            && $tenant->verification_status === 'approved';
    }

    public function refreshBasicVerification(Tenant $tenant): Tenant
    {
        $tenant = Tenant::query()->findOrFail($tenant->idTenant);

        if ($tenant->verification_level === 'verified') {
            return $tenant;
        }

        $checklist = $this->getBasicChecklist($tenant);

        if ($checklist['is_complete']) {
            $tenant->verification_level = 'basic_verified';
            $tenant->basic_verified_at = $tenant->basic_verified_at ?: now();

            if (!in_array($tenant->verification_status, ['pending', 'approved'], true)) {
                $tenant->verification_status = 'draft';
            }
        } else {
            $tenant->verification_level = 'none';
            $tenant->basic_verified_at = null;

            if ($tenant->verification_status !== 'pending') {
                $tenant->verification_status = 'draft';
            }
        }

        $tenant->save();

        return $tenant;
    }

    public function resetForEmailChange(Tenant $tenant): Tenant
    {
        $tenant = Tenant::query()->findOrFail($tenant->idTenant);

        $tenant->forceFill([
            'email_otp_verified_at' => null,
            'verification_level' => 'none',
            'verification_status' => 'draft',
            'status' => 'inactive',
            'basic_verified_at' => null,
            'manual_verified_at' => null,
            'verification_submitted_at' => null,
            'verification_reviewed_at' => null,
            'verification_reviewer_id' => null,
            'verification_notes' => null,
        ])->save();

        TenantEmailOtp::query()
            ->where('tenant_id', $tenant->idTenant)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        return $tenant;
    }

    public function hasAllRequiredDocuments(Tenant $tenant): bool
    {
        $foundDocTypes = TenantVerificationDocument::query()
            ->where('tenant_id', $tenant->idTenant)
            ->pluck('doc_type')
            ->unique()
            ->all();

        $required = TenantVerificationDocument::REQUIRED_DOC_TYPES;

        return count(array_diff($required, $foundDocTypes)) === 0;
    }

    public function recordLog(Tenant $tenant, ?User $actor, string $action, array $meta = []): void
    {
        TenantVerificationLog::query()->create([
            'tenant_id' => $tenant->idTenant,
            'actor_id' => $actor?->id,
            'action' => $action,
            'meta' => $this->sanitizeMeta($meta),
        ]);
    }

    private function sanitizeMeta(array $meta): array
    {
        return collect($meta)
            ->map(function ($value) {
                if (is_string($value)) {
                    return Str::limit($value, 2000);
                }

                return $value;
            })
            ->all();
    }

}
