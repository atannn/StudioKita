<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Photo;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\TenantDatabaseManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudioController extends Controller
{
    public function __construct(
        private readonly TenantDatabaseManager $tenantDbManager
    ) {
    }

    public function index(Request $request)
    {
        $q = $request->query('q');
        $kota = $request->query('kota');

        $totalStudios = Tenant::query()
            ->where('status', 'active')
            ->count();

        $studios = Tenant::query()
            ->when($q, fn($qr) => $qr->where('nama', 'like', "%{$q}%"))
            ->when($kota, fn($qr) => $qr->where('kota', $kota))
            ->where('status', 'active')
            ->orderBy('nama')
            ->paginate(6)
            ->withQueryString();

        $this->attachTenantPublicData($studios->getCollection(), withServices: true);

        $availableCities = Tenant::query()
            ->where('status', 'active')
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct()
            ->orderBy('kota')
            ->pluck('kota');

        $popularCities = Tenant::query()
            ->select('kota', 'provinsi', DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->groupBy('kota', 'provinsi')
            ->orderByDesc('total')
            ->orderBy('kota')
            ->limit(2)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'filters' => view('studios.partials.filters', compact('q', 'kota'))->render(),
                'grid' => view('studios.partials.grid', compact('studios'))->render(),
                'pagination' => (string) $studios->links(),
            ]);
        }

        return view('studios.index', compact('studios', 'q', 'kota', 'availableCities', 'popularCities', 'totalStudios'));
    }

    public function catalog(Request $request)
    {
        $q = $request->query('q');
        $kota = $request->query('kota');
        $tipe = $request->query('tipe');
        $sort = $request->query('sort', 'name');

        $baseTenants = Tenant::query()
            ->when($q, fn($qr) => $qr->where('nama', 'like', "%{$q}%"))
            ->when($kota, fn($qr) => $qr->where('kota', $kota))
            ->where('status', 'active')
            ->get();

        $filtered = $baseTenants->filter(function (Tenant $tenant) use ($tipe) {
            if (!$tipe) {
                return true;
            }

            try {
                return $this->tenantDbManager->runForTenant($tenant, function () use ($tipe) {
                    return Service::query()
                        ->where('status', 1)
                        ->where('tipe_service', $tipe)
                        ->exists();
                });
            } catch (\Throwable $e) {
                return false;
            }
        });

        $sorted = match ($sort) {
            'newest' => $filtered->sortByDesc('createdAt')->values(),
            'name_desc' => $filtered->sortByDesc('nama')->values(),
            default => $filtered->sortBy('nama')->values(),
        };

        $studios = $this->paginateCollection($sorted, 9, $request);

        $this->attachTenantPublicData($studios->getCollection(), withServices: true);

        $popularCities = Tenant::query()
            ->select('kota', 'provinsi', DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->groupBy('kota', 'provinsi')
            ->orderByDesc('total')
            ->orderBy('kota')
            ->limit(4)
            ->get();

        return view('studios.catalog', compact('studios', 'q', 'kota', 'tipe', 'sort', 'popularCities'));
    }

    public function how()
    {
        return view('studios.how-it-works');
    }

    public function join()
    {
        $user = Auth::user();

        if ($user && $user->role === 'customer') {
            return redirect()->route('studios.index');
        }

        return view('studios.join');
    }

    public function show(Tenant $tenant, Request $request)
    {
        $this->tenantDbManager->activateForTenant($tenant);

        $primaryPhoto = Photo::query()
            ->where('is_primary', true)
            ->where('status', 1)
            ->latest('idfoto')
            ->first();

        $photos = Photo::query()
            ->where('status', 1)
            ->orderByDesc('is_primary')
            ->orderByDesc('idfoto')
            ->get();

        $services = Service::query()
            ->where('status', 1)
            ->orderBy('tipe_service')
            ->orderBy('nama_service')
            ->get();

        $rooms = Room::query()
            ->with(['facilities' => fn ($query) => $query
                ->where('status', 1)
                ->orderBy('nama_fasilitas')])
            ->where('status', 1)
            ->orderBy('tipe_ruangan')
            ->orderBy('nama_ruangan')
            ->get();

        $tenant->setRelation('primaryPhoto', $primaryPhoto);
        $tenant->setRelation('photos', $photos);
        $tenant->setRelation('services', $services);
        $tenant->setRelation('rooms', $rooms);

        $facilities = Facility::query()
            ->where('status', 1)
            ->orderBy('nama_fasilitas')
            ->paginate(5, ['*'], 'facilities_page')
            ->withQueryString()
            ->fragment('facilities');

        if ($request->ajax() && $request->has('facilities_page')) {
            return response()->json([
                'html' => view('studios.partials.facilities', compact('facilities'))->render(),
            ]);
        }

        return view('studios.show', compact('tenant', 'facilities'));
    }

    private function attachTenantPublicData(Collection $tenants, bool $withServices): void
    {
        $tenants->each(function (Tenant $tenant) use ($withServices) {
            try {
                $this->tenantDbManager->runForTenant($tenant, function () use ($tenant, $withServices) {
                    $primaryPhoto = Photo::query()
                        ->where('is_primary', true)
                        ->where('status', 1)
                        ->latest('idfoto')
                        ->first();

                    $tenant->setRelation('primaryPhoto', $primaryPhoto);

                    if ($withServices) {
                        $services = Service::query()
                            ->where('status', 1)
                            ->orderBy('tipe_service')
                            ->orderBy('nama_service')
                            ->get();

                        $tenant->setRelation('services', $services);
                    }
                });
            } catch (\Throwable $e) {
                $tenant->setRelation('primaryPhoto', null);
                if ($withServices) {
                    $tenant->setRelation('services', collect());
                }
            }
        });
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $total = $items->count();
        $results = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
