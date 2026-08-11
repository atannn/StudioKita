<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $connection = 'tenant';

    protected $primaryKey = 'idbooking';

    protected $fillable = [
        'tanggal_booking',
        'total_harga',
        'status',
        'payment_scheme',
        'dp_percent',
        'payment_state',
        'paid_amount',
        'tenants_idTenant',
        'rooms_idrooms',
        'service_idservice',
        'Jadwal_idJadwal',
        'user_id',
    ];

    protected $casts = [
        'dp_percent' => 'integer',
        'paid_amount' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(\App\Models\User::class, 'user_id'); }
    public function room() { return $this->belongsTo(\App\Models\Room::class, 'rooms_idrooms', 'idrooms'); }
    public function service() { return $this->belongsTo(\App\Models\Service::class, 'service_idservice', 'idservice'); }
    public function jadwal() { return $this->belongsTo(\App\Models\Jadwal::class, 'Jadwal_idJadwal', 'idJadwal'); }
    public function tenant() { return $this->belongsTo(\App\Models\Tenant::class, 'tenants_idTenant', 'idTenant'); }
    public function payments() { return $this->hasMany(\App\Models\Payment::class, 'booking_idbooking', 'idbooking'); }
    


}
