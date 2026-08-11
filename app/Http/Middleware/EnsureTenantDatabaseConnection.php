<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantDatabaseConnection
{
    public function __construct(
        private readonly TenantDatabaseManager $tenantDbManager
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        if (is_string($tenant) && $tenant !== '') {
            $tenant = Tenant::query()
                ->where('slug', $tenant)
                ->first();
        }

        if (!$tenant instanceof Tenant) {
            $user = $request->user();
            if ($user && $user->role === 'owner' && $user->tenants_idTenant) {
                $tenant = Tenant::query()->find($user->tenants_idTenant);
            }
        }

        if ($tenant instanceof Tenant) {
            $this->tenantDbManager->activateForTenant($tenant, true);
        }

        return $next($request);
    }
}

