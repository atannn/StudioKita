<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Photo;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\TenantDatabase;
use App\Models\TenantMidtransSubmission;
use App\Models\TenantVerificationDocument;
use App\Support\TenantDatabaseManager;
use App\Support\TenantMidtransService;
use App\Support\TenantProfileSynchronizer;
use App\Support\TenantVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantDatabaseManager $tenantDbManager,
        private readonly TenantProfileSynchronizer $tenantProfileSynchronizer,
        private readonly TenantVerificationService $verificationService,
        private readonly TenantMidtransService $tenantMidtransService
    ) {
    }

    public function show(Tenant $tenant)
    {
        $rooms = collect();
        $services = collect();
        $facilities = collect();
        $photos = collect();
        $primaryPhoto = null;
        $paymentAccount = null;
        $paymentConfigActive = false;
        $paymentConfigReady = false;

        try {
            $this->tenantDbManager->runForTenant($tenant, function () use (&$rooms, &$services, &$facilities, &$photos, &$primaryPhoto) {
                $rooms = Room::query()
                    ->orderBy('nama_ruangan')
                    ->get();

                $services = Service::query()
                    ->orderBy('nama_service')
                    ->get();

                $facilities = Facility::query()
                    ->orderBy('nama_fasilitas')
                    ->get();

                $photos = Photo::query()
                    ->orderByDesc('is_primary')
                    ->orderByDesc('idfoto')
                    ->get();

                $primaryPhoto = Photo::query()
                    ->where('is_primary', true)
                    ->where('status', 1)
                    ->latest('idfoto')
                    ->first();
            });

            $paymentAccount = $this->tenantMidtransService->getPaymentAccount($tenant);
            $paymentConfigActive = $this->tenantMidtransService->hasActiveConfiguration($tenant);
            $paymentConfigReady = $this->tenantMidtransService->hasReadyActivationConfiguration($tenant);
        } catch (\Throwable $e) {
            $rooms = collect();
            $services = collect();
            $facilities = collect();
            $photos = collect();
            $primaryPhoto = null;
            $paymentAccount = null;
            $paymentConfigActive = false;
            $paymentConfigReady = false;
        }

        $tenant->setRelation('rooms', $rooms);
        $tenant->setRelation('services', $services);
        $tenant->setRelation('facilities', $facilities);
        $tenant->setRelation('photos', $photos);
        $tenant->setRelation('primaryPhoto', $primaryPhoto);
        $tenant->setRelation('users', $tenant->users()->get());
        $tenant->setRelation('midtransSubmission', $tenant->midtransSubmission()->first());
        $tenant->setRelation('verificationDocuments', $tenant->verificationDocuments()->orderBy('doc_type')->get());
        $tenant->setRelation('verificationLogs', $tenant->verificationLogs()->latest('id')->limit(10)->get());
        $tenant->setRelation('verificationReviewer', $tenant->verificationReviewer()->first());

        $stats = [
            'rooms' => $rooms->count(),
            'services' => $services->count(),
            'facilities' => $facilities->count(),
            'photos' => $photos->where('is_primary', false)->count(),
        ];

        return view('developer.tenants.show', [
            'tenant' => $tenant,
            'stats' => $stats,
            'requiredDocTypes' => TenantVerificationDocument::REQUIRED_DOC_TYPES,
            'paymentAccount' => $paymentAccount,
            'paymentSubmissionStatusLabels' => TenantMidtransSubmission::labels(),
            'paymentConfigActive' => $paymentConfigActive,
            'paymentConfigReady' => $paymentConfigReady,
        ]);
    }

    public function updateStatus(Tenant $tenant): RedirectResponse
    {
        $newStatus = $tenant->status === 'active' ? 'inactive' : 'active';

        if ($newStatus === 'active' && !$this->verificationService->canActivate($tenant)) {
            return back()->withErrors([
                'status' => 'Studio belum Verified Level 2, sehingga belum bisa diaktifkan.',
            ]);
        }

        if ($newStatus === 'active') {
            $this->tenantDbManager->activateForTenant($tenant, true);

            if (!$this->tenantMidtransService->hasReadyActivationConfiguration($tenant)) {
                return back()->withErrors([
                    'status' => 'Studio belum bisa diaktifkan karena Payment Settings Midtrans belum tervalidasi (aktif + test koneksi berhasil).',
                ]);
            }
        }

        $tenant->status = $newStatus;
        $tenant->save();
        $this->tenantProfileSynchronizer->sync($tenant);

        return back()->with('success', 'Status studio berhasil diperbarui.');
    }

    public function approveVerification(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'verification_notes' => 'nullable|string|max:2000',
        ]);

        if ((int) ($request->user()->tenants_idTenant ?? 0) === (int) $tenant->idTenant) {
            return back()->withErrors([
                'verification' => 'Developer tidak boleh memverifikasi tenant miliknya sendiri.',
            ]);
        }

        if ($tenant->verification_status !== 'pending') {
            return back()->withErrors([
                'verification' => 'Studio belum dalam status menunggu review.',
            ]);
        }

        if (!$this->verificationService->hasAllRequiredDocuments($tenant)) {
            return back()->withErrors([
                'verification' => 'Dokumen verifikasi belum lengkap.',
            ]);
        }

        $tenant->forceFill([
            'verification_level' => 'verified',
            'verification_status' => 'approved',
            'manual_verified_at' => now(),
            'verification_reviewed_at' => now(),
            'verification_reviewer_id' => $request->user()->id,
            'verification_notes' => $request->input('verification_notes'),
        ])->save();

        TenantVerificationDocument::query()
            ->where('tenant_id', $tenant->idTenant)
            ->update(['status' => 'approved']);

        $this->verificationService->recordLog($tenant, $request->user(), 'manual_approved', [
            'notes' => $request->input('verification_notes'),
        ]);

        return back()->with('success', 'Verifikasi manual disetujui. Studio naik ke level Verified.');
    }

    public function rejectVerification(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'verification_notes' => 'required|string|max:2000',
        ]);

        if ((int) ($request->user()->tenants_idTenant ?? 0) === (int) $tenant->idTenant) {
            return back()->withErrors([
                'verification' => 'Developer tidak boleh memverifikasi tenant miliknya sendiri.',
            ]);
        }

        if ($tenant->verification_status !== 'pending') {
            return back()->withErrors([
                'verification' => 'Studio belum dalam status menunggu review.',
            ]);
        }

        $tenant = $this->verificationService->refreshBasicVerification($tenant);

        $tenant->forceFill([
            'verification_level' => $tenant->verification_level === 'basic_verified' ? 'basic_verified' : 'none',
            'verification_status' => 'rejected',
            'status' => 'inactive',
            'manual_verified_at' => null,
            'verification_reviewed_at' => now(),
            'verification_reviewer_id' => $request->user()->id,
            'verification_notes' => $request->input('verification_notes'),
        ])->save();
        $this->tenantProfileSynchronizer->sync($tenant);

        TenantVerificationDocument::query()
            ->where('tenant_id', $tenant->idTenant)
            ->update(['status' => 'rejected']);

        $this->verificationService->recordLog($tenant, $request->user(), 'manual_rejected', [
            'notes' => $request->input('verification_notes'),
        ]);

        return back()->with('success', 'Verifikasi manual ditolak dan catatan review tersimpan.');
    }

        public function downloadVerificationDocument(TenantVerificationDocument $document)
        {
            $disk = Storage::disk('local');

            abort_unless($disk->exists($document->file_path), 404);

            return response()->download(
                $disk->path($document->file_path),
                $document->original_name ?: basename($document->file_path)
            );
        }

    public function destroy(Request $request, Tenant $tenant): RedirectResponse
    {
        if ((int) ($request->user()->tenanzts_idTenant ?? 0) === (int) $tenant->idTenant) {
            return back()->withErrors([
                'tenant' => 'Developer tidak boleh menghapus tenant miliknya sendiri.',
            ]);
        }

        $documentPaths = TenantVerificationDocument::query()
            ->where('tenant_id', $tenant->idTenant)
            ->pluck('file_path')
            ->filter()
            ->values()
            ->all();

        $photoPaths = [];
        $tenantDatabase = $tenant->databaseConnection;

        if ($tenantDatabase) {
            try {
                $this->tenantDbManager->runForTenant($tenant, function () use (&$photoPaths) {
                    $photoPaths = Photo::query()
                        ->pluck('foto_path')
                        ->filter()
                        ->values()
                        ->all();
                }, false);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            DB::transaction(function () use ($tenant) {
                $tenant->delete();
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'tenant' => 'Gagal menghapus studio. Coba lagi.',
            ]);
        }

        if (!empty($photoPaths)) {
            Storage::disk('public')->delete($photoPaths);
        }

        if (!empty($documentPaths)) {
            Storage::disk('local')->delete($documentPaths);
        }

        $tenantDbDeleted = $this->deleteTenantDatabase($tenantDatabase);

        $message = 'Studio berhasil dihapus permanen.';
        if (!$tenantDbDeleted) {
            $message .= ' Database tenant belum berhasil di-drop otomatis.';
        }

        return redirect()
            ->route('developer.dashboard')
            ->with('success', $message);
    }

    private function deleteTenantDatabase(?TenantDatabase $tenantDatabase): bool
    {
        if (!$tenantDatabase) {
            return true;
        }

        DB::disconnect('tenant');
        DB::purge('tenant');

        if ($tenantDatabase->driver !== 'mysql') {
            report(new \RuntimeException("Driver tenant DB [{$tenantDatabase->driver}] tidak didukung untuk proses delete."));

            return false;
        }

        try {
            $this->tenantDbManager->dropMySqlDatabase((string) $tenantDatabase->database_name);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
