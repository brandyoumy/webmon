<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'provider',
        'description',
    ];

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }
}
