<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\ScheduleAvailabilityService;
use App\Support\TenantMidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(
        private readonly TenantMidtransService $tenantMidtransService,
        private readonly ScheduleAvailabilityService $scheduleAvailabilityService
    ) {
    }

    public function create()
    {
        // Route ini untuk owner yang punya tenant sendiri.
        // Customer booking melalui /studios/{tenant}/booking/create.
        $user = Auth::user();

        if ($user->role !== 'owner' || !$user->tenants_idTenant) {
            return redirect()->route('studios.index')
                ->with('info', 'Silakan pilih studio terlebih dahulu untuk melakukan booking.');
        }

        $tenantId = (int) $user->tenants_idTenant;

        $rooms = Room::query()
            ->with(['facilities' => fn ($query) => $query
                ->where('status', 1)
                ->orderBy('nama_fasilitas')])
            ->where('tenants_idTenant', $tenantId)
            ->where('status', 1)
            ->orderBy('nama_ruangan')
            ->get();

        $services = Service::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('status', 1)
            ->orderBy('nama_service')
            ->get();

        $tenant = Tenant::query()->findOrFail($tenantId);
        $dpPolicy = $this->tenantMidtransService->getDpPolicy($tenant);
        $cashPolicy = $this->tenantMidtransService->getCashPolicy($tenant);

        return view('booking.create', compact('rooms', 'services', 'dpPolicy', 'cashPolicy'));
    }

    // Untuk dropdown slot berdasarkan room + tanggal (untuk owner).
    public function slots(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'owner' || !$user->tenants_idTenant) {
            abort(403, 'Unauthorized');
        }

        $tenantId = (int) $user->tenants_idTenant;

        $request->validate([
            'service_idservice' => 'required|integer',
            'rooms_idrooms' => 'required|integer',
            'tanggal' => 'required|date',
        ]);

        $service = Service::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('idservice', $request->service_idservice)
            ->where('status', 1)
            ->first();

        $room = Room::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('idrooms', $request->rooms_idrooms)
            ->where('status', 1)
            ->first();

        if (!$service || !$room || $service->tipe_service !== $room->tipe_ruangan) {
            return response()->json([]);
        }

        $this->scheduleAvailabilityService->recomputeRoomDate(
            $tenantId,
            (int) $room->idrooms,
            (string) $request->tanggal
        );

        $slots = Jadwal::query()
            ->where('tenants_idTenant', $tenantId)
            ->where('rooms_idrooms', $request->rooms_idrooms)
            ->where('service_idservice', $service->idservice)
            ->where('tanggal', $request->tanggal)
            ->where('status', 'available')
            ->orderBy('waktu_mulai')
            ->get(['idJadwal', 'waktu_mulai', 'waktu_selesai']);

        return response()->json($slots);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'owner' || !$user->tenants_idTenant) {
            return redirect()->route('studios.index')
                ->with('error', 'Silakan booking melalui halaman studio.');
        }

        $tenantId = (int) $user->tenants_idTenant;

        $tenant = Tenant::query()->findOrFail($tenantId);
        $dpPolicy = $this->tenantMidtransService->getDpPolicy($tenant);
        $cashPolicy = $this->tenantMidtransService->getCashPolicy($tenant);
        $allowedPaymentMethods = $cashPolicy['enabled'] ? ['midtrans', 'cash'] : ['midtrans'];

        $request->validate([
            'rooms_idrooms' => 'required|integer',
            'service_idservice' => 'required|integer',
            'Jadwal_idJadwal' => 'required|integer',
            'tanggal' => 'nullable|date',
            'payment_method' => ['required', Rule::in($allowedPaymentMethods)],
            'payment_scheme' => 'required|string',
        ]);

        return DB::connection('tenant')->transaction(function () use ($request, $tenantId, $tenant, $dpPolicy, $cashPolicy) {
            $room = Room::query()
                ->where('tenants_idTenant', $tenantId)
                ->where('idrooms', $request->rooms_idrooms)
                ->where('status', 1)
                ->firstOrFail();

            $service = Service::query()
                ->where('tenants_idTenant', $tenantId)
                ->where('idservice', $request->service_idservice)
                ->where('status', 1)
                ->firstOrFail();

            if ($service->tipe_service !== $room->tipe_ruangan) {
                return back()
                    ->withErrors(['rooms_idrooms' => 'Tipe room harus sesuai dengan tipe service (rekaman/latihan).'])
                    ->withInput();
            }

            $jadwal = Jadwal::query()
                ->where('tenants_idTenant', $tenantId)
                ->where('idJadwal', $request->Jadwal_idJadwal)
                ->lockForUpdate()
                ->firstOrFail();

            $this->scheduleAvailabilityService->recomputeRoomDate(
                $tenantId,
                (int) $room->idrooms,
                (string) $jadwal->tanggal
            );

            $jadwal->refresh();

            if ($jadwal->status !== 'available') {
                return back()
                    ->withErrors(['Jadwal_idJadwal' => 'Slot sudah tidak tersedia. Silakan pilih slot lain.'])
                    ->withInput();
            }

            if (Booking::query()
                ->where('Jadwal_idJadwal', $jadwal->idJadwal)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists()) {
                return back()
                    ->withErrors(['Jadwal_idJadwal' => 'Slot sudah dibooking. Silakan pilih slot lain.'])
                    ->withInput();
            }

            if ((int) $jadwal->rooms_idrooms !== (int) $room->idrooms) {
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
                $tenantId,
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
                    ->withErrors(['payment_scheme' => 'Persentase DP belum dikonfigurasi oleh tenant ini.'])
                    ->withInput();
            }

            $bookingTotal = (float) $service->getPriceForDate($jadwal->tanggal);
            $initialChargeAmount = !$isCashPayment && $paymentScheme === 'dp'
                ? max(1, round($bookingTotal * ($dpPercent / 100), 2))
                : $bookingTotal;

            $booking = Booking::query()->create([
                'tanggal_booking' => now(),
                'total_harga' => $bookingTotal,
                'status' => 'pending',
                'payment_scheme' => $paymentScheme,
                'dp_percent' => $dpPercent,
                'payment_state' => 'unpaid',
                'paid_amount' => 0,
                'tenants_idTenant' => $tenantId,
                'rooms_idrooms' => $room->idrooms,
                'service_idservice' => $service->idservice,
                'Jadwal_idJadwal' => $jadwal->idJadwal,
                'user_id' => Auth::id(),
            ]);

            $payment = Payment::query()->create([
                'method' => $isCashPayment ? 'Cash' : 'Midtrans',
                'status' => 'pending',
                'payment_type' => !$isCashPayment && $paymentScheme === 'dp' ? 'dp' : 'full',
                'amount' => $initialChargeAmount,
                'tenants_idTenant' => $tenantId,
                'booking_idbooking' => $booking->idbooking,
                'raw_status' => $isCashPayment ? 'cash_waiting_owner_confirmation' : null,
            ]);

            $this->scheduleAvailabilityService->recomputeRoomDate(
                $tenantId,
                (int) $room->idrooms,
                (string) $jadwal->tanggal
            );

            if ($isCashPayment) {
                return redirect()
                    ->route('studios.payments.checkout', ['tenant' => $tenant->slug, 'paymentId' => $payment->idpayments])
                    ->with('success', 'Booking cash berhasil dibuat. Menunggu pembayaran langsung di studio.');
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
                        $tenantId,
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
                        : 'Booking berhasil dibuat. Lanjutkan pembayaran.')
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
}
