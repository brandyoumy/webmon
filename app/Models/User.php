<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;  // Add this
use Filament\Panel;                           // Add this

class User extends Authenticatable implements FilamentUser  // Add implements
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'access_level'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->access_level == 'admin';
    }

    // Add this method
   public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->access_level, ['admin', 'user']);
    }
}