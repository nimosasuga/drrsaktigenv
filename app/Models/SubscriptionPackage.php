<?php
// app/Models/SubscriptionPackage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'package_name',
        'duration_months',
        'price',
        'is_active',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
