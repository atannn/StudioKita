<?php

namespace App\Support;

use Carbon\CarbonInterface;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantPaymentAccount;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class TenantMidtransService
{
    /**
     * @return array{enabled:bool,percent:int,allowed_percents:int[]}
     */
    public function getDpPolicy(Tenant $tenant): array
    {
        $account = $this->resolvePaymentAccount($tenant);
        $percent = (int) ($account?->dp_percent ?? 30);
        if ($percent < 1 || $percent > 90) {
            $percent = 30;
        }

        return [
            'enabled' => (bool) ($account?->dp_enabled ?? true),
            'percent' => $percent,
            'allowed_percents' => [$percent],
        ];
    }

    /**
     * @return array{enabled:bool,instruction:?string}
     */
    public function getCashPolicy(Tenant $tenant): array
    {
        $account = $this->resolvePaymentAccount($tenant);
        $instruction = trim((string) ($account?->cash_instruction ?? ''));

        return [
            'enabled' => (bool) ($account?->cash_enabled ?? false),
            'instruction' => $instruction !== '' ? $instruction : null,
        ];
    }

    public function getPaymentAccount(Tenant $tenant): ?TenantPaymentAccount
    {
        return $this->resolvePaymentAccount($tenant);
    }

    public function hasActiveConfiguration(Tenant $tenant): bool
    {
        $account = $this->resolvePaymentAccount($tenant);

        return $account instanceof TenantPaymentAccount
            && (bool) $account->is_active
            && (bool) $this->decryptSecret($account->midtrans_client_key_enc)
            && (bool) $this->decryptSecret($account->midtrans_server_key_enc);
    }

    public function hasReadyActivationConfiguration(Tenant $tenant): bool
    {
        $account = $this->resolvePaymentAccount($tenant);

        return $this->hasActiveConfiguration($tenant)
            && (bool) ($account?->midtrans_last_test_success ?? false)
            && (bool) ($account?->midtrans_last_tested_at ?? false);
    }

    public function getSnapClientKey(Tenant $tenant): ?string
    {
        $account = $this->resolvePaymentAccount($tenant);
        if (!$account) {
            return null;
        }

        return $this->decryptSecret($account->midtrans_client_key_enc);
    }

    public function getSnapJsUrl(Tenant $tenant): string
    {
        $account = $this->resolvePaymentAccount($tenant);
        $isProduction = (bool) ($account?->is_production ?? false);

        return $isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * @return array{order_id:string,token:string,redirect_url:string}
     */
    public function createSnapTransaction(
        Tenant $tenant,
        Booking $booking,
        Payment $payment,
        ?User $user = null,
        ?CarbonInterface $expiresAt = null
    ): array
    {
        $account = $this->resolvePaymentAccount($tenant);
        if (!$account || !(bool) $account->is_active) {
            throw new \RuntimeException('Konfigurasi Midtrans tenant belum aktif.');
        }

        $serverKey = $this->decryptSecret($account->midtrans_server_key_enc);
        if (!$serverKey) {
            throw new \RuntimeException('Server Key Midtrans tenant tidak valid.');
        }

        $this->configureMidtrans($serverKey, (bool) $account->is_production);

        $orderId = $payment->midtrans_order_id ?: $this->buildOrderId(
            (int) $tenant->idTenant,
            (int) $payment->idpayments,
            (int) $booking->idbooking
        );

        $amount = (int) round((float) $payment->amount);
        if ($amount < 1) {
            throw new \RuntimeException('Total pembayaran tidak valid.');
        }

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user?->name ?: 'Customer',
                'email' => $user?->email ?: $tenant->email,
                'phone' => $user?->no_telp ?: $tenant->no_telp,
            ],
            'item_details' => [[
                'id' => 'BOOKING-'.$booking->idbooking,
                'price' => $amount,
                'quantity' => 1,
                'name' => substr((string) ('Booking '.$tenant->nama), 0, 50),
            ]],
        ];

        // Untuk pelunasan, kita bisa kirim custom expiry agar konsisten dengan batas waktu internal.
        if ($expiresAt && $expiresAt->isFuture()) {
            $params['custom_expiry'] = [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => max(1, now()->diffInMinutes($expiresAt)),
            ];
        }

        $response = Snap::createTransaction($params);
        $token = (string) ($response->token ?? '');
        $redirectUrl = (string) ($response->redirect_url ?? '');

        if ($token === '' || $redirectUrl === '') {
            throw new \RuntimeException('Gagal membuat transaksi Snap Midtrans.');
        }

        return [
            'order_id' => $orderId,
            'token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * @return array{tenant_id:int,payment_id:int,booking_id:int}|null
     */
    public function parseOrderId(string $orderId): ?array
    {
        if (!preg_match('/^SK-(\d+)-(\d+)-(\d+)-(\d+)$/', $orderId, $matches)) {
            return null;
        }

        return [
            'tenant_id' => (int) $matches[1],
            'payment_id' => (int) $matches[2],
            'booking_id' => (int) $matches[3],
        ];
    }

    public function isValidSignature(Tenant $tenant, array $payload): bool
    {
        $account = $this->resolvePaymentAccount($tenant);
        if (!$account) {
            return false;
        }

        $serverKey = $this->decryptSecret($account->midtrans_server_key_enc);
        if (!$serverKey) {
            return false;
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return false;
        }

        $raw = $orderId.$statusCode.$grossAmount.$serverKey;
        $expected = hash('sha512', $raw);

        return hash_equals($expected, $signatureKey);
    }

    public function mapStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'success' : 'pending',
            'settlement' => 'success',
            'pending' => 'pending',
            'deny', 'failure' => 'failed',
            'expire' => 'expired',
            'cancel' => 'cancelled',
            default => 'pending',
        };
    }

    private function buildOrderId(int $tenantId, int $paymentId, int $bookingId): string
    {
        return 'SK-'.$tenantId.'-'.$paymentId.'-'.$bookingId.'-'.now()->timestamp;
    }

    private function configureMidtrans(string $serverKey, bool $isProduction): void
    {
        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = $isProduction;
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
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

    private function resolvePaymentAccount(Tenant $tenant): ?TenantPaymentAccount
    {
        return TenantPaymentAccount::query()
            ->where('tenant_id', $tenant->idTenant)
            ->first();
    }

}
