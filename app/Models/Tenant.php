<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'mysql';

    protected $primaryKey = 'idTenant';
    public $timestamps = false;
    protected $table = 'tenants';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'nama_pemilik',
        'email',
        'no_telp',
        'alamat',
        'provinsi',
        'kota',
        'kecamatan',
        'status',
        'open_time',
        'close_time',
        'createdAt',
        'verification_level',
        'verification_status',
        'email_otp_verified_at',
        'basic_verified_at',
        'manual_verified_at',
        'verification_submitted_at',
        'verification_reviewed_at',
        'verification_reviewer_id',
        'verification_notes',
    ];

    protected $casts = [
        'email_otp_verified_at' => 'datetime',
        'basic_verified_at' => 'datetime',
        'manual_verified_at' => 'datetime',
        'verification_submitted_at' => 'datetime',
        'verification_reviewed_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'tenants_idTenant', 'idTenant');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'tenants_idTenant', 'idTenant');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'tenants_idTenant', 'idTenant');
    }


    public function photos()
    {
        return $this->hasMany(Photo::class, 'tenants_idTenant', 'idTenant');
    }

    public function primaryPhoto()
    {
        return $this->hasOne(Photo::class, 'tenants_idTenant', 'idTenant')
            ->where('is_primary', true);
    }

    public function facilities()
    {
        return $this->hasMany(Facility::class, 'tenants_idTenant', 'idTenant');
    }

    public function databaseConnection()
    {
        return $this->hasOne(TenantDatabase::class, 'tenant_id', 'idTenant');
    }

    public function paymentAccount()
    {
        return $this->hasOne(TenantPaymentAccount::class, 'tenant_id', 'idTenant');
    }

    public function verificationDocuments()
    {
        return $this->hasMany(TenantVerificationDocument::class, 'tenant_id', 'idTenant');
    }

    public function midtransSubmission()
    {
        return $this->hasOne(TenantMidtransSubmission::class, 'tenant_id', 'idTenant');
    }

    public function verificationLogs()
    {
        return $this->hasMany(TenantVerificationLog::class, 'tenant_id', 'idTenant');
    }

    public function verificationReviewer()
    {
        return $this->belongsTo(User::class, 'verification_reviewer_id', 'id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
