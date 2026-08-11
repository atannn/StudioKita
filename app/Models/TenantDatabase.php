<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantDatabase extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id',
        'driver',
        'database_name',
        'status',
        'last_migrated_at',
    ];

    protected $casts = [
        'last_migrated_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'idTenant');
    }
}
