<?php

use App\Models\Tenant;
use App\Support\TenantDatabaseManager;
use App\Support\TenantProfileSynchronizer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tenants:backfill-databases {--tenant= : ID tenant tertentu}', function () {
    /** @var TenantDatabaseManager $tenantDbManager */
    $tenantDbManager = app(TenantDatabaseManager::class);
    /** @var TenantProfileSynchronizer $tenantProfileSynchronizer */
    $tenantProfileSynchronizer = app(TenantProfileSynchronizer::class);

    $tenantId = $this->option('tenant');

    $tenantQuery = Tenant::query()->orderBy('idTenant');
    if ($tenantId) {
        $tenantQuery->where('idTenant', (int) $tenantId);
    }

    $tenants = $tenantQuery->get();

    if ($tenants->isEmpty()) {
        $this->warn('Tenant tidak ditemukan.');
        return Command::SUCCESS;
    }

    $centralConnection = DB::connection(config('database.default'));
    $centralSchema = $centralConnection->getSchemaBuilder();

    $tables = [
        ['name' => 'rooms', 'pk' => 'idrooms'],
        ['name' => 'services', 'pk' => 'idservice'],
        ['name' => 'facilities', 'pk' => 'idfasiltas'],
        ['name' => 'photos', 'pk' => 'idfoto'],
        ['name' => 'jadwals', 'pk' => 'idJadwal'],
        ['name' => 'bookings', 'pk' => 'idbooking'],
        ['name' => 'payments', 'pk' => 'idpayments'],
        ['name' => 'operasionals', 'pk' => 'idoperasional'],
    ];

    foreach ($tenants as $tenant) {
        $this->info("Memproses tenant #{$tenant->idTenant} ({$tenant->nama})");

        $tenantDbManager->activateForTenant($tenant, true);
        $tenantConnection = DB::connection('tenant');

        foreach ($tables as $tableConfig) {
            $tableName = $tableConfig['name'];
            $primaryKey = $tableConfig['pk'];

            if (!$centralSchema->hasTable($tableName)) {
                $this->line("  - {$tableName}: tabel tidak ada di central DB, skip");
                continue;
            }

            $rows = $centralConnection->table($tableName)
                ->where('tenants_idTenant', $tenant->idTenant)
                ->orderBy($primaryKey)
                ->get();

            if ($rows->isEmpty()) {
                $this->line("  - {$tableName}: 0 data");
                continue;
            }

            $payload = $rows->map(fn ($row) => (array) $row)->all();
            $updateColumns = array_values(array_diff(array_keys($payload[0]), [$primaryKey]));

            $tenantConnection->table($tableName)->upsert(
                $payload,
                [$primaryKey],
                $updateColumns
            );

            $this->line("  - {$tableName}: ".count($payload)." data tersalin");
        }

        $tenantProfileSynchronizer->sync($tenant);
        $this->line('  - tenant_profiles: profil studio tersinkron');
    }

    $this->info('Backfill data tenant selesai.');
    return Command::SUCCESS;
})->purpose('Salin data tenant lama dari central DB ke database per tenant');

