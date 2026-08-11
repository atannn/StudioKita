<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Payment;
use App\Support\ScheduleAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct(
        private readonly ScheduleAvailabilityService $scheduleAvailabilityService
    ) {
    }

    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        $status = $request->query('status');

        $query = Booking::with([
            'user',
            'room',
            'service',
            'jadwal',
            'payments' => fn ($query) => $query->orderByDesc('idpayments'),
        ])
            ->where('tenants_idTenant', $tenantId)
            ->orderByDesc('tanggal_booking');

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('owner.bookings.index', compact('bookings', 'status'));
    }

    public function confirm(int $id)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        $booking = $this->findBooking($tenantId, $id);
        $latestPayment = $this->latestPayment($booking);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Hanya booking pending yang bisa dikonfirmasi.');
        }

        $isCashPendingBooking = $latestPayment
            && $latestPayment->method === 'Cash'
            && $latestPayment->status !== 'success';

        if (!$isCashPendingBooking && $latestPayment && $latestPayment->status !== 'success' && (string) $booking->payment_state !== 'paid') {
            return back()->with('error', 'Booking belum dibayar. Tidak bisa dikonfirmasi manual.');
        }

        $booking->update(['status' => 'confirmed']);

        return back()->with('success', 'Booking berhasil dikonfirmasi.');
    }

    public function markNoShow(int $id)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        $ownerId = (int) Auth::id();
        $booking = $this->findBooking($tenantId, $id);
        $payment = $this->latestPayment($booking);

        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Hanya booking confirmed yang bisa ditandai tidak hadir.');
        }

        if (!$payment || $payment->method !== 'Cash') {
            return back()->with('error', 'Aksi tidak hadir hanya berlaku untuk booking cash.');
        }

        if ($payment->status === 'success') {
            return back()->with('error', 'Pembayaran cash sudah dikonfirmasi. Booking tidak bisa ditandai tidak hadir.');
        }

        DB::connection('tenant')->transaction(function () use ($booking, $payment, $tenantId, $ownerId) {
            $now = now();

            $booking->update(['status' => 'no_show']);

            $payment->forceFill([
                'status' => 'cancelled',
                'raw_status' => 'cash_no_show',
                'failed_at' => $now,
                'handled_by_user_id' => $ownerId,
                'handled_at' => $now,
                'payment_note' => 'Customer tidak hadir pada booking cash yang sudah dikonfirmasi.',
            ])->save();

            $this->syncBookingPaymentState($booking);
            $this->recomputeBookingSchedule($booking, $tenantId);
        });

        return back()->with('success', 'Booking cash ditandai tidak hadir.');
    }

    public function cancel(int $id)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        $ownerId = (int) Auth::id();
        $booking = $this->findBooking($tenantId, $id);

        if (!in_array((string) $booking->status, ['pending', 'confirmed'], true)) {
            return back()->with('error', 'Booking ini tidak bisa dibatalkan.');
        }

        DB::connection('tenant')->transaction(function () use ($booking, $tenantId, $ownerId) {
            $now = now();

            $booking->update(['status' => 'cancelled']);

            Payment::query()
                ->where('booking_idbooking', $booking->idbooking)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'raw_status' => 'owner_cancelled_booking',
                    'failed_at' => $now,
                    'handled_by_user_id' => $ownerId,
                    'handled_at' => $now,
                    'payment_note' => 'Booking dibatalkan oleh pemilik studio.',
                    'updated_at' => $now,
                ]);

            $this->syncBookingPaymentState($booking);
            $this->recomputeBookingSchedule($booking, $tenantId);
        });

        return back()->with('success', 'Booking dibatalkan dan jadwal dibuka kembali.');
    }

    public function complete(int $id)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        $ownerId = (int) Auth::id();
        $booking = $this->findBooking($tenantId, $id);
        $payment = $this->latestPayment($booking);

        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Hanya booking confirmed yang bisa diselesaikan.');
        }

        $isCashPendingBooking = $payment
            && $payment->method === 'Cash'
            && $payment->status !== 'success';

        if (!$isCashPendingBooking && $payment && $payment->status !== 'success' && (string) $booking->payment_state !== 'paid') {
            return back()->with('error', 'Booking belum dibayar. Selesaikan pembayaran terlebih dahulu.');
        }

        DB::connection('tenant')->transaction(function () use ($booking, $payment, $ownerId, $isCashPendingBooking) {
            $now = now();

            if ($isCashPendingBooking) {
                $payment->forceFill([
                    'status' => 'success',
                    'raw_status' => 'cash_paid_on_completion',
                    'payment_time' => $payment->payment_time ?: $now,
                    'paid_at' => $payment->paid_at ?: $now,
                    'failed_at' => null,
                    'handled_by_user_id' => $ownerId,
                    'handled_at' => $now,
                    'payment_note' => 'Pembayaran cash diakui saat booking ditandai selesai.',
                ])->save();

                $this->syncBookingPaymentState($booking);
            }

            $booking->update(['status' => 'completed']);
        });

        return back()->with('success', 'Booking ditandai selesai.');
    }

    private function findBooking(int $tenantId, int $bookingId): Booking
    {
        return Booking::with([
            'user',
            'room',
            'service',
            'jadwal',
            'payments' => fn ($query) => $query->orderByDesc('idpayments'),
        ])
            ->where('tenants_idTenant', $tenantId)
            ->where('idbooking', $bookingId)
            ->firstOrFail();
    }

    private function latestPayment(Booking $booking): ?Payment
    {
        return $booking->payments->first();
    }

    private function calculatePaidAmount(int $bookingId): float
    {
        return (float) Payment::query()
            ->where('booking_idbooking', $bookingId)
            ->where('status', 'success')
            ->sum('amount');
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

    private function recomputeBookingSchedule(Booking $booking, int $tenantId): void
    {
        $booking->loadMissing('jadwal');

        if (!$booking->jadwal) {
            return;
        }

        $this->scheduleAvailabilityService->recomputeRoomDate(
            $tenantId,
            (int) $booking->jadwal->rooms_idrooms,
            (string) $booking->jadwal->tanggal
        );
    }
}
