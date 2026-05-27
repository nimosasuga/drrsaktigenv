<?php
// app/Models/BatteryRecommendation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'battery_id',
        'part_number',
        'part_name',
        'qty',
        'remarks'
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function battery(): BelongsTo
    {
        return $this->belongsTo(Battery::class);
    }
}
