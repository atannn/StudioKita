<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'title',
        'body',
        'target_role',
        'is_active',
    ];
}
