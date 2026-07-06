<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseBatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_batch_id',
        'user_id',
        'user_subscription_id',
        'previous_subscription_package_id',
        'new_subscription_package_id',
        'action',
        'previous_status',
        'new_status',
        'previous_started_at',
        'previous_expired_at',
        'new_started_at',
        'new_expired_at',
        'note',
    ];

    protected $casts = [
        'previous_started_at' => 'datetime',
        'previous_expired_at' => 'datetime',
        'new_started_at' => 'datetime',
        'new_expired_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(LicenseBatch::class, 'license_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function previousPackage()
    {
        return $this->belongsTo(SubscriptionPackage::class, 'previous_subscription_package_id');
    }

    public function newPackage()
    {
        return $this->belongsTo(SubscriptionPackage::class, 'new_subscription_package_id');
    }
}
