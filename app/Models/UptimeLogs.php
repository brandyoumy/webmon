<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UptimeLogs extends Model
{
    use HasFactory;
    protected $table = 'uptime_logs';

    protected $fillable = [
        'websites_id',
        'status_code',
        'response_time',
        'is_up',
        'ssl_valid',
        'checked_at'
    ];

    protected $casts = [
        'is_up' => 'boolean',
        'ssl_valid' => 'boolean',
        'checked_at' => 'datetime',
        'response_time' => 'float',
        'status_code' => 'integer',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class,'websites_id');
    }
}
