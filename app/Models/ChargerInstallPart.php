<?php
// app/Models/ChargerInstallPart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargerInstallPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'charger_id',
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

    public function charger(): BelongsTo
    {
        return $this->belongsTo(Charger::class);
    }
}
