<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'company_name',
        'pic_email',
        'pic_phone',
        'domain_expires_at',
        'check_ssl',
        'is_up',
        'ssl_valid',
        'last_checked_at',
    ];
    
    protected $casts = [
        'domain_expires_at' => 'date',
        'is_up' => 'boolean',
        'ssl_valid' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    public function latestLog() : HasOne
    {
        return $this->hasOne(UptimeLogs::class,'websites_id')
        ->latestOfMany('checked_at');
    }
}
