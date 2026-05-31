<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/Delivery.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_code',
        'user_id',
        'branch',
        'department',
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
        'serial_number',
        'unit_type',
        'year',
        'hour_meter',
        'job_type',
        'status_unit',
        'battery_type',
        'battery_sn',
        'charger_type',
        'charger_sn',
        'trolly',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'in_time' => 'datetime:H:i',
        'out_time' => 'datetime:H:i',
        'year' => 'integer',
        'hour_meter' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedDateAttribute(): string
    {
        if (!$this->date) {
            return '-';
        }

        return $this->date->translatedFormat('d M Y');
    }

    public function getFormattedInTimeAttribute(): string
    {
        if (!$this->in_time) {
            return '-';
        }

        return $this->in_time->format('H:i');
    }

    public function getFormattedOutTimeAttribute(): string
    {
        if (!$this->out_time) {
            return '-';
        }

        return $this->out_time->format('H:i');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_unit) {
            'RFU' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'BREAKDOWN' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };
    }

    public function getDisplayJobTypeAttribute(): string
    {
        return $this->job_type ?: 'DELIVERY UNIT';
    }

    public function getDisplayPicAttribute(): string
    {
        return $this->pic ?: '-';
    }
}
