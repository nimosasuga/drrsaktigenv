<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'subscription_package_id',
        'duration_months',
        'expired_at',
        'total_users',
        'processed_users',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class, 'subscription_package_id');
    }

    public function items()
    {
        return $this->hasMany(LicenseBatchItem::class);
    }
}
