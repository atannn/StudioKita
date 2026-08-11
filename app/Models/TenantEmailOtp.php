<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantEmailOtp extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'created_by',
        'email',
        'code_hash',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'idTenant');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
