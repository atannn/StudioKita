<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\TenantDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantDatabaseManager
{
    /**
     * Avoid running tenant migrations repeatedly in the same request lifecycle.
     *
     * @var array<int, bool>
     */
    private static array $schemaChecked = [];

    public function activateForTenant(Tenant $tenant, bool $ensureMigrated = true): void
    {
        $connection = $this->ensureConnectionRecord($tenant);

        $this->configureTenantConnection($connection);

        if ($ensureMigrated) {
            $this->ensureTenantSchema($tenant, $connection);
        }
    }

    public function runForTenant(Tenant $tenant, callable $callback, bool $ensureMigrated = true): mixed
    {
        $this->activateForTenant($tenant, $ensureMigrated);

        return $callback();
    }

    public function ensureConnectionRecord(Tenant $tenant): TenantDatabase
    {
        $connection = $tenant->databaseConnection;

        if ($connection) {
            if ($connection->driver !== 'mysql') {
                throw new \RuntimeException("Driver tenant DB [{$connection->driver}] tidak didukung.");
            }

            if (blank($connection->database_name)) {
                $connection->forceFill([
                    'database_name' => $this->buildTenantDatabaseName($tenant),
                ])->save();
            }

            $this->ensureMySqlDatabaseExists((string) $connection->database_name);

            return $connection;
        }

        $databaseName = $this->buildTenantDatabaseName($tenant);

        $this->ensureMySqlDatabaseExists($databaseName);

        return TenantDatabase::create([
            'tenant_id' => $tenant->idTenant,
            'driver' => 'mysql',
            'database_name' => $databaseName,
            'status' => 'active',
        ]);
    }

    private function buildTenantDatabaseName(Tenant $tenant): string
    {
        $prefix = (string) env('DB_DATABASE', 'studiokita').'_tenant';
        $normalizedPrefix = preg_replace('/[^A-Za-z0-9_]/', '_', Str::lower($prefix)) ?: 'tenant';
        $databaseName = "{$normalizedPrefix}_{$tenant->idTenant}";

        return substr($databaseName, 0, 64);
    }

    private function configureTenantConnection(TenantDatabase $connection): void
    {
        if ($connection->driver !== 'mysql') {
            throw new \RuntimeException("Driver tenant DB [{$connection->driver}] tidak didukung.");
        }

        config([
            'database.connections.tenant' => array_merge(
                $this->tenantMysqlBaseConfig(),
                ['database' => $connection->database_name]
            ),
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function dropMySqlDatabase(string $databaseName): void
    {
        if ($databaseName === '') {
            return;
        }

        $this->configureTenantAdminConnection();

        DB::connection('tenant_admin')->statement(
            'DROP DATABASE IF EXISTS '.$this->quoteMySqlIdentifier($databaseName)
        );
    }

    private function ensureTenantSchema(Tenant $tenant, TenantDatabase $connection): void
    {
        if (isset(self::$schemaChecked[$tenant->idTenant])) {
            return;
        }

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        $connection->forceFill([
            'last_migrated_at' => now(),
        ])->save();

        self::$schemaChecked[$tenant->idTenant] = true;
    }

    private function ensureMySqlDatabaseExists(string $databaseName): void
    {
        if ($databaseName === '') {
            throw new \RuntimeException('Nama database tenant tidak boleh kosong.');
        }

        $this->configureTenantAdminConnection();
        $adminConfig = config('database.connections.tenant_admin', []);
        $charset = $this->sanitizeMySqlToken((string) ($adminConfig['charset'] ?? 'utf8mb4'));
        $collation = $this->sanitizeMySqlToken((string) ($adminConfig['collation'] ?? 'utf8mb4_unicode_ci'));

        DB::connection('tenant_admin')->statement(
            'CREATE DATABASE IF NOT EXISTS '
            .$this->quoteMySqlIdentifier($databaseName)
            ." CHARACTER SET {$charset} COLLATE {$collation}"
        );
    }

    private function configureTenantAdminConnection(): void
    {
        $baseConfig = $this->tenantMysqlBaseConfig();
        $adminDatabase = (string) env(
            'TENANT_DB_ADMIN_DATABASE',
            config('database.connections.mysql.database', 'mysql')
        );

        config([
            'database.connections.tenant_admin' => array_merge(
                $baseConfig,
                ['database' => $adminDatabase]
            ),
        ]);

        DB::purge('tenant_admin');
    }

    private function tenantMysqlBaseConfig(): array
    {
        $mysqlConfig = config('database.connections.mysql', []);

        return [
            'driver' => 'mysql',
            'url' => env('TENANT_DB_URL'),
            'host' => env('TENANT_DB_HOST', $mysqlConfig['host'] ?? '127.0.0.1'),
            'port' => env('TENANT_DB_PORT', $mysqlConfig['port'] ?? '3306'),
            'database' => env('TENANT_DB_DATABASE', 'tenant_placeholder'),
            'username' => env('TENANT_DB_USERNAME', $mysqlConfig['username'] ?? 'root'),
            'password' => env('TENANT_DB_PASSWORD', $mysqlConfig['password'] ?? ''),
            'unix_socket' => env('TENANT_DB_SOCKET', $mysqlConfig['unix_socket'] ?? ''),
            'charset' => env('TENANT_DB_CHARSET', $mysqlConfig['charset'] ?? 'utf8mb4'),
            'collation' => env('TENANT_DB_COLLATION', $mysqlConfig['collation'] ?? 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => $mysqlConfig['strict'] ?? true,
            'engine' => $mysqlConfig['engine'] ?? null,
            'options' => $mysqlConfig['options'] ?? [],
        ];
    }

    private function quoteMySqlIdentifier(string $value): string
    {
        return '`'.str_replace('`', '``', $value).'`';
    }

    private function sanitizeMySqlToken(string $value): string
    {
        if (!preg_match('/\A[A-Za-z0-9_]+\z/', $value)) {
            throw new \RuntimeException("Token MySQL tidak valid: {$value}");
        }

        return $value;
    }
}
