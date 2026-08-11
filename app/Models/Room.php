<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $connection = 'tenant';

    protected $primaryKey = 'idrooms';

    protected $fillable = [
        'nama_ruangan',
        'deskripsi',
        'kapasitas',
        'status',
        'tipe_ruangan',
        'foto_ruangan',
        'tenants_idTenant'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenants_idTenant', 'idTenant');
    }

    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'room_facilities',
            'rooms_idrooms',
            'facilities_idfasiltas',
            'idrooms',
            'idfasiltas'
        )->withPivot(['tenants_idTenant', 'notes'])
            ->withTimestamps();
    }
}
