<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantVerificationLog extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id',
        'actor_id',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'idTenant');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}

