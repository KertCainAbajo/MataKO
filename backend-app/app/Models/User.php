<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'age', 'role', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'age' => 'integer',
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'disabled_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function isDisabled(): bool
    {
        return $this->disabled_at !== null;
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
