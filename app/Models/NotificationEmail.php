<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEmail extends Model
{
    protected $fillable = [
        'name',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
