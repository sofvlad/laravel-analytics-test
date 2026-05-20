<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'ip',
        'lat',
        'lon',
        'city',
        'country',
        'user_agent',
        'visited_at',
    ];

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'visited_at' => 'datetime',
    ];
}
