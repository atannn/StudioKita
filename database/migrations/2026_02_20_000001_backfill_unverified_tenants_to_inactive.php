<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('verification_level')
                    ->orWhereNull('verification_status')
                    ->orWhere('verification_level', '!=', 'verified')
                    ->orWhere('verification_status', '!=', 'approved');
            })
            ->update([
                'status' => 'inactive',
            ]);
    }

    public function down(): void
    {
        // Tidak ada rollback otomatis untuk backfill status.
    }
};
