<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Payment;
use App\Models\Tenant;
use App\Support\TenantDatabaseManager;
use App\Support\ScheduleAvailabilityService;
use App\Support\TenantMidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        private readonly TenantMidtransService $tenantMidtransService,
        private readonly TenantDatabaseManager $tenantDbManager,
        private readonly ScheduleAvailabilityService $scheduleAvailabilityService
    ) {
    }

    public function checkout(Tenant $tenant, int $paymentId, Request $request)
    {
        $tenant->loadMissing(['primaryPhoto']);

        $payment = Payment::query()
            ->where('idpayments', $paymentId)
            ->where('tenants_idTenant', $tenant->idTenant)
            ->firstOrFail();

        $booking = Booking::query()
            ->with(['room', 'service', 'jadwal', 'payments' => fn ($query) => $query->latest('idpayments')])
            ->where('idbooking', $payment->booking_idbooking)
            ->firstOrFail();

        $user = $request->user();
        $isOwnerOfTenant = $user->role === 'owner' && (int) $user->tenants_idTenant === (int) $tenant->idTenant;
        $isBookingOwner = (int) $booking->user_id === (int) $user->id;
        abort_unless($isOwnerOfTenant || $isBookingOwner, 403);

        $this->syncBookingPaymentState($booking);
        $booking->refresh();

        $isCashPayment = (string) $payment->method === 'Cash';
        $cashPolicy = $this->tenantMidtransService->getCashPolicy($tenant);

        $remainingPaymentDeadline = null;
        $isRemainingPaymentWindowClosed = false;
        if ((string) $payment->payment_type === 'remaining') {
            $remainingPaymentDeadline = $this->resolveRemainingPaymentDeadline($booking);
            $isRemainingPaymentWindowClosed = $remainingPaymentDeadline
                ? now()->greaterThanOrEqualTo($remainingPaymentDeadline)
                : true;

            // Selaraskan expiry internal untuk pelunasan agar countdown selalu konsisten.
            if ($remainingPaymentDeadline && (
                !$payment->expires_time
                || $payment->expires_time->greaterThan($remainingPaymentDeadline)
            )) {
                $payment->forceFill([
                    'expires_time' => $remainingPaymentDeadline,
                ])->save();
            }

            if ($isRemainingPaymentWindowClosed && $payment->status === 'pending') {
                $payment->forceFill([
                    'status' => 'expired',
                    'raw_status' => 'remaining_payment_deadline_passed',
                    'failed_at' => now(),
                ])->save();
            }
        }

        $payment->refresh();

        if ($payment->status === 'success') {
            return redirect()
                ->route('customer.profile')
                ->with('success', 'Pembayaran sudah sukses.');
        }

        $isPaymentConfigured = $isCashPayment
            ? true
            : $this->tenantMidtransService->hasActiveConfiguration($tenant);
        $shouldRegenerateSnap = in_array((string) $payment->status, ['failed', 'expired', 'cancelled'], true);

        if (!$isCashPayment && $isPaymentConfigured && !$isRemainingPaymentWindowClosed && (
            !$payment->snap_token
            || !$payment->midtrans_order_id
            || $shouldRegenerateSnap
        )) {
            try {
                if ($shouldRegenerateSnap) {
                    // Force new order_id + snap token when previous transaction already failed/expired.
                    $payment->midtrans_order_id = null;
                    $payment->snap_token = null;
                    $payment->snap_redirect_url = null;
                }

                $snap = $this->tenantMidtransService->createSnapTransaction(
                    $tenant,
                    $booking,
                    $payment,
                    $user,
                    $remainingPaymentDeadline
                );

                $payment->forceFill([
                    'method' => 'Midtrans',
                    'midtrans_order_id' => $snap['order_id'],
                    'snap_token' => $snap['token'],
                    'snap_redirect_url' => $snap['redirect_url'],
                    'status' => 'pending',
                ])->save();
            } catch (\Throwable $e) {
                report($e);

                return redirect()
                    ->route('studios.show', $tenant->slug)
                    ->withErrors(['payment' => 'Gagal menyiapkan checkout Midtrans. Silakan coba lagi.']);
            }
        }

        $paidAmount = (float) ($booking->paid_amount ?? 0);
        $bookingTotal = (float) $booking->total_harga;
        $remainingAmount = max($bookingTotal - $paidAmount, 0);

        return view('payments.checkout', [
            'tenant' => $tenant,
            'booking' => $booking,
            'payment' => $payment,
            'snapClientKey' => $this->tenantMidtransService->getSnapClientKey($tenant),
            'snapJsUrl' => $this->tenantMidtransService->getSnapJsUrl($tenant),
            'isPaymentConfigured' => $isPaymentConfigured,
            'isBypassEnabled' => $this->isMidtransBypassEnabled(),
            'bookingTotal' => $bookingTotal,
            'paidAmount' => $paidAmount,
            'remainingAmount' => $remainingAmount,
            'paymentTypeLabel' => $this->resolvePaymentTypeLabel((string) $payment->payment_type),
            'bookingPaymentSchemeLabel' => $this->resolveBookingPaymentSchemeLabel($booking),
            'remainingPaymentDeadline' => $remainingPaymentDeadline,
            'isRemainingPaymentWindowClosed' => $isRemainingPaymentWindowClosed,
            'isCashPayment' => $isCashPayment,
            'cashInstruction' => $cashPolicy['instruction'] ?? null,
        ]);
    }

    public function createRemainingPayment(Tenant $tenant, int $bookingId, Request $request)
    {
        $tenant->loadMissing('primaryPhoto');

        $booking = Booking::query()
            ->where('idbooking', $bookingId)
            ->where('tenants_idTenant', $tenant->idTenant)
            ->firstOrFail();

        $user = $request->user();
        $isOwnerOfTenant = $user->role === 'owner' && (int) $user->tenants_idTenant === (int) $tenant->idTenant;
        $isBookingOwner = (int) $booking->user_id === (int) $user->id;
        abort_unless($isOwnerOfTenant || $isBookingOwner, 403);

        if (in_array((string) $booking->status, ['cancelled', 'completed'], true)) {
            return back()->withErrors([
                'payment' => 'Booking tidak bisa dibuatkan tagihan pelunasan karena status saat ini tidak valid.',
            ]);
        }

        $this->syncBookingPaymentState($booking);
        $booking->refresh();

        $remainingAmount = $this->calculateRemainingAmount($booking);
        if ($remainingAmount <= 0) {
            return redirect()
                ->route('customer.profile')
                ->with('success', 'Booking ini sudah lunas.');
        }

        $remainingPaymentDeadline = $this->resolveRemainingPaymentDeadline($booking);
        if (!$remainingPaymentDeadline) {
            return back()->withErrors([
                'payment' => 'Batas waktu pelunasan tidak dapat dihitung dari jadwal booking ini.',
            ]);
        }

        if (now()->greaterThanOrEqualTo($remainingPaymentDeadline)) {
            return back()->withErrors([
                'payment' => 'Batas waktu pelunasan sudah lewat (15 menit sebelum jadwal selesai).',
            ]);
        }

        $pendingPayment = Payment::query()
            ->where('booking_idbooking', $booking->idbooking)
            ->where('status', 'pending')
            ->latest('idpayments')
            ->first();

        if ($pendingPayment) {
            return redirect()
                ->route('studios.payments.checkout', [
                    'tenant' => $tenant->slug,
                    'paymentId' => $pendingPayment->idpayments,
                ])
                ->with('success', 'Tagihan pending sudah tersedia. Lanjutkan pembayaran.');
        }

        $payment = Payment::query()->create([
            'method' => 'Midtrans',
            'status' => 'pending',
            'payment_type' => 'remaining',
            'amount' => $remainingAmount,
            'expires_time' => $remainingPaymentDeadline,
            'tenants_idTenant' => $tenant->idTenant,
            'booking_idbooking' => $booking->idbooking,
        ]);

        $isPaymentConfigured = $this->tenantMidtransService->hasActiveConfiguration($tenant);
        if ($isPaymentConfigured) {
            try {
                $snap = $this->tenantMidtransService->createSnapTransaction(
                    $tenant,
                    $booking,
                    $payment,
                    $user,
                    $remainingPaymentDeadline
                );

                $payment->forceFill([
                    'midtrans_order_id' => $snap['order_id'],
                    'snap_token' => $snap['token'],
                    'snap_redirect_url' => $snap['redirect_url'],
                    'status' => 'pending',
                ])->save();
            } catch (\Throwable $e) {
                report($e);

                $payment->forceFill([
                    'status' => 'failed',
                    'raw_status' => 'snap_create_failed_remaining',
                    'failed_at' => now(),
                ])->save();

                return back()->withErrors([
                    'payment' => 'Gagal membuat tagihan pelunasan Midtrans. Silakan coba lagi.',
                ]);
            }
        } else {
            $payment->forceFill([
                'raw_status' => 'tenant_midtrans_inactive',
            ])->save();
        }

        return redirect()
            ->route('studios.payments.checkout', [
                'tenant' => $tenant->slug,
                'paymentId' => $payment->idpayments,
            ])
            ->with('success', 'Tagihan pelunasan berhasil dibuat. Lanjutkan pembayaran.');
    }

    public function bypassSuccess(Tenant $tenant, int $paymentId, Request $request)
    {
        abort_unless($this->isMidtransBypassEnabled(), 403);

        $payment = Payment::query()
            ->where('idpayments', $paymentId)
            ->where('tenants_idTenant', $tenant->idTenant)
            ->firstOrFail();

        $booking = Booking::query()
            ->where('idbooking', $payment->booking_idbooking)
            ->firstOrFail();

        $user = $request->user();
        $isOwnerOfTenant = $user->role === 'owner' && (int) $user->tenants_idTenant === (int) $tenant->idTenant;
        $isBookingOwner = (int) $booking->user_id === (int) $user->id;
        abort_unless($isOwnerOfTenant || $isBookingOwner, 403);

        DB::connection('tenant')->transaction(function () use ($payment, $booking) {
            $now = now();

            $payment->forceFill([
                'status' => 'success',
                'raw_status' => 'bypass_button',
                'payment_time' => $payment->payment_time ?: $now,
                'paid_at' => $payment->paid_at ?: $now,
                'failed_at' => null,
            ])->save();

            if ($booking->status === 'pending') {
                $booking->update(['status' => 'confirmed']);
            }

            $this->syncBookingPaymentState($booking);
        });

        return response()->json([
            'message' => 'ok',
            'redirect_to' => route('customer.profile'),
        ]);
    }

    public function cancelBooking(Tenant $tenant, int $paymentId, Request $request)
    {
        $payment = Payment::query()
            ->where('idpayments', $paymentId)
            ->where('tenants_idTenant', $tenant->idTenant)
            ->firstOrFail();

        $booking = Booking::query()
            ->where('idbooking', $payment->booking_idbooking)
            ->firstOrFail();

        $user = $request->user();
        $isOwnerOfTenant = $user->role === 'owner' && (int) $user->tenants_idTenant === (int) $tenant->idTenant;
        $isBookingOwner = (int) $booking->user_id === (int) $user->id;
        abort_unless($isOwnerOfTenant || $isBookingOwner, 403);

        if (in_array((string) $booking->status, ['completed'], true) || $payment->status === 'success') {
            return back()->withErrors([
                'payment' => 'Booking yang sudah dibayar/selesai tidak bisa dibatalkan dari halaman ini.',
            ]);
        }

        if ($booking->status === 'cancelled') {
            return redirect()
                ->route('studios.show', $tenant->slug)
                ->with('success', 'Booking sudah dalam status dibatalkan.');
        }

        $booking->loadMissing('jadwal');

        DB::connection('tenant')->transaction(function () use ($booking, $tenant) {
            $now = now();

            $booking->update(['status' => 'cancelled']);

            Payment::query()
                ->where('booking_idbooking', $booking->idbooking)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'raw_status' => 'customer_cancelled_on_checkout',
                    'failed_at' => $now,
                    'updated_at' => $now,
                ]);

            $this->syncBookingPaymentState($booking);

            if ($booking->jadwal) {
                $this->scheduleAvailabilityService->recomputeRoomDate(
                    (int) $tenant->idTenant,
                    (int) $booking->jadwal->rooms_idrooms,
                    (string) $booking->jadwal->tanggal
                );
            }
        });

        return redirect()
            ->route('studios.show', $tenant->slug)
            ->with('success', 'Booking berhasil dibatalkan.');
    }

    public function midtransWebhook(Request $request)
    {
        $payload = $request->all();
        $orderId = (string) ($payload['order_id'] ?? '');

        if ($orderId === '') {
            return response()->json(['message' => 'order_id wajib'], 422);
        }

        $parsed = $this->tenantMidtransService->parseOrderId($orderId);
        if (!$parsed) {
            return response()->json(['message' => 'format order_id tidak valid'], 422);
        }

        $tenant = Tenant::query()->find($parsed['tenant_id']);
        if (!$tenant) {
            return response()->json(['message' => 'tenant tidak ditemukan'], 404);
        }

        $this->tenantDbManager->activateForTenant($tenant, true);

        if (!$this->tenantMidtransService->isValidSignature($tenant, $payload)) {
            return response()->json(['message' => 'signature tidak valid'], 403);
        }

        $this->tenantDbManager->runForTenant($tenant, function () use ($payload, $parsed, $orderId) {
            $payment = Payment::query()
                ->where('idpayments', $parsed['payment_id'])
                ->where('booking_idbooking', $parsed['booking_id'])
                ->first();

            if (!$payment) {
                $payment = Payment::query()
                    ->where('midtrans_order_id', $orderId)
                    ->first();
            }

            if (!$payment) {
                return;
            }

            $booking = Booking::query()
                ->where('idbooking', $payment->booking_idbooking)
                ->first();

            $mappedStatus = $this->tenantMidtransService->mapStatus(
                (string) ($payload['transaction_status'] ?? ''),
                (string) ($payload['fraud_status'] ?? '')
            );

            $now = now();
            $transactionTime = $payload['transaction_time'] ?? null;
            $expiryTime = $payload['expiry_time'] ?? null;

            $payment->forceFill([
                'method' => 'Midtrans',
                'midtrans_order_id' => $orderId,
                'midtrans_transaction_id' => $payload['transaction_id'] ?? $payment->midtrans_transaction_id,
                'status' => $mappedStatus,
                'raw_status' => $payload['transaction_status'] ?? $payment->raw_status,
                'webhook_payload' => json_encode($payload),
                'payment_time' => $mappedStatus === 'success'
                    ? ($transactionTime ?: $now)
                    : $payment->payment_time,
                'paid_at' => $mappedStatus === 'success'
                    ? $now
                    : $payment->paid_at,
                'failed_at' => in_array($mappedStatus, ['failed', 'expired', 'cancelled'], true)
                    ? $now
                    : $payment->failed_at,
                'expires_time' => $expiryTime ?: $payment->expires_time,
            ])->save();

            if (!$booking) {
                return;
            }

            $this->syncBookingPaymentState($booking);

            if ($mappedStatus === 'success' && $booking->status === 'pending') {
                $booking->update(['status' => 'confirmed']);
            }
        });

        return response()->json(['message' => 'ok']);
    }

    private function calculatePaidAmount(int $bookingId): float
    {
        return (float) Payment::query()
            ->where('booking_idbooking', $bookingId)
            ->where('status', 'success')
            ->sum('amount');
    }

    private function calculateRemainingAmount(Booking $booking): float
    {
        $total = (float) $booking->total_harga;
        $paid = $this->calculatePaidAmount((int) $booking->idbooking);

        return max(round($total - $paid, 2), 0);
    }

    private function syncBookingPaymentState(Booking $booking): void
    {
        $total = (float) $booking->total_harga;
        $paid = $this->calculatePaidAmount((int) $booking->idbooking);
        $normalizedPaid = min(max($paid, 0), $total);

        $state = 'unpaid';
        if ($normalizedPaid > 0 && $normalizedPaid < $total) {
            $state = 'partial';
        } elseif ($normalizedPaid >= $total && $total > 0) {
            $state = 'paid';
        }

        $booking->forceFill([
            'paid_amount' => round($normalizedPaid, 2),
            'payment_state' => $state,
        ])->save();
    }

    private function isMidtransBypassEnabled(): bool
    {
        if (!(bool) config('payment.midtrans_bypass_enabled', false)) {
            return false;
        }

        $allowedEnvs = collect(explode(',', (string) config('payment.midtrans_bypass_allowed_envs', 'local,testing')))
            ->map(fn (string $env) => trim($env))
            ->filter()
            ->values()
            ->all();

        return !empty($allowedEnvs) && app()->environment($allowedEnvs);
    }

    private function resolveRemainingPaymentDeadline(Booking $booking): ?Carbon
    {
        $booking->loadMissing('jadwal');
        $jadwal = $booking->jadwal;

        if (!$jadwal || !$jadwal->tanggal || !$jadwal->waktu_selesai) {
            return null;
        }

        try {
            return Carbon::parse($jadwal->tanggal.' '.$jadwal->waktu_selesai)->subMinutes(15);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolvePaymentTypeLabel(string $paymentType): string
    {
        return match ($paymentType) {
            'dp' => 'Pembayaran DP',
            'remaining' => 'Pelunasan sisa',
            default => 'Pembayaran lunas',
        };
    }

    private function resolveBookingPaymentSchemeLabel(Booking $booking): string
    {
        $scheme = (string) ($booking->payment_scheme ?? 'full');

        if ($scheme === 'dp') {
            $percent = (int) ($booking->dp_percent ?? 0);

            return $percent > 0
                ? 'DP '.$percent.'%'
                : 'DP';
        }

        return 'Lunas';
    }
}
