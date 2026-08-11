<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $connection = 'tenant';

    protected $primaryKey = 'idservice';

    protected $fillable = [
        'nama_service',
        'tipe_service',
        'durasi_menit',
        'weekday_price',
        'weekend_price',
        'deskripsi',
        'status',
        'tenants_idTenant',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenants_idTenant', 'idTenant');
    }

    public function getPriceForDayType(string $dayType): float
    {
        return (float) match ($dayType) {
            'weekend' => $this->weekend_price ?? 0,
            default => $this->weekday_price ?? 0,
        };
    }

    public function getPriceForDate(string $date): float
    {
        $dayType = Carbon::parse($date)->isWeekend() ? 'weekend' : 'weekdays';

        return $this->getPriceForDayType($dayType);
    }
}
