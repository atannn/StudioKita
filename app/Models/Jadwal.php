<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $connection = 'tenant';

    protected $table = 'jadwals';
    protected $primaryKey = 'idJadwal';

    protected $fillable = [
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'tenants_idTenant',
        'rooms_idrooms',
        'service_idservice',
        'source_type',
        'schedule_template_id',
        'schedule_date_harian_override_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'rooms_idrooms', 'idrooms');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenants_idTenant', 'idTenant');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_idservice', 'idservice');
    }

    public function scheduleTemplate()
    {
        return $this->belongsTo(ScheduleTemplate::class, 'schedule_template_id');
    }

    public function harianOverride()
    {
        return $this->belongsTo(ScheduleDateHarianOverride::class, 'schedule_date_harian_override_id');
    }
}
