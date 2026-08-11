<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantProfileSynchronizer
{
    public function __construct(
        private readonly TenantDatabaseManager $tenantDbManager
    ) {
    }

    public function sync(Tenant $tenant): void
    {
        $this->tenantDbManager->runForTenant($tenant, function () use ($tenant) {
            $now = now();

            $payload = [
                'tenant_id' => $tenant->idTenant,
                'nama' => $tenant->nama,
                'slug' => $tenant->slug,
                'deskripsi' => $tenant->deskripsi,
                'nama_pemilik' => $tenant->nama_pemilik,
                'email' => $tenant->email,
                'no_telp' => $tenant->no_telp,
                'alamat' => $tenant->alamat,
                'provinsi' => $tenant->provinsi,
                'kota' => $tenant->kota,
                'kecamatan' => $tenant->kecamatan,
                'open_time' => $tenant->open_time,
                'close_time' => $tenant->close_time,
                'status' => $tenant->status ?? 'inactive',
                'source_updated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            DB::connection('tenant')->table('tenant_profiles')->upsert(
                [$payload],
                ['tenant_id'],
                [
                    'nama',
                    'slug',
                    'deskripsi',
                    'nama_pemilik',
                    'email',
                    'no_telp',
                    'alamat',
                    'provinsi',
                    'kota',
                    'kecamatan',
                    'open_time',
                    'close_time',
                    'status',
                    'source_updated_at',
                    'updated_at',
                ]
            );
        });
    }
}