Artisan::command('tenants:phase15-drop-central-payment-accounts {--force-drop : Drop tabel pusat setelah checklist lolos}', function () {
    /** @var TenantDatabaseManager $tenantDbManager */
    $tenantDbManager = app(TenantDatabaseManager::class);

    $centralConnection = DB::connection(config('database.default'));
    $centralSchema = $centralConnection->getSchemaBuilder();
    $tableName = 'tenant_payment_accounts';

    if (!$centralSchema->hasTable($tableName)) {
        $this->info('Checklist: tabel pusat tenant_payment_accounts sudah tidak ada.');
        return Command::SUCCESS;
    }

    $tenants = Tenant::query()
        ->orderBy('idTenant')
        ->get(['idTenant', 'slug', 'nama']);

    $tenantIds = $tenants->pluck('idTenant')->map(fn ($id) => (int) $id);
    $legacyRows = $centralConnection->table($tableName)->orderBy('tenant_id')->get();
    $legacyByTenant = $legacyRows->keyBy('tenant_id');
    $orphanLegacyCount = $legacyRows
        ->filter(fn ($row) => !$tenantIds->contains((int) $row->tenant_id))
        ->count();

    $checkRows = [];
    $issues = [];

    foreach ($tenants as $tenant) {
        $tenantHasTable = false;
        $tenantHasAccount = false;
        $legacyHasAccount = $legacyByTenant->has((int) $tenant->idTenant);
        $errorMessage = null;

        try {
            $tenantDbManager->activateForTenant($tenant, true);
            $tenantConnection = DB::connection('tenant');
            $tenantSchema = $tenantConnection->getSchemaBuilder();

            $tenantHasTable = $tenantSchema->hasTable($tableName);
            if ($tenantHasTable) {
                $tenantHasAccount = $tenantConnection
                    ->table($tableName)
                    ->where('tenant_id', (int) $tenant->idTenant)
                    ->exists();
            }

            if (!$tenantHasTable) {
                $issues[] = "Tenant #{$tenant->idTenant} ({$tenant->slug}) belum punya tabel {$tableName} di DB tenant.";
            } elseif ($legacyHasAccount && !$tenantHasAccount) {
                $issues[] = "Tenant #{$tenant->idTenant} ({$tenant->slug}) punya data lama di pusat tapi belum ada di DB tenant.";
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $issues[] = "Tenant #{$tenant->idTenant} ({$tenant->slug}) gagal diverifikasi: {$errorMessage}";
        }

        $checkRows[] = [
            'tenant_id' => (string) $tenant->idTenant,
            'slug' => (string) ($tenant->slug ?? '-'),
            'legacy_center' => $legacyHasAccount ? 'yes' : 'no',
            'tenant_table' => $tenantHasTable ? 'yes' : 'no',
            'tenant_row' => $tenantHasAccount ? 'yes' : 'no',
            'status' => $errorMessage ? 'error' : 'ok',
        ];
    }

    $this->info('Checklist Verifikasi Phase 1.5:');
    $this->line('1) Tabel pusat tenant_payment_accounts: ditemukan');
    $this->line('2) Jumlah baris di pusat: '.$legacyRows->count());
    $this->line('3) Orphan baris tenant_id (tenant sudah tidak ada): '.$orphanLegacyCount);
    $this->line('4) Verifikasi per tenant:');
    $this->table(
        ['tenant_id', 'slug', 'legacy_center', 'tenant_table', 'tenant_row', 'status'],
        $checkRows
    );

    if (!empty($issues)) {
        $this->error('Checklist gagal. Perbaiki isu berikut sebelum drop:');
        foreach ($issues as $issue) {
            $this->line('- '.$issue);
        }

        return Command::FAILURE;
    }

    $this->info('Checklist lolos. Tidak ada data krusial yang tertinggal di pusat.');

    if (!$this->option('force-drop')) {
        $this->warn('Mode dry-run. Tabel belum di-drop. Jalankan ulang dengan --force-drop untuk eksekusi drop.');
        return Command::SUCCESS;
    }

    $centralSchema->drop($tableName);
    $this->info('Tabel pusat tenant_payment_accounts berhasil di-drop.');

    return Command::SUCCESS;
})->purpose('Checklist + drop aman tabel pusat tenant_payment_accounts setelah migrasi ke DB tenant');

Artisan::command('tenants:backfill-email-otps {--tenant= : ID tenant tertentu}', function () {
    /** @var TenantDatabaseManager $tenantDbManager */
    $tenantDbManager = app(TenantDatabaseManager::class);

    $centralConnection = DB::connection(config('database.default'));
    $centralSchema = $centralConnection->getSchemaBuilder();
    $tableName = 'tenant_email_otps';

    if (!$centralSchema->hasTable($tableName)) {
        $this->warn('Tabel pusat tenant_email_otps tidak ditemukan. Tidak ada data untuk dibackfill.');
        return Command::SUCCESS;
    }

    $tenantId = $this->option('tenant');

    $tenantQuery = Tenant::query()->orderBy('idTenant');
    if ($tenantId) {
        $tenantQuery->where('idTenant', (int) $tenantId);
    }

    $tenants = $tenantQuery->get(['idTenant', 'slug', 'nama']);

    if ($tenants->isEmpty()) {
        $this->warn('Tenant tidak ditemukan.');
        return Command::SUCCESS;
    }

    $hasError = false;

    foreach ($tenants as $tenant) {
        $this->info("Memproses tenant #{$tenant->idTenant} ({$tenant->slug})");

        try {
            $tenantDbManager->activateForTenant($tenant, true);
            $tenantConnection = DB::connection('tenant');
            $tenantSchema = $tenantConnection->getSchemaBuilder();

            if (!$tenantSchema->hasTable($tableName)) {
                $this->error("  - tabel {$tableName} belum ada di DB tenant.");
                $hasError = true;
                continue;
            }

            $rows = $centralConnection->table($tableName)
                ->where('tenant_id', (int) $tenant->idTenant)
                ->orderBy('id')
                ->get();

            if ($rows->isEmpty()) {
                $this->line("  - {$tableName}: 0 data");
                continue;
            }

            $payload = $rows->map(fn ($row) => (array) $row)->all();
            $updateColumns = array_values(array_diff(array_keys($payload[0]), ['id']));

            $tenantConnection->table($tableName)->upsert(
                $payload,
                ['id'],
                $updateColumns
            );

            $this->line("  - {$tableName}: ".count($payload).' data tersalin');
        } catch (\Throwable $e) {
            $hasError = true;
            $this->error("  - gagal backfill: {$e->getMessage()}");
        }
    }

    if ($hasError) {
        $this->error('Backfill tenant_email_otps selesai dengan error.');
        return Command::FAILURE;
    }

    $this->info('Backfill tenant_email_otps selesai.');
    return Command::SUCCESS;
})->purpose('Salin tenant_email_otps dari DB pusat ke DB tenant');

Artisan::command('tenants:phase16-drop-central-email-otps {--force-drop : Drop tabel pusat setelah checklist lolos}', function () {
    /** @var TenantDatabaseManager $tenantDbManager */
    $tenantDbManager = app(TenantDatabaseManager::class);

    $centralConnection = DB::connection(config('database.default'));
    $centralSchema = $centralConnection->getSchemaBuilder();
    $tableName = 'tenant_email_otps';

    if (!$centralSchema->hasTable($tableName)) {
        $this->info('Checklist: tabel pusat tenant_email_otps sudah tidak ada.');
        return Command::SUCCESS;
    }

    $tenants = Tenant::query()
        ->orderBy('idTenant')
        ->get(['idTenant', 'slug', 'nama']);

    $tenantIds = $tenants->pluck('idTenant')->map(fn ($id) => (int) $id);
    $legacyRows = $centralConnection->table($tableName)->orderBy('tenant_id')->orderBy('id')->get();
    $legacyByTenant = $legacyRows->groupBy(fn ($row) => (int) $row->tenant_id);
    $orphanLegacyCount = $legacyRows
        ->filter(fn ($row) => !$tenantIds->contains((int) $row->tenant_id))
        ->count();

    $checkRows = [];
    $issues = [];

    if ($orphanLegacyCount > 0) {
        $issues[] = "Ada {$orphanLegacyCount} baris orphan di tabel pusat tenant_email_otps (tenant_id tidak valid).";
    }

    foreach ($tenants as $tenant) {
        $tenantHasTable = false;
        $legacyCount = (int) ($legacyByTenant->get((int) $tenant->idTenant)?->count() ?? 0);
        $tenantCount = 0;
        $missingCount = 0;
        $errorMessage = null;

        try {
            $tenantDbManager->activateForTenant($tenant, true);
            $tenantConnection = DB::connection('tenant');
            $tenantSchema = $tenantConnection->getSchemaBuilder();

            $tenantHasTable = $tenantSchema->hasTable($tableName);

            if (!$tenantHasTable) {
                $issues[] = "Tenant #{$tenant->idTenant} ({$tenant->slug}) belum punya tabel {$tableName} di DB tenant.";
            } else {
                $tenantCount = (int) $tenantConnection->table($tableName)
                    ->where('tenant_id', (int) $tenant->idTenant)
                    ->count();

                if ($legacyCount > 0) {
                    $legacyIds = $legacyByTenant
                        ->get((int) $tenant->idTenant, collect())
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->all();

                    $copiedIds = $tenantConnection->table($tableName)
                        ->where('tenant_id', (int) $tenant->idTenant)
                        ->whereIn('id', $legacyIds)
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->all();

                    $missingCount = count(array_diff($legacyIds, $copiedIds));

                    if ($missingCount > 0) {
                        $issues[] = "Tenant #{$tenant->idTenant} ({$tenant->slug}) masih kurang {$missingCount} baris OTP di DB tenant.";
                    }
                }
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $issues[] = "Tenant #{$tenant->idTenant} ({$tenant->slug}) gagal diverifikasi: {$errorMessage}";
        }

        $checkRows[] = [
            'tenant_id' => (string) $tenant->idTenant,
            'slug' => (string) ($tenant->slug ?? '-'),
            'legacy_rows' => (string) $legacyCount,
            'tenant_table' => $tenantHasTable ? 'yes' : 'no',
            'tenant_rows' => (string) $tenantCount,
            'missing_rows' => (string) $missingCount,
            'status' => $errorMessage ? 'error' : 'ok',
        ];
    }

    $this->info('Checklist Verifikasi Phase 1.6:');
    $this->line('1) Tabel pusat tenant_email_otps: ditemukan');
    $this->line('2) Jumlah baris di pusat: '.$legacyRows->count());
    $this->line('3) Orphan baris tenant_id (tenant sudah tidak ada): '.$orphanLegacyCount);
    $this->line('4) Verifikasi per tenant:');
    $this->table(
        ['tenant_id', 'slug', 'legacy_rows', 'tenant_table', 'tenant_rows', 'missing_rows', 'status'],
        $checkRows
    );

    if (!empty($issues)) {
        $this->error('Checklist gagal. Perbaiki isu berikut sebelum drop:');
        foreach ($issues as $issue) {
            $this->line('- '.$issue);
        }

        return Command::FAILURE;
    }

    $this->info('Checklist lolos. Tidak ada data krusial yang tertinggal di pusat.');

    if (!$this->option('force-drop')) {
        $this->warn('Mode dry-run. Tabel belum di-drop. Jalankan ulang dengan --force-drop untuk eksekusi drop.');
        return Command::SUCCESS;
    }

    $centralSchema->drop($tableName);
    $this->info('Tabel pusat tenant_email_otps berhasil di-drop.');

    return Command::SUCCESS;
})->purpose('Checklist + drop aman tabel pusat tenant_email_otps setelah migrasi ke DB tenant');
