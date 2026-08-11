<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'tenants_idTenant',
        'rooms_idrooms',
        'service_idservice',
        'nama_template',
        'repeat_mode',
        'days_of_week_json',
        'waktu_mulai',
        'waktu_selesai',
        'is_active',
    ];

    protected $casts = [
        'days_of_week_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'rooms_idrooms', 'idrooms');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_idservice', 'idservice');
    }

    public function appliesToDate(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return match ($this->repeat_mode) {
            'daily' => true,
            'weekdays' => $date->isWeekday(),
            'weekends' => $date->isWeekend(),
            'custom_days' => in_array((int) $date->dayOfWeekIso, array_map('intval', $this->days_of_week_json ?? []), true),
            default => false,
        };
    }
}
