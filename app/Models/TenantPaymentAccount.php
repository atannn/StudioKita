<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPaymentAccount extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'provider',
        'merchant_id',
        'midtrans_client_key_enc',
        'midtrans_server_key_enc',
        'is_production',
        'is_active',
        'dp_enabled',
        'dp_percent',
        'cash_enabled',
        'cash_instruction',
        'midtrans_last_test_success',
        'midtrans_last_tested_at',
    ];

    protected $casts = [
        'is_production' => 'boolean',
        'is_active' => 'boolean',
        'dp_enabled' => 'boolean',
        'dp_percent' => 'integer',
        'cash_enabled' => 'boolean',
        'midtrans_last_test_success' => 'boolean',
        'midtrans_last_tested_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'idTenant');
    }
}
