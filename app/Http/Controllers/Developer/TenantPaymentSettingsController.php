<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantMidtransSubmission;
use App\Models\TenantPaymentAccount;
use App\Support\TenantDatabaseManager;
use App\Support\TenantMidtransService;
use App\Support\TenantProfileSynchronizer;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Midtrans\Config as MidtransConfig;
use Midtrans\Transaction as MidtransTransaction;

class TenantPaymentSettingsController extends Controller
{
    public function __construct(
        private readonly TenantMidtransService $tenantMidtransService,
        private readonly TenantProfileSynchronizer $tenantProfileSynchronizer,
        private readonly TenantDatabaseManager $tenantDbManager
    ) {
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->guardAgainstSelfReview($request, $tenant);
        $this->tenantDbManager->activateForTenant($tenant, true);

        $validated = $request->validate([
            'merchant_id' => 'nullable|string|max:100',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_server_key' => 'nullable|string|max:255',
            'is_production' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $account = $this->tenantMidtransService->getPaymentAccount($tenant)
            ?? TenantPaymentAccount::query()->firstOrNew([
                'tenant_id' => $tenant->idTenant,
            ]);

        $isProduction = (bool) $request->boolean('is_production');
        $isActive = (bool) $request->boolean('is_active');

        $currentClientKey = $this->decryptSecret($account->midtrans_client_key_enc ?? null);
        $currentServerKey = $this->decryptSecret($account->midtrans_server_key_enc ?? null);

        $newClientKey = trim((string) ($validated['midtrans_client_key'] ?? ''));
        $newServerKey = trim((string) ($validated['midtrans_server_key'] ?? ''));
        $modeChanged = (bool) ($account->is_production ?? false) !== $isProduction;
        $credentialsChanged = $newClientKey !== '' || $newServerKey !== '';

        $clientKey = $newClientKey !== '' ? $newClientKey : $currentClientKey;
        $serverKey = $newServerKey !== '' ? $newServerKey : $currentServerKey;

        if ($clientKey && !$this->isValidMidtransClientKey($clientKey, $isProduction)) {
            return back()
                ->withErrors([
                    'midtrans_client_key' => 'Format Client Key tidak sesuai untuk mode yang dipilih.',
                ])
                ->withInput();
        }

        if ($serverKey && !$this->isValidMidtransServerKey($serverKey, $isProduction)) {
            return back()
                ->withErrors([
                    'midtrans_server_key' => 'Format Server Key tidak sesuai untuk mode yang dipilih.',
                ])
                ->withInput();
        }

        if ($isActive && (!$clientKey || !$serverKey)) {
            return back()
                ->withErrors([
                    'is_active' => 'Isi Client Key dan Server Key terlebih dahulu sebelum mengaktifkan Midtrans.',
                ])
                ->withInput();
        }

        $account->fill([
            'provider' => 'midtrans',
            'merchant_id' => $this->normalizeNullableString($validated['merchant_id'] ?? null),
            'is_production' => $isProduction,
            'is_active' => $isActive,
        ]);

        if ($newClientKey !== '') {
            $account->midtrans_client_key_enc = Crypt::encryptString($newClientKey);
        }

        if ($newServerKey !== '') {
            $account->midtrans_server_key_enc = Crypt::encryptString($newServerKey);
        }

        if ($modeChanged || $credentialsChanged) {
            $account->midtrans_last_test_success = false;
            $account->midtrans_last_tested_at = null;
        }

        $account->save();

        $forcedInactive = $this->enforceTenantInactiveIfPaymentNotReady($tenant);

        $message = 'Konfigurasi Midtrans tenant berhasil disimpan.';
        if ($forcedInactive) {
            $message .= ' Status studio diubah menjadi Inactive karena koneksi Midtrans belum tervalidasi.';
        }

        return back()->with('success', $message);
    }

    public function testConnection(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->guardAgainstSelfReview($request, $tenant);
        $this->tenantDbManager->activateForTenant($tenant, true);

        $validated = $request->validate([
            'merchant_id' => 'nullable|string|max:100',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_server_key' => 'nullable|string|max:255',
            'is_production' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $account = $this->tenantMidtransService->getPaymentAccount($tenant);

        $isProduction = (bool) $request->boolean('is_production');

        $currentClientKey = $this->decryptSecret($account?->midtrans_client_key_enc);
        $currentServerKey = $this->decryptSecret($account?->midtrans_server_key_enc);

        $newClientKey = trim((string) ($validated['midtrans_client_key'] ?? ''));
        $newServerKey = trim((string) ($validated['midtrans_server_key'] ?? ''));

        $clientKey = $newClientKey !== '' ? $newClientKey : $currentClientKey;
        $serverKey = $newServerKey !== '' ? $newServerKey : $currentServerKey;
        $isTestingSavedConfig = $this->isTestingSavedConfiguration(
            $account,
            $isProduction,
            $newClientKey,
            $newServerKey,
            $currentClientKey,
            $currentServerKey
        );

        if (!$clientKey || !$serverKey) {
            return back()
                ->withErrors([
                    'midtrans_test' => 'Isi Client Key dan Server Key terlebih dahulu untuk test koneksi.',
                ])
                ->withInput();
        }

        if (!$this->isValidMidtransClientKey($clientKey, $isProduction)) {
            return back()
                ->withErrors([
                    'midtrans_client_key' => 'Format Client Key tidak sesuai untuk mode yang dipilih.',
                ])
                ->withInput();
        }

        if (!$this->isValidMidtransServerKey($serverKey, $isProduction)) {
            return back()
                ->withErrors([
                    'midtrans_server_key' => 'Format Server Key tidak sesuai untuk mode yang dipilih.',
                ])
                ->withInput();
        }

        try {
            MidtransConfig::$serverKey = $serverKey;
            MidtransConfig::$isProduction = $isProduction;
            MidtransConfig::$isSanitized = true;
            MidtransConfig::$is3ds = true;

            $probeOrderId = 'SK-CONN-'.$tenant->idTenant.'-'.now()->timestamp;
            MidtransTransaction::status($probeOrderId);

            $this->recordConnectionTestResult($account, $isTestingSavedConfig, true);
            $this->enforceTenantInactiveIfPaymentNotReady($tenant);

            $message = 'Koneksi Midtrans berhasil. Kredensial dapat digunakan.';
            if (!$isTestingSavedConfig) {
                $message .= ' Hasil test belum disimpan sebagai syarat aktivasi karena key atau mode yang dites belum tersimpan.';
            }

            return back()
                ->with('test_success', $message)
                ->withInput();
        } catch (\Throwable $e) {
            $message = strtolower($e->getMessage());

            if (
                str_contains($message, 'unauthorized') ||
                str_contains($message, 'access denied') ||
                str_contains($message, 'http status code: 401')
            ) {
                $this->recordConnectionTestResult($account, $isTestingSavedConfig, false);
                $this->enforceTenantInactiveIfPaymentNotReady($tenant);

                return back()
                    ->withErrors([
                        'midtrans_test' => 'Koneksi gagal. Midtrans menolak kredensial (401). Periksa key dan mode.',
                    ])
                    ->withInput();
            }

            if (
                str_contains($message, '404') ||
                str_contains($message, "transaction doesn't exist") ||
                str_contains($message, 'not found')
            ) {
                $this->recordConnectionTestResult($account, $isTestingSavedConfig, true);
                $this->enforceTenantInactiveIfPaymentNotReady($tenant);

                $notFoundMessage = 'Koneksi Midtrans berhasil. Kredensial valid.';
                if (!$isTestingSavedConfig) {
                    $notFoundMessage .= ' Hasil test belum disimpan sebagai syarat aktivasi karena key atau mode yang dites belum tersimpan.';
                }

                return back()
                    ->with('test_success', $notFoundMessage)
                    ->withInput();
            }

            $this->recordConnectionTestResult($account, $isTestingSavedConfig, false);
            $this->enforceTenantInactiveIfPaymentNotReady($tenant);

            return back()
                ->withErrors([
                    'midtrans_test' => 'Test koneksi gagal: '.$this->normalizeErrorMessage($e->getMessage()),
                ])
                ->withInput();
        }
    }

    public function reviewSubmission(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->guardAgainstSelfReview($request, $tenant);

        $validated = $request->validate([
            'status' => 'required|in:revision_needed,approved',
            'review_notes' => 'nullable|string|max:2000',
        ]);

        $submission = TenantMidtransSubmission::query()
            ->where('tenant_id', $tenant->idTenant)
            ->first();

        if (!$submission) {
            return back()->withErrors([
                'payment_submission' => 'Pengajuan Midtrans dari owner belum tersedia.',
            ]);
        }

        if ($validated['status'] === TenantMidtransSubmission::STATUS_REVISION_NEEDED && trim((string) ($validated['review_notes'] ?? '')) === '') {
            return back()->withErrors([
                'review_notes' => 'Catatan review wajib diisi saat meminta revisi.',
            ]);
        }

        if ($validated['status'] === TenantMidtransSubmission::STATUS_APPROVED) {
            $this->tenantDbManager->activateForTenant($tenant, true);

            if (!$this->tenantMidtransService->hasReadyActivationConfiguration($tenant)) {
                return back()->withErrors([
                    'payment_submission' => 'Pengajuan belum bisa disetujui karena konfigurasi Midtrans tenant belum aktif dan lulus test koneksi.',
                ]);
            }
        }

        $submission->forceFill([
            'status' => $validated['status'],
            'review_notes' => $this->normalizeNullableString($validated['review_notes'] ?? null),
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        $message = $validated['status'] === TenantMidtransSubmission::STATUS_APPROVED
            ? 'Pengajuan Midtrans disetujui.'
            : 'Pengajuan Midtrans dikembalikan untuk direvisi.';

        return back()->with('success', $message);
    }

    private function guardAgainstSelfReview(Request $request, Tenant $tenant): void
    {
        if ((int) ($request->user()->tenants_idTenant ?? 0) === (int) $tenant->idTenant) {
            abort(403, 'Developer tidak boleh memproses payment tenant miliknya sendiri.');
        }
    }

    private function isValidMidtransClientKey(string $key, bool $isProduction): bool
    {
        if ($isProduction) {
            return str_starts_with($key, 'Mid-client-');
        }

        return str_starts_with($key, 'SB-Mid-client-')
            || str_starts_with($key, 'Mid-client-');
    }

    private function isValidMidtransServerKey(string $key, bool $isProduction): bool
    {
        if ($isProduction) {
            return str_starts_with($key, 'Mid-server-');
        }

        return str_starts_with($key, 'SB-Mid-server-')
            || str_starts_with($key, 'Mid-server-');
    }

    private function decryptSecret(?string $encryptedValue): ?string
    {
        if (!$encryptedValue) {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedValue);
        } catch (DecryptException $e) {
            return null;
        }
    }

    private function normalizeNullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeErrorMessage(string $message): string
    {
        $message = preg_replace('/\s+/', ' ', trim($message)) ?? 'unknown error';

        return mb_substr($message, 0, 220);
    }

    private function isTestingSavedConfiguration(
        ?TenantPaymentAccount $account,
        bool $isProduction,
        string $newClientKey,
        string $newServerKey,
        ?string $currentClientKey,
        ?string $currentServerKey
    ): bool {
        if (!$account || !$currentClientKey || !$currentServerKey) {
            return false;
        }

        if ((bool) $account->is_production !== $isProduction) {
            return false;
        }

        if ($newClientKey !== '' && $newClientKey !== $currentClientKey) {
            return false;
        }

        if ($newServerKey !== '' && $newServerKey !== $currentServerKey) {
            return false;
        }

        return true;
    }

    private function recordConnectionTestResult(?TenantPaymentAccount $account, bool $shouldPersist, bool $isSuccess): void
    {
        if (!$account || !$shouldPersist) {
            return;
        }

        $account->forceFill([
            'midtrans_last_test_success' => $isSuccess,
            'midtrans_last_tested_at' => now(),
        ])->save();
    }

    private function enforceTenantInactiveIfPaymentNotReady(Tenant $tenant): bool
    {
        $isReadyForActivation = $this->tenantMidtransService->hasReadyActivationConfiguration($tenant);

        if ($tenant->status !== 'active' || $isReadyForActivation) {
            return false;
        }

        $tenant->forceFill(['status' => 'inactive'])->save();
        $this->tenantProfileSynchronizer->sync($tenant);

        return true;
    }
}
