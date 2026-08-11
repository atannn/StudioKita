<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $connection = 'tenant';

    protected $primaryKey = 'idfoto';

    protected $fillable = [
        'foto_path',
        'caption',
        'is_primary',
        'uploaded_at',
        'status',
        'tenants_idTenant',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenants_idTenant', 'idTenant');
    }
}
