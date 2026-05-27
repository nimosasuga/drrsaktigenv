<?php
// app/Models/Battery.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Battery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch',
        'status_mekanik',
        'pic',
        'partner',
        'in_time',
        'out_time',
        'vehicle',
        'nopol',
        'date',
        'customer',
        'location',
        'unit_type',
        'serial_number',
        'sn_battery',
        'battery_type',
        'battery_year',
        'category_job',
        'job_type',
        'status_unit',
        'problem_date',
        'rfu_date',
        'problem',
        'action'
    ];

    protected $casts = [
        'date' => 'date',
        'problem_date' => 'date',
        'rfu_date' => 'date',
        'in_time' => 'datetime:H:i',
        'out_time' => 'datetime:H:i',
        'battery_year' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(BatteryRecommendation::class);
    }

    public function installParts(): HasMany
    {
        return $this->hasMany(BatteryInstallPart::class);
    }
}
