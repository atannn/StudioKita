<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $connection = 'tenant';

    protected $primaryKey = 'idfasiltas';

    protected $fillable = [
        'nama_fasilitas',
        'deskripsi',
        'status',
        'quantity',
        'tenants_idTenant',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenants_idTenant', 'idTenant');
    }

    public function rooms()
    {
        return $this->belongsToMany(
            Room::class,
            'room_facilities',
            'facilities_idfasiltas',
            'rooms_idrooms',
            'idfasiltas',
            'idrooms'
        )->withPivot(['tenants_idTenant', 'notes'])
            ->withTimestamps();
    }
}
