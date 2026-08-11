<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleDateHarianOverride extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'tenants_idTenant',
        'rooms_idrooms',
        'service_idservice',
        'tanggal',
        'override_type',
        'waktu_mulai',
        'waktu_selesai',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'rooms_idrooms', 'idrooms');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_idservice', 'idservice');
    }
}
