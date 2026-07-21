<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function ipAddress(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return [];
                }
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
                return [$value];
            },
            set: function ($value) {
                if (is_array($value)) {
                    return json_encode(array_values(array_filter(array_map('trim', $value))));
                }
                return json_encode(empty($value) ? [] : [trim($value)]);
            }
        );
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }
}
