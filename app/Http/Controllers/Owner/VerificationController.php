<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Mail\TenantEmailOtpMail;
use App\Models\Tenant;
use App\Models\TenantEmailOtp;
use App\Models\TenantVerificationDocument;
use App\Support\TenantVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function __construct(
        private readonly TenantVerificationService $verificationService
    ) {
    }

    public function index()
    {
        $tenant = $this->resolveOwnerTenant();

        $checklist = $this->verificationService->getBasicChecklist($tenant);
        $documents = TenantVerificationDocument::query()
            ->where('tenant_id', $tenant->idTenant)
            ->orderBy('doc_type')
            ->get()
            ->keyBy('doc_type');

        $latestOtp = TenantEmailOtp::query()
            ->where('tenant_id', $tenant->idTenant)
            ->latest('id')
            ->first();

        return view('owner.verification.index', [
            'tenant' => $tenant,
            'checklist' => $checklist,
            'documents' => $documents,
            'requiredDocTypes' => TenantVerificationDocument::REQUIRED_DOC_TYPES,
            'latestOtp' => $latestOtp,
        ]);
    }

    public function sendEmailOtp(Request $request)
    {
        $tenant = $this->resolveOwnerTenant();

        if (trim((string) $tenant->email) === '') {
            return back()->withErrors([
                'otp' => 'Email studio belum diisi. Lengkapi profil studio terlebih dahulu.',
            ]);
        }

        $rateKey = 'tenant-email-otp-send:'.$tenant->idTenant;
        if (RateLimiter::tooManyAttempts($rateKey, 1)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return back()->withErrors([
                'otp' => "Tunggu {$seconds} detik sebelum kirim OTP lagi.",
            ]);
        }

        RateLimiter::hit($rateKey, 60);

        TenantEmailOtp::query()
            ->where('tenant_id', $tenant->idTenant)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        $otpCode = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        TenantEmailOtp::query()->create([
            'tenant_id' => $tenant->idTenant,
            'created_by' => Auth::id(),
            'email' => $tenant->email,
            'code_hash' => Hash::make($otpCode),
            'attempts' => 0,
            'expires_at' => $expiresAt,
        ]);

        Mail::to($tenant->email)->send(new TenantEmailOtpMail(
            tenant: $tenant,
            otpCode: $otpCode,
            expiresAt: $expiresAt->format('d M Y H:i')
        ));

        $this->verificationService->recordLog($tenant, $request->user(), 'otp_sent', [
            'email' => $tenant->email,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        return back()->with('success', 'Kode OTP telah dikirim ke email studio.');
    }

    public function verifyEmailOtp(Request $request)
    {
        $tenant = $this->resolveOwnerTenant();

        $validated = $request->validate([
            'otp_code' => 'required|digits:6',
        ]);

        $otp = TenantEmailOtp::query()
            ->where('tenant_id', $tenant->idTenant)
            ->whereNull('verified_at')
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (!$otp) {
            return back()->withErrors([
                'otp_code' => 'OTP tidak ditemukan atau sudah kedaluwarsa. Kirim ulang OTP.',
            ]);
        }

        if ($otp->attempts >= 5) {
            return back()->withErrors([
                'otp_code' => 'OTP diblokir karena terlalu banyak percobaan. Kirim ulang OTP.',
            ]);
        }

        if (!Hash::check($validated['otp_code'], $otp->code_hash)) {
            $otp->increment('attempts');

            return back()->withErrors([
                'otp_code' => 'Kode OTP tidak valid.',
            ]);
        }

        $otp->forceFill([
            'verified_at' => now(),
        ])->save();

        $tenant->forceFill([
            'email_otp_verified_at' => now(),
        ])->save();

        $tenant = $this->verificationService->refreshBasicVerification($tenant);

        $this->verificationService->recordLog($tenant, $request->user(), 'otp_verified', [
            'email' => $tenant->email,
        ]);

        if ($tenant->verification_level === 'basic_verified') {
            return back()->with('success', 'OTP valid. Level Basic Verified aktif.');
        }

        return back()->with('success', 'OTP valid. Lengkapi checklist basic verification agar level aktif.');
    }

    public function submitManual(Request $request)
    {
        $tenant = $this->resolveOwnerTenant();
        $tenant = $this->verificationService->refreshBasicVerification($tenant);

        if ($tenant->verification_level !== 'basic_verified' && $tenant->verification_level !== 'verified') {
            return back()->withErrors([
                'documents' => 'Basic verification belum lengkap. Selesaikan OTP email dan checklist profil terlebih dahulu.',
            ]);
        }

        if ($tenant->verification_level === 'verified' && $tenant->verification_status === 'approved') {
            return back()->with('success', 'Studio sudah terverifikasi penuh.');
        }

        $existingDocTypes = TenantVerificationDocument::query()
            ->where('tenant_id', $tenant->idTenant)
            ->pluck('doc_type')
            ->all();

        $rules = [];
        foreach (TenantVerificationDocument::REQUIRED_DOC_TYPES as $docType) {
            $baseRule = 'file|mimes:jpg,jpeg,png,pdf|max:5120';
            $rules[$docType] = in_array($docType, $existingDocTypes, true)
                ? 'nullable|'.$baseRule
                : 'required|'.$baseRule;
        }

        $request->validate($rules);

        foreach (TenantVerificationDocument::REQUIRED_DOC_TYPES as $docType) {
            if (!$request->hasFile($docType)) {
                continue;
            }

            $file = $request->file($docType);
            $path = $file->store('tenant-verifications/'.$tenant->idTenant, 'local');

            TenantVerificationDocument::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->idTenant,
                    'doc_type' => $docType,
                ],
                [
                    'uploaded_by' => Auth::id(),
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => 'uploaded',
                    'uploaded_at' => now(),
                ]
            );
        }

        if (!$this->verificationService->hasAllRequiredDocuments($tenant)) {
            return back()->withErrors([
                'documents' => 'Dokumen wajib belum lengkap. Pastikan keempat dokumen diunggah.',
            ]);
        }

        $tenant->forceFill([
            'verification_status' => 'pending',
            'verification_submitted_at' => now(),
            'verification_reviewed_at' => null,
            'verification_reviewer_id' => null,
            'verification_notes' => null,
        ])->save();

        TenantVerificationDocument::query()
            ->where('tenant_id', $tenant->idTenant)
            ->update(['status' => 'uploaded']);

        $this->verificationService->recordLog($tenant, $request->user(), 'manual_submitted', [
            'document_types' => TenantVerificationDocument::REQUIRED_DOC_TYPES,
        ]);

        return back()->with('success', 'Dokumen verifikasi terkirim. Menunggu review developer.');
    }

    public function downloadDocument(TenantVerificationDocument $document)
    {
        $tenant = $this->resolveOwnerTenant();

        abort_unless($document->tenant_id === $tenant->idTenant, 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name ?: basename($document->file_path)
        );
    }

    private function resolveOwnerTenant(): Tenant
    {
        $tenantId = Auth::user()?->tenants_idTenant;
        abort_unless($tenantId, 404);

        return Tenant::query()->findOrFail($tenantId);
    }
}
