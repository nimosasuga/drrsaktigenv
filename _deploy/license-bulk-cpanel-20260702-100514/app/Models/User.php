<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/User.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nrpp',
        'password',
        'status_user',
        'branch',
        'position',
        'department',
        'is_verified',
        'verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function latestSubscription()
    {
        return $this->hasOne(UserSubscription::class)->latestOfMany();
    }
}
