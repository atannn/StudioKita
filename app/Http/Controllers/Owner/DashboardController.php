<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\TenantMidtransService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private readonly TenantMidtransService $tenantMidtransService
    ) {
    }

    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenants_idTenant;
        if (!$tenantId) {
            return redirect()->route('owner.setup.step1');
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return redirect()->route('owner.setup.step1');
        }

        $tenant->rooms_count = Room::query()
            ->where('tenants_idTenant', $tenantId)
            ->count();
        $tenant->services_count = Service::query()
            ->where('tenants_idTenant', $tenantId)
            ->count();
        $tenant->facilities_count = Facility::query()
            ->where('tenants_idTenant', $tenantId)
            ->count();
        $verificationProgress = $this->resolveVerificationProgress($tenant);

        $range = $request->query('range');
        $startInput = $request->query('start');
        $endInput = $request->query('end');

        if ($startInput && $endInput) {
            $startDate = Carbon::parse($startInput)->startOfDay();
            $endDate = Carbon::parse($endInput)->endOfDay();
        } else {
            $endDate = Carbon::now()->endOfDay();
            $startDate = match ($range) {
                'today' => Carbon::now()->startOfDay(),
                '7d' => Carbon::now()->subDays(6)->startOfDay(),
                '30d' => Carbon::now()->subDays(29)->startOfDay(),
                default => Carbon::now()->subDays(29)->startOfDay(),
            };
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $topMonth = $request->query('top_month');
        try {
            $topStart = $topMonth
                ? Carbon::createFromFormat('Y-m', $topMonth)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Throwable $e) {
            $topStart = Carbon::now()->startOfMonth();
        }
        $topEnd = $topStart->copy()->endOfMonth();

        $bookingQuery = Booking::where('tenants_idTenant', $tenantId)
            ->whereBetween('tanggal_booking', [$startDate, $endDate]);

        $successfulPaymentQuery = Payment::query()
            ->join('bookings', 'payments.booking_idbooking', '=', 'bookings.idbooking')
            ->where('payments.tenants_idTenant', $tenantId)
            ->whereBetween('bookings.tanggal_booking', [$startDate, $endDate])
            ->where('payments.status', 'success');

        $metrics = [
            'total' => (clone $bookingQuery)->count(),
            'active' => (clone $bookingQuery)->whereIn('status', ['pending', 'confirmed'])->count(),
            'completed' => (clone $bookingQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $bookingQuery)->where('status', 'cancelled')->count(),
            'revenue' => (clone $successfulPaymentQuery)->sum('payments.amount'),
        ];

        $dailyRevenue = (clone $successfulPaymentQuery)
            ->selectRaw('DATE(bookings.tanggal_booking) as date')
            ->selectRaw('SUM(payments.amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $daily = (clone $bookingQuery)
            ->selectRaw('DATE(tanggal_booking) as date')
            ->selectRaw("COUNT(*) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $counts = [];
        $revenues = [];

        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());
        foreach ($period as $date) {
            $key = $date->toDateString();
            $row = $daily->get($key);
            $revenueRow = $dailyRevenue->get($key);
            $labels[] = $date->translatedFormat('d M');
            $counts[] = (int) ($row->total ?? 0);
            $revenues[] = round(((float) ($revenueRow->revenue ?? 0)) / 1000000, 2);
        }

        $bookingByType = Booking::query()
            ->join('rooms', 'bookings.rooms_idrooms', '=', 'rooms.idrooms')
            ->where('bookings.tenants_idTenant', $tenantId)
            ->whereBetween('bookings.tanggal_booking', [$startDate, $endDate])
            ->selectRaw('rooms.tipe_ruangan as label, COUNT(*) as total')
            ->groupBy('rooms.tipe_ruangan')
            ->orderBy('label')
            ->get();

        $bookingByRoom = Booking::query()
            ->join('rooms', 'bookings.rooms_idrooms', '=', 'rooms.idrooms')
            ->where('bookings.tenants_idTenant', $tenantId)
            ->whereBetween('bookings.tanggal_booking', [$startDate, $endDate])
            ->selectRaw('rooms.nama_ruangan as label, COUNT(*) as total')
            ->groupBy('rooms.nama_ruangan')
            ->orderBy('label')
            ->get();

        $revenueByType = Payment::query()
            ->join('bookings', 'payments.booking_idbooking', '=', 'bookings.idbooking')
            ->join('rooms', 'bookings.rooms_idrooms', '=', 'rooms.idrooms')
            ->where('payments.tenants_idTenant', $tenantId)
            ->whereBetween('bookings.tanggal_booking', [$startDate, $endDate])
            ->where('payments.status', 'success')
            ->selectRaw('rooms.tipe_ruangan as label, SUM(payments.amount) as total')
            ->groupBy('rooms.tipe_ruangan')
            ->orderBy('label')
            ->get();

        $revenueByRoom = Payment::query()
            ->join('bookings', 'payments.booking_idbooking', '=', 'bookings.idbooking')
            ->join('rooms', 'bookings.rooms_idrooms', '=', 'rooms.idrooms')
            ->where('payments.tenants_idTenant', $tenantId)
            ->whereBetween('bookings.tanggal_booking', [$startDate, $endDate])
            ->where('payments.status', 'success')
            ->selectRaw('rooms.nama_ruangan as label, SUM(payments.amount) as total')
            ->groupBy('rooms.nama_ruangan')
            ->orderBy('label')
            ->get();

        $topServices = Booking::query()
            ->join('services', 'bookings.service_idservice', '=', 'services.idservice')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.booking_idbooking', '=', 'bookings.idbooking')
                    ->where('payments.status', '=', 'success');
            })
            ->where('bookings.tenants_idTenant', $tenantId)
            ->whereBetween('bookings.tanggal_booking', [$topStart, $topEnd])
            ->selectRaw('services.nama_service as label, COUNT(DISTINCT bookings.idbooking) as total')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as revenue')
            ->groupBy('services.nama_service')
            ->orderByDesc('total')
            ->limit(1)
            ->get();

        $topRooms = Booking::query()
            ->join('rooms', 'bookings.rooms_idrooms', '=', 'rooms.idrooms')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.booking_idbooking', '=', 'bookings.idbooking')
                    ->where('payments.status', '=', 'success');
            })
            ->where('bookings.tenants_idTenant', $tenantId)
            ->whereBetween('bookings.tanggal_booking', [$topStart, $topEnd])
            ->selectRaw('rooms.nama_ruangan as label, COUNT(DISTINCT bookings.idbooking) as total')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as revenue')
            ->groupBy('rooms.nama_ruangan')
            ->orderByDesc('total')
            ->limit(1)
            ->get();

        $viewData = [
            'tenant' => $tenant,
            'verificationProgress' => $verificationProgress,
            'metrics' => $metrics,
            'chartLabels' => $labels,
            'chartCounts' => $counts,
            'chartRevenues' => $revenues,
            'bookingByTypeLabels' => $bookingByType->pluck('label')->map(fn ($v) => ucfirst($v ?? 'Lainnya'))->values(),
            'bookingByTypeCounts' => $bookingByType->pluck('total')->map(fn ($v) => (int) $v)->values(),
            'bookingByRoomLabels' => $bookingByRoom->pluck('label')->values(),
            'bookingByRoomCounts' => $bookingByRoom->pluck('total')->map(fn ($v) => (int) $v)->values(),
            'revenueByTypeLabels' => $revenueByType->pluck('label')->map(fn ($v) => ucfirst($v ?? 'Lainnya'))->values(),
            'revenueByTypeValues' => $revenueByType->pluck('total')->map(fn ($v) => round(((float) $v) / 1000000, 2))->values(),
            'revenueByRoomLabels' => $revenueByRoom->pluck('label')->values(),
            'revenueByRoomValues' => $revenueByRoom->pluck('total')->map(fn ($v) => round(((float) $v) / 1000000, 2))->values(),
            'topServices' => $topServices,
            'topRooms' => $topRooms,
            'topMonth' => $topStart->format('Y-m'),
            'filterStart' => $startDate->toDateString(),
            'filterEnd' => $endDate->toDateString(),
        ];

        if ($request->query('partial') === 'analytics') {
            return view('owner.dashboard.partials.analytics', $viewData);
        }

        if ($request->query('partial') === 'top') {
            return view('owner.dashboard.partials.top-card', $viewData);
        }

        return view('owner.dashboard', $viewData);
    }

    private function resolveVerificationProgress(Tenant $tenant): array
    {
        $basicVerified = in_array((string) $tenant->verification_level, ['basic_verified', 'verified'], true);
        $verified = (string) $tenant->verification_level === 'verified'
            && (string) $tenant->verification_status === 'approved';
        $paymentVerified = $verified
            && $this->tenantMidtransService->hasReadyActivationConfiguration($tenant);

        $completed = collect([$basicVerified, $verified, $paymentVerified])
            ->filter()
            ->count();

        return [
            'basic_verified' => $basicVerified,
            'verified' => $verified,
            'payment_verified' => $paymentVerified,
            'completed' => $completed,
            'total' => 3,
            'percentage' => (int) round(($completed / 3) * 100),
        ];
    }
}
