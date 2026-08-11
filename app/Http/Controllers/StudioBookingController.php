<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\ScheduleAvailabilityService;
use App\Support\TenantDatabaseManager;
use App\Support\TenantMidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudioBookingController extends Controller
{
    public function __construct(
        private readonly TenantMidtransService $tenantMidtransService,
        private readonly TenantDatabaseManager $tenantDbManager,
        private readonly ScheduleAvailabilityService $scheduleAvailabilityService
    ) {
    }

    public function create(Tenant $tenant)
    {
        $user = Auth::user();
        if ($user && $user->role === 'customer') {
            $lockMessage = $this->resolveOutstandingLockMessage((int) $user->id);
            if ($lockMessage) {
                return redirect()
                    ->route('customer.profile')
                    ->withErrors(['booking' => $lockMessage]);
            }
        }

        // Re-activate tenant target setelah possible scan lintas tenant.
        $this->tenantDbManager->activateForTenant($tenant, true);

        $rooms = Room::with(['facilities' => fn ($query) => $query
                ->where('status', 1)
                ->orderBy('nama_fasilitas')])
            ->where('tenants_idTenant', $tenant->idTenant)
            ->where('status', 1)
            ->orderBy('nama_ruangan')
            ->get();

        $services = Service::where('tenants_idTenant', $tenant->idTenant)
            ->where('status', 1)
            ->orderBy('nama_service')
            ->get();

        $dpPolicy = $this->tenantMidtransService->getDpPolicy($tenant);
        $cashPolicy = $this->tenantMidtransService->getCashPolicy($tenant);

        return view('studios.booking.create', compact('tenant', 'rooms', 'services', 'dpPolicy', 'cashPolicy'));
    }

    public function rooms(Tenant $tenant)
    {
        $rooms = Room::where('tenants_idTenant', $tenant->idTenant)
            ->where('status', 1)
            ->orderBy('nama_ruangan')
            ->get(['idrooms', 'nama_ruangan', 'tipe_ruangan']);

        return response()->json($rooms);
    }

    public function slots(Tenant $tenant, Request $request)
    {
        $request->validate([
            'service_idservice' => 'required|integer',
            'rooms_idrooms' => 'required|integer',
            'tanggal'       => 'required|date',
        ]);

        $service = Service::where('tenants_idTenant', $tenant->idTenant)
            ->where('idservice', $request->service_idservice)
            ->where('status', 1)
            ->first();

        $room = Room::where('tenants_idTenant', $tenant->idTenant)
            ->where('idrooms', $request->rooms_idrooms)
            ->where('status', 1)
            ->first();

        if (!$service || !$room || $service->tipe_service !== $room->tipe_ruangan) {
            return response()->json([]);
        }

        $this->scheduleAvailabilityService->recomputeRoomDate(
            (int) $tenant->idTenant,
            (int) $room->idrooms,
            (string) $request->tanggal
        );

        $slots = Jadwal::where('tenants_idTenant', $tenant->idTenant)
            ->where('rooms_idrooms', $request->rooms_idrooms)
            ->where('service_idservice', $service->idservice)
            ->where('tanggal', $request->tanggal)
            ->where('status', 'available')
            ->orderBy('waktu_mulai')
            ->get(['idJadwal', 'waktu_mulai', 'waktu_selesai']);

        return response()->json($slots);
    }

    public function store(Tenant $tenant, Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role === 'customer') {
            $lockMessage = $this->resolveOutstandingLockMessage((int) $user->id);
            if ($lockMessage) {
                return redirect()
                    ->route('studios.booking.create', $tenant->slug)
                    ->withErrors(['booking' => $lockMessage])
                    ->withInput();
            }
        }

        // Re-activate tenant target setelah possible scan lintas tenant.
        $this->tenantDbManager->activateForTenant($tenant, true);

        $dpPolicy = $this->tenantMidtransService->getDpPolicy($tenant);
        $cashPolicy = $this->tenantMidtransService->getCashPolicy($tenant);
        $allowedPaymentMethods = $cashPolicy['enabled'] ? ['midtrans', 'cash'] : ['midtrans'];

        $request->validate([
            'rooms_idrooms'     => 'required|integer',
            'service_idservice' => 'required|integer',
            'Jadwal_idJadwal'   => 'required|integer',
            'tanggal'           => 'nullable|date',
            'payment_method'    => ['required', Rule::in($allowedPaymentMethods)],
            'payment_scheme'    => 'required|string',
        ]);

        return DB::connection('tenant')->transaction(function () use ($request, $tenant, $dpPolicy, $cashPolicy) {
            // Ambil room milik tenant + aktif
            $room = Room::where('tenants_idTenant', $tenant->idTenant)
                ->where('idrooms', $request->rooms_idrooms)
                ->where('status', 1)
                ->firstOrFail();

            // Ambil service milik tenant + aktif
            $service = Service::where('tenants_idTenant', $tenant->idTenant)
                ->where('idservice', $request->service_idservice)
                ->where('status', 1)
                ->firstOrFail();

            // Validasi: tipe service harus match tipe room
            if ($service->tipe_service !== $room->tipe_ruangan) {
                return back()
                    ->withErrors(['rooms_idrooms' => 'Tipe room harus sesuai dengan tipe service (rekaman/latihan).'])
                    ->withInput();
            }

            // Lock slot jadwal
            $jadwal = Jadwal::where('tenants_idTenant', $tenant->idTenant)
                ->where('idJadwal', $request->Jadwal_idJadwal)
                ->lockForUpdate()
                ->firstOrFail();

            $this->scheduleAvailabilityService->recomputeRoomDate(
                (int) $tenant->idTenant,
                (int) $room->idrooms,
                (string) $jadwal->tanggal
            );

            $jadwal->refresh();

            // Slot harus available
            if ($jadwal->status !== 'available') {
                return back()
                    ->withErrors(['Jadwal_idJadwal' => 'Slot sudah tidak tersedia. Silakan pilih slot lain.'])
                    ->withInput();
            }

            // Hindari duplicate booking aktif untuk jadwal yang sama
            if (Booking::where('Jadwal_idJadwal', $jadwal->idJadwal)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists()) {
                return back()
                    ->withErrors(['Jadwal_idJadwal' => 'Slot sudah dibooking. Silakan pilih slot lain.'])
                    ->withInput();
            }

            // Jadwal harus untuk room yang dipilih
            if ((int)$jadwal->rooms_idrooms !== (int)$room->idrooms) {
                return back()
                    ->withErrors(['Jadwal_idJadwal' => 'Slot tidak sesuai dengan room yang dipilih.'])
                    ->withInput();
            }

            if ((int) $jadwal->service_idservice !== (int) $service->idservice) {
                return back()
                    ->withErrors(['Jadwal_idJadwal' => 'Slot tidak sesuai dengan service yang dipilih.'])
                    ->withInput();
            }

            if ($this->scheduleAvailabilityService->hasActiveBookingOverlap(
                (int) $tenant->idTenant,
                (int) $room->idrooms,
                (string) $jadwal->tanggal,
                (string) $jadwal->waktu_mulai,
                (string) $jadwal->waktu_selesai
            )) {
                return back()
                    ->withErrors(['Jadwal_idJadwal' => 'Ada booking aktif lain yang bentrok dengan slot ini. Silakan pilih slot lain.'])
                    ->withInput();
            }

            $paymentMethod = (string) $request->input('payment_method', 'midtrans');
            if (!$cashPolicy['enabled']) {
                $paymentMethod = 'midtrans';
            }

            $isCashPayment = $paymentMethod === 'cash';
            $allowedSchemes = !$isCashPayment && $dpPolicy['enabled'] ? ['full', 'dp'] : ['full'];
            $paymentScheme = (string) $request->input('payment_scheme', 'full');

            if (!in_array($paymentScheme, $allowedSchemes, true)) {
                return back()
                    ->withErrors(['payment_scheme' => 'Skema pembayaran tidak valid untuk metode yang dipilih.'])
                    ->withInput();
            }

            $dpPercent = !$isCashPayment && $paymentScheme === 'dp'
                ? (int) ($dpPolicy['percent'] ?? 0)
                : null;

            if ($paymentScheme === 'dp' && $dpPercent < 1) {
                return back()
                    ->withErrors(['payment_scheme' => 'Persentase DP belum dikonfigurasi oleh studio ini.'])
                    ->withInput();
            }

            $bookingTotal = (float) $service->getPriceForDate($jadwal->tanggal);
            $initialChargeAmount = !$isCashPayment && $paymentScheme === 'dp'
                ? max(1, round($bookingTotal * ($dpPercent / 100), 2))
                : $bookingTotal;

            // Buat booking
            $booking = Booking::create([
                'tanggal_booking'   => now(),
                'total_harga'       => $bookingTotal,
                'status'            => 'pending',
                'payment_scheme'    => $paymentScheme,
                'dp_percent'        => $dpPercent,
                'payment_state'     => 'unpaid',
                'paid_amount'       => 0,
                'tenants_idTenant'  => $tenant->idTenant,
                'rooms_idrooms'     => $room->idrooms,
                'service_idservice' => $service->idservice,
                'Jadwal_idJadwal'   => $jadwal->idJadwal,
                'user_id'           => Auth::id(),
            ]);

            $payment = Payment::query()->create([
                'method' => $isCashPayment ? 'Cash' : 'Midtrans',
                'status' => 'pending',
                'payment_type' => !$isCashPayment && $paymentScheme === 'dp' ? 'dp' : 'full',
                'amount' => $initialChargeAmount,
                'tenants_idTenant' => $tenant->idTenant,
                'booking_idbooking' => $booking->idbooking,
                'raw_status' => $isCashPayment ? 'cash_waiting_owner_confirmation' : null,
            ]);

            $this->scheduleAvailabilityService->recomputeRoomDate(
                (int) $tenant->idTenant,
                (int) $room->idrooms,
                (string) $jadwal->tanggal
            );

            if ($isCashPayment) {
                return redirect()
                    ->route('studios.payments.checkout', ['tenant' => $tenant->slug, 'paymentId' => $payment->idpayments])
                    ->with('success', 'Booking cash berhasil dibuat. Lakukan pembayaran langsung di studio saat hadir.');
            }

            $isPaymentConfigured = $this->tenantMidtransService->hasActiveConfiguration($tenant);

            if ($isPaymentConfigured) {
                try {
                    $snap = $this->tenantMidtransService->createSnapTransaction(
                        $tenant,
                        $booking,
                        $payment,
                        Auth::user()
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
                        'raw_status' => 'snap_create_failed',
                        'failed_at' => now(),
                    ])->save();

                    $booking->update(['status' => 'cancelled']);
                    $this->scheduleAvailabilityService->recomputeRoomDate(
                        (int) $tenant->idTenant,
                        (int) $room->idrooms,
                        (string) $jadwal->tanggal
                    );

                    return back()
                        ->withErrors(['payment' => $this->resolveMidtransErrorMessage($e)])
                        ->withInput();
                }
            } else {
                $payment->forceFill([
                    'raw_status' => 'tenant_midtrans_inactive',
                ])->save();
            }

            return redirect()
                ->route('studios.payments.checkout', ['tenant' => $tenant->slug, 'paymentId' => $payment->idpayments])
                ->with('success', $isPaymentConfigured
                    ? ($paymentScheme === 'dp'
                        ? 'Booking berhasil dibuat. Lanjutkan pembayaran DP.'
                        : 'Booking berhasil dibuat. Lanjutkan pembayaran.'
                    )
                    : 'Booking berhasil dibuat. Studio belum mengaktifkan pembayaran Midtrans.');
        });
    }

    private function resolveMidtransErrorMessage(\Throwable $e): string
    {
        $raw = strtolower($e->getMessage());

        if (str_contains($raw, 'unauthorized') || str_contains($raw, 'access denied')) {
            return 'Midtrans menolak kredensial. Periksa Server Key/Client Key dan pastikan mode Sandbox/Production sesuai.';
        }

        if (str_contains($raw, 'forbidden')) {
            return 'Akses ke Midtrans ditolak. Periksa status akun Midtrans studio ini.';
        }

        return 'Gagal membuat transaksi Midtrans. Silakan coba lagi.';
    }

    private function resolveOutstandingLockMessage(int $userId): ?string
    {
        $outstanding = $this->findFirstOutstandingBooking($userId);
        if (!$outstanding) {
            return null;
        }

        $remainingAmount = (float) ($outstanding['remaining_amount'] ?? 0);
        $tenantName = (string) ($outstanding['tenant_name'] ?? 'studio');
        $bookingId = (int) ($outstanding['booking_id'] ?? 0);

        return sprintf(
            'Anda masih memiliki tagihan Rp %s pada booking #%d di %s. Lunasi terlebih dahulu di menu Profil > Riwayat pesanan sebelum membuat booking baru.',
            number_format($remainingAmount, 0, ',', '.'),
            $bookingId,
            $tenantName
        );
    }

    /**
     * @return array{tenant_name:string,booking_id:int,remaining_amount:float}|null
     */
    private function findFirstOutstandingBooking(int $userId): ?array
    {
        $tenants = Tenant::query()
            ->select(['idTenant', 'nama', 'slug'])
            ->orderBy('idTenant')
            ->get();

        foreach ($tenants as $tenant) {
            try {
                $result = $this->tenantDbManager->runForTenant($tenant, function () use ($userId, $tenant) {
                    $bookings = Booking::query()
                        ->with([
                            'payments' => fn ($query) => $query->orderByDesc('idpayments'),
                        ])
                        ->where('user_id', $userId)
                        ->whereNotIn('status', ['cancelled', 'no_show'])
                        ->orderByDesc('tanggal_booking')
                        ->get();

                    foreach ($bookings as $booking) {
                        // Legacy/manual booking (tanpa jejak payment digital) tidak dipakai untuk lock.
                        if ($booking->payments->isEmpty()) {
                            continue;
                        }

                        $paidAmount = (float) $booking->payments
                            ->where('status', 'success')
                            ->sum('amount');
                        $totalHarga = (float) ($booking->total_harga ?? 0);
                        $remainingAmount = max(round($totalHarga - $paidAmount, 2), 0);
                        $hasCashOnlyPendingTrail = $booking->payments->every(function ($payment) {
                            return $payment->method === 'Cash'
                                && $payment->status !== 'success';
                        });

                        if ($remainingAmount > 0 && !$hasCashOnlyPendingTrail) {
                            return [
                                'tenant_name' => (string) $tenant->nama,
                                'booking_id' => (int) $booking->idbooking,
                                'remaining_amount' => $remainingAmount,
                            ];
                        }
                    }

                    return null;
                });
            } catch (\Throwable) {
                continue;
            }

            if ($result) {
                return $result;
            }
        }

        return null;
    }
}
