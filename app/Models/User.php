<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'email',
        'no_telp',
        'password',
        'role',
        'status',
        'tenants_idTenant'
    ];

    protected $hidden = ['password'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenants_idTenant', 'idTenant');
    }
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isOwner()
    {
        return $this->role === 'owner';
    }

    public function isDeveloper()
    {
        return $this->role === 'developer';
    }

    public function isOwnerOrDeveloper()
    {
        return in_array($this->role, ['owner', 'developer'], true);
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

}
