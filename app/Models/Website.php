<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'company_name',
        'package',
        'remark',
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

    protected static function booted()
    {
        static::deleting(function ($website) {
            $website->uptimeLogs()->delete();
        });
    }

    public function uptimeLogs() : HasMany
    {
        return $this->hasMany(UptimeLogs::class, 'websites_id');
    }

    public function latestLog() : HasOne
    {
        return $this->hasOne(UptimeLogs::class,'websites_id')
            ->latestOfMany('checked_at');
    }
}
