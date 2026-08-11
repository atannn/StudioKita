<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantMidtransSubmission;
use App\Models\TenantPaymentAccount;
use App\Support\TenantMidtransService;
use App\Support\TenantVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PaymentSettingsController extends Controller
{
    public function __construct(
        private readonly TenantVerificationService $tenantVerificationService,
        private readonly TenantMidtransService $tenantMidtransService
    ) {
    }

    public function edit()
    {
        $tenantId = Auth::user()?->tenants_idTenant;
        if (!$tenantId) {
            return redirect()
                ->route('owner.setup.step1')
                ->withErrors(['tenant' => 'Lengkapi data studio terlebih dahulu.']);
        }

        $tenant = Tenant::query()
            ->with(['midtransSubmission', 'verificationDocuments'])
            ->findOrFail($tenantId);

        $submission = $tenant->midtransSubmission;
        $paymentAccount = $this->tenantMidtransService->getPaymentAccount($tenant);

        return view('owner.payments.settings', [
            'tenant' => $tenant,
            'submission' => $submission,
            'paymentAccount' => $paymentAccount,
            'submissionStatusLabels' => TenantMidtransSubmission::labels(),
            'hasRequiredDocuments' => $this->tenantVerificationService->hasAllRequiredDocuments($tenant),
            'isPaymentReady' => $this->tenantMidtransService->hasReadyActivationConfiguration($tenant),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant();
        $submission = $this->resolveSubmission($tenant);
        $validated = $this->validateSubmission($request, false);

        $submission->fill($validated);
        $submission->status = TenantMidtransSubmission::STATUS_DRAFT;
        $submission->save();

        return redirect()
            ->route('owner.payment-settings.edit')
            ->with('success', 'Draft pengajuan Midtrans berhasil disimpan.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant();
        $submission = $this->resolveSubmission($tenant);
        $validated = $this->validateSubmission($request, true);

        if (!$this->tenantVerificationService->hasAllRequiredDocuments($tenant)) {
            return back()
                ->withErrors([
                    'payment_submission' => 'Dokumen verifikasi studio belum lengkap. Lengkapi dokumen terlebih dahulu sebelum mengirim pengajuan Midtrans.',
                ])
                ->withInput();
        }

        $submission->fill($validated);
        $submission->forceFill([
            'status' => TenantMidtransSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_notes' => null,
        ])->save();

        return redirect()
            ->route('owner.payment-settings.edit')
            ->with('success', 'Pengajuan Midtrans berhasil dikirim ke developer untuk direview.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant();

        $validated = $request->validate([
            'dp_enabled' => 'nullable|boolean',
            'dp_percent' => 'nullable|integer|min:1|max:90',
            'cash_enabled' => 'nullable|boolean',
            'cash_instruction' => 'nullable|string|max:2000',
        ]);

        $dpEnabled = $request->boolean('dp_enabled');
        $cashEnabled = $request->boolean('cash_enabled');
        $dpPercent = $this->resolveDpPercent($validated['dp_percent'] ?? null);

        $account = $this->tenantMidtransService->getPaymentAccount($tenant)
            ?? TenantPaymentAccount::query()->firstOrNew([
                'tenant_id' => $tenant->idTenant,
            ]);

        $account->fill([
            'provider' => $account->provider ?: 'midtrans',
            'dp_enabled' => $dpEnabled,
            'dp_percent' => $dpPercent,
            'cash_enabled' => $cashEnabled,
            'cash_instruction' => $cashEnabled ? $this->normalizeNullableString($validated['cash_instruction'] ?? null) : null,
        ]);
        $account->save();

        return redirect()
            ->route('owner.payment-settings.edit')
            ->with('success', 'Preferensi pembayaran studio berhasil disimpan.');
    }

    private function resolveTenant(): Tenant
    {
        $tenantId = Auth::user()?->tenants_idTenant;
        if (!$tenantId) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        return Tenant::query()->findOrFail($tenantId);
    }

    private function resolveSubmission(Tenant $tenant): TenantMidtransSubmission
    {
        return TenantMidtransSubmission::query()->firstOrNew([
            'tenant_id' => $tenant->idTenant,
        ]);
    }

    private function validateSubmission(Request $request, bool $strict): array
    {
        $required = $strict ? ['required', 'string'] : ['nullable', 'string'];
        $requiredUrl = $strict ? ['required', 'url', 'max:255'] : ['nullable', 'url', 'max:255'];
        $requiredEmail = $strict ? ['required', 'email', 'max:255'] : ['nullable', 'email', 'max:255'];

        $validated = $request->validate([
            'business_entity_type' => [
                $strict ? 'required' : 'nullable',
                'string',
                Rule::in(['individu', 'pt', 'cv', 'yayasan', 'lainnya']),
            ],
            'legal_business_name' => array_merge($required, ['max:150']),
            'brand_name' => ['nullable', 'string', 'max:150'],
            'business_category' => array_merge($required, ['max:150']),
            'business_description_short' => [$strict ? 'required' : 'nullable', 'string', 'max:2000'],
            'business_email' => $requiredEmail,
            'business_phone' => array_merge($required, ['max:45']),
            'public_business_url' => $requiredUrl,
            'pic_name' => array_merge($required, ['max:100']),
            'pic_phone' => array_merge($required, ['max:45']),
            'pic_email' => $requiredEmail,
            'bank_name' => array_merge($required, ['max:100']),
            'bank_account_number' => array_merge($required, ['max:100']),
            'bank_account_holder_name' => array_merge($required, ['max:150']),
            'submission_notes' => 'nullable|string|max:2000',
        ]);

        return $validated;
    }

    private function resolveDpPercent(?int $percent): int
    {
        if ($percent !== null && $percent >= 1 && $percent <= 90) {
            return $percent;
        }

        return 30;
    }

    private function normalizeNullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
