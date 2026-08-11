<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Announcement;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private readonly TenantDatabaseManager $tenantDbManager
    ) {
    }

    public function index(Request $request)
    {
        $query = Tenant::query()
            ->orderByDesc('createdAt');

        $search = $request->query('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nama_pemilik', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $verificationStatus = $request->query('verification_status');
        if ($verificationStatus) {
            $query->where('verification_status', $verificationStatus);
        }

        $kota = $request->query('kota');
        if ($kota) {
            $query->where('kota', $kota);
        }

        $tenants = $query->paginate(10)->withQueryString();

        $cities = Tenant::query()
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct()
            ->orderBy('kota')
            ->pluck('kota');

        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $ownerCount = User::where('role', 'owner')->count();
        $pendingManualVerifications = Tenant::where('verification_status', 'pending')->count();

        $now = Carbon::now();
        $bookingThisMonth = 0;
        $completeStudios = 0;
        $missingDescription = 0;
        $missingPhotos = 0;
        $missingRooms = 0;

        $metricTenants = Tenant::query()
            ->orderBy('idTenant')
            ->get(['idTenant', 'slug', 'deskripsi']);

        foreach ($metricTenants as $tenant) {
            $hasDescription = trim((string) $tenant->deskripsi) !== '';
            if (!$hasDescription) {
                $missingDescription++;
            }

            $monthlyBookings = 0;
            $galleryCount = 0;
            $roomCount = 0;

            try {
                $this->tenantDbManager->runForTenant($tenant, function () use ($now, &$monthlyBookings, &$galleryCount, &$roomCount) {
                    $monthlyBookings = Booking::query()
                        ->whereYear('tanggal_booking', $now->year)
                        ->whereMonth('tanggal_booking', $now->month)
                        ->count();

                    $galleryCount = Photo::query()
                        ->where('is_primary', false)
                        ->where('status', 1)
                        ->count();

                    $roomCount = Room::query()->count();
                });
            } catch (\Throwable $e) {
                $monthlyBookings = 0;
                $galleryCount = 0;
                $roomCount = 0;
            }

            $bookingThisMonth += $monthlyBookings;

            if ($galleryCount === 0) {
                $missingPhotos++;
            }

            if ($roomCount === 0) {
                $missingRooms++;
            }

            if ($hasDescription && $galleryCount >= 3 && $roomCount > 0) {
                $completeStudios++;
            }
        }

        $announcements = Announcement::orderByDesc('created_at')->limit(5)->get();

        return view('developer.dashboard', [
            'tenants' => $tenants,
            'cities' => $cities,
            'totalTenants' => $totalTenants,
            'activeTenants' => $activeTenants,
            'ownerCount' => $ownerCount,
            'bookingThisMonth' => $bookingThisMonth,
            'completeStudios' => $completeStudios,
            'missingDescription' => $missingDescription,
            'missingPhotos' => $missingPhotos,
            'missingRooms' => $missingRooms,
            'announcements' => $announcements,
            'pendingManualVerifications' => $pendingManualVerifications,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'kota' => $kota,
                'verification_status' => $verificationStatus,
            ],
        ]);
    }
}
