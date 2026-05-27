<?php
// app/Models/BatteryInstallPart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryInstallPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'battery_id',
        'part_number',
        'part_name',
        'qty',
        'remarks',
        'no_job',
        'no_pr'
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function battery(): BelongsTo
    {
        return $this->belongsTo(Battery::class);
    }
}
