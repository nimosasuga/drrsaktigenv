<?php
// app/Models/ChargerRecommendation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargerRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'charger_id',
        'part_number',
        'part_name',
        'qty',
        'remarks'
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function charger(): BelongsTo
    {
        return $this->belongsTo(Charger::class);
    }
}
