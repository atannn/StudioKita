<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $connection = 'tenant';

    protected $primaryKey = 'idpayments';

    protected $fillable = [
        'method',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'snap_token',
        'snap_redirect_url',
        'status',
        'raw_status',
        'webhook_payload',
        'payment_time',
        'expires_time',
        'paid_at',
        'failed_at',
        'payment_type',
        'amount',
        'tenants_idTenant',
        'booking_idbooking',
        'handled_by_user_id',
        'handled_at',
        'payment_note',
    ];

    protected $casts = [
        'payment_time' => 'datetime',
        'expires_time' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'handled_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_idbooking', 'idbooking');
    }
}
