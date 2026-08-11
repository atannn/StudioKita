<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Buat tenant (studio) dulu
        $tenant = Tenant::firstOrCreate(
            ['email' => 'owner@studiokita.test'],
            [
                'nama' => 'Studio Kita',
                'nama_pemilik' => 'Owner Studio',
                'no_telp' => '081234567890',
                'alamat' => 'Surabaya',
                'status' => 'active',
                'createdAt' => now()->toDateString(),
            ]
        );

        // 2) Buat user owner
        User::firstOrCreate(
            ['email' => 'owner@studiokita.test'],
            [
                'name' => 'Owner Studio',
                'password' => Hash::make('password123'),
                'role' => 'owner',
                'status' => 1,
                'tenants_idTenant' => $tenant->idTenant,
            ]
        );

        // (opsional) customer dummy
        User::firstOrCreate(
            ['email' => 'customer@studiokita.test'],
            [
                'name' => 'Customer Test',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'status' => 1,
                'tenants_idTenant' => $tenant->idTenant,
            ]
        );
    }
}

