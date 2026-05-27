<?php
// app/Models/UnitAsset.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Job;

class UnitAsset extends Model
{

    public function jobHistories(): HasMany
    {
        return $this->hasMany(Job::class, 'serial_number', 'serial_number')
            ->orderByDesc('work_date')
            ->orderByDesc('created_at');
    }

    use HasFactory;

    protected $fillable = [
        'supported_by',
        'customer',
        'location',
        'branch',
        'serial_number',
        'unit_type',
        'year',
        'status',
        'delivery',
        'jenis_unit',
        'note',
        'qr_token',
    ];
}
