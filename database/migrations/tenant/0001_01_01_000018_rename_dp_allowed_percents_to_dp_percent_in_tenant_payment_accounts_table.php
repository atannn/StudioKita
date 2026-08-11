<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_payment_accounts')) {
            return;
        }

        if (!Schema::hasColumn('tenant_payment_accounts', 'dp_percent')) {
            Schema::table('tenant_payment_accounts', function (Blueprint $table) {
                $table->unsignedTinyInteger('dp_percent')->default(30)->after('dp_enabled');
            });
        }

        $hasLegacyColumn = Schema::hasColumn('tenant_payment_accounts', 'dp_allowed_percents');

        $rows = DB::table('tenant_payment_accounts')
            ->select($hasLegacyColumn ? ['id', 'dp_percent', 'dp_allowed_percents'] : ['id', 'dp_percent'])
            ->get();

        foreach ($rows as $row) {
            $current = (int) ($row->dp_percent ?? 0);
            if ($current >= 1 && $current <= 90) {
                continue;
            }

            $resolved = $hasLegacyColumn
                ? $this->resolvePercentFromLegacy($row->dp_allowed_percents ?? null)
                : 30;

            DB::table('tenant_payment_accounts')
                ->where('id', $row->id)
                ->update([
                    'dp_percent' => $resolved,
                ]);
        }

        if (Schema::hasColumn('tenant_payment_accounts', 'dp_allowed_percents')) {
            Schema::table('tenant_payment_accounts', function (Blueprint $table) {
                $table->dropColumn('dp_allowed_percents');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenant_payment_accounts')) {
            return;
        }

        if (!Schema::hasColumn('tenant_payment_accounts', 'dp_allowed_percents')) {
            Schema::table('tenant_payment_accounts', function (Blueprint $table) {
                $table->text('dp_allowed_percents')->nullable()->after('dp_enabled');
            });
        }

        $rows = DB::table('tenant_payment_accounts')
            ->select(['id', 'dp_percent', 'dp_allowed_percents'])
            ->get();

        foreach ($rows as $row) {
            if (!empty($row->dp_allowed_percents)) {
                continue;
            }

            $percent = (int) ($row->dp_percent ?? 30);
            if ($percent < 1 || $percent > 90) {
                $percent = 30;
            }

            DB::table('tenant_payment_accounts')
                ->where('id', $row->id)
                ->update([
                    'dp_allowed_percents' => json_encode([$percent]),
                ]);
        }

        if (Schema::hasColumn('tenant_payment_accounts', 'dp_percent')) {
            Schema::table('tenant_payment_accounts', function (Blueprint $table) {
                $table->dropColumn('dp_percent');
            });
        }
    }

    private function resolvePercentFromLegacy(mixed $legacy): int
    {
        if (is_numeric($legacy)) {
            $percent = (int) $legacy;
            if ($percent >= 1 && $percent <= 90) {
                return $percent;
            }
        }

        if (is_string($legacy)) {
            $decoded = json_decode($legacy, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as $item) {
                    $percent = (int) $item;
                    if ($percent >= 1 && $percent <= 90) {
                        return $percent;
                    }
                }
            }

            $parts = preg_split('/[,\s]+/', trim($legacy), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($parts as $part) {
                $percent = (int) $part;
                if ($percent >= 1 && $percent <= 90) {
                    return $percent;
                }
            }
        }

        if (is_array($legacy)) {
            foreach ($legacy as $item) {
                $percent = (int) $item;
                if ($percent >= 1 && $percent <= 90) {
                    return $percent;
                }
            }
        }

        return 30;
    }
};
