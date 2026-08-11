<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tenant;
use App\Support\TenantDatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private const LATEST_ACTIVITY_LIMIT = 2;

    public function __construct(
        private readonly TenantDatabaseManager $tenantDbManager
    ) {
    }

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'owner') {
            return redirect()->route('owner.dashboard');
        }

        if ($user->role === 'developer') {
            return redirect()->route('developer.dashboard');
        }

        $allBookings = $this->collectUserBookings((int) $user->id);
        $now = now();

        $upcomingBookings = $allBookings
            ->filter(function (Booking $booking) use ($now) {
                return $this->isActiveBooking($booking, $now);
            })
            ->take(3)
            ->values();

        $historyCollection = $allBookings->values();

        $historyBookings = $this->paginateCollection($historyCollection, 8, 'page');

        $latestBookings = $allBookings
            ->take(self::LATEST_ACTIVITY_LIMIT)
            ->values();

        $stats = [
            'total' => $allBookings->count(),
            'upcoming' => $allBookings
                ->filter(function (Booking $booking) use ($now) {
                    return $this->isActiveBooking($booking, $now);
                })
                ->count(),
            'completed' => $allBookings->where('status', 'completed')->count(),
            'spent' => $allBookings
                ->sum(fn (Booking $booking) => (float) ($booking->paid_amount ?? 0)),
        ];

        return view('customer.profile', [
            'user' => $user,
            'upcomingBookings' => $upcomingBookings,
            'historyBookings' => $historyBookings,
            'latestBookings' => $latestBookings,
            'stats' => $stats,
        ]);
    }

    private function collectUserBookings(int $userId): Collection
    {
        $tenants = Tenant::query()
            ->orderBy('idTenant')
            ->get();

        $bookings = collect();

        foreach ($tenants as $tenant) {
            try {
                $tenantBookings = $this->tenantDbManager->runForTenant($tenant, function () use ($userId) {
                    return Booking::query()
                        ->with([
                            'room',
                            'service',
                            'jadwal',
                            'payments' => fn ($query) => $query->orderByDesc('idpayments'),
                        ])
                        ->where('user_id', $userId)
                        ->orderByDesc('tanggal_booking')
                        ->get();
                });
            } catch (\Throwable $e) {
                continue;
            }

            $tenantBookings->each(function (Booking $booking) use ($tenant) {
                $paidAmount = (float) $booking->payments
                    ->where('status', 'success')
                    ->sum('amount');
                $totalHarga = (float) ($booking->total_harga ?? 0);
                $remainingAmount = max(round($totalHarga - $paidAmount, 2), 0);
                $remainingPaymentDeadline = $this->resolveRemainingPaymentDeadline($booking);
                $isRemainingPaymentDeadlinePassed = $remainingPaymentDeadline
                    ? now()->greaterThanOrEqualTo($remainingPaymentDeadline)
                    : false;

                $pendingPayment = $booking->payments->first(
                    fn ($payment) => $payment->method !== 'Cash'
                        && in_array($payment->status, ['pending', 'failed', 'expired', 'cancelled'], true)
                );
                $pendingPaymentIsRemaining = (string) ($pendingPayment?->payment_type ?? '') === 'remaining';
                $pendingRemainingExpiredByDeadline = $pendingPaymentIsRemaining
                    && $isRemainingPaymentDeadlinePassed;

                $isPayableBooking = in_array((string) $booking->status, ['pending', 'confirmed'], true);
                $canContinuePayment = $isPayableBooking
                    && $remainingAmount > 0
                    && !empty($pendingPayment?->idpayments)
                    && !$pendingRemainingExpiredByDeadline;
                $canCreateRemainingPayment = $isPayableBooking
                    && $remainingAmount > 0
                    && $paidAmount > 0
                    && empty($pendingPayment?->idpayments)
                    && !$isRemainingPaymentDeadlinePassed;

                $booking->setRelation('tenant', $tenant);
                $booking->tenants_idTenant = (int) $tenant->idTenant;
                $booking->paid_amount = $paidAmount;
                $booking->remaining_amount = $remainingAmount;
                $booking->checkout_payment_id = $pendingPayment?->idpayments;
                $booking->can_continue_payment = $canContinuePayment;
                $booking->can_create_remaining_payment = $canCreateRemainingPayment;
                $booking->payment_action_label = $canCreateRemainingPayment
                    ? 'Lunasi sisa'
                    : 'Lanjutkan pembayaran';
                $booking->checkout_action_method = $canCreateRemainingPayment ? 'POST' : 'GET';
                $booking->checkout_url = $canContinuePayment
                    ? route('studios.payments.checkout', [
                        'tenant' => $tenant->slug,
                        'paymentId' => $pendingPayment->idpayments,
                    ])
                    : ($canCreateRemainingPayment
                        ? route('studios.payments.remaining.create', [
                            'tenant' => $tenant->slug,
                            'bookingId' => $booking->idbooking,
                        ])
                        : null);
                $booking->can_do_payment_action = $canContinuePayment || $canCreateRemainingPayment;
                $booking->payment_action_is_post = $canCreateRemainingPayment;
                $booking->payment_action_hint = $canCreateRemainingPayment
                    ? 'Buat tagihan pelunasan dari sisa pembayaran.'
                    : null;
                $booking->remaining_payment_deadline_at = $remainingPaymentDeadline;
                $booking->remaining_payment_deadline_passed = $isRemainingPaymentDeadlinePassed;
            });

            $bookings = $bookings->merge($tenantBookings);
        }

        return $bookings
            ->sortByDesc(fn (Booking $booking) => $this->bookingDate($booking)->timestamp)
            ->values();
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $results = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => $pageName,
            ]
        );
    }

    private function bookingDate(Booking $booking): Carbon
    {
        return Carbon::parse($booking->tanggal_booking);
    }

    private function bookingScheduleEndAt(Booking $booking): ?Carbon
    {
        $jadwal = $booking->jadwal;

        if (!$jadwal || !$jadwal->tanggal || !$jadwal->waktu_selesai) {
            return null;
        }

        try {
            return Carbon::parse($jadwal->tanggal.' '.$jadwal->waktu_selesai);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isActiveBooking(Booking $booking, Carbon $referenceTime): bool
    {
        if (!in_array((string) $booking->status, ['pending', 'confirmed'], true)) {
            return false;
        }

        $scheduleEnd = $this->bookingScheduleEndAt($booking);
        if ($scheduleEnd) {
            return $scheduleEnd->gte($referenceTime);
        }

        // Fallback untuk data lama yang mungkin belum punya relasi jadwal valid.
        return $this->bookingDate($booking)->gte($referenceTime);
    }

    private function isPastBookingSchedule(Booking $booking, Carbon $referenceTime): bool
    {
        $scheduleEnd = $this->bookingScheduleEndAt($booking);
        if ($scheduleEnd) {
            return $scheduleEnd->lt($referenceTime);
        }

        // Fallback untuk data lama yang mungkin belum punya relasi jadwal valid.
        return $this->bookingDate($booking)->lt($referenceTime);
    }

    private function resolveRemainingPaymentDeadline(Booking $booking): ?Carbon
    {
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
}
