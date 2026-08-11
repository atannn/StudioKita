<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operasional extends Model
{
    protected $connection = 'tenant';

    protected $primaryKey = 'idoperasional';

    protected $fillable = [
        'day',
        'open_time',
        'close_time',
        'is_closed',
        'tenants_idTenant',
    ];
}
