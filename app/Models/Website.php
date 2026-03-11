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
        'check_ssl'
    ];

    public function latestLog() : HasOne
    {
        return $this->hasOne(UptimeLogs::class,'websites_id')
        ->latestOfMany('checked_at');
    }
}
