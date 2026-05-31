<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/WorkPlanning.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPlanning extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'mechanic_id',
        'partner_id',
        'branch',
        'department',
        'planned_date',
        'planned_time',
        'customer',
        'location',
        'serial_number',
        'unit_type',
        'job_type',
        'status',
        'note',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'planned_time' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function getDisplayTimeAttribute(): string
    {
        if (!$this->planned_time) {
            return '-';
        }

        return $this->planned_time->format('H:i');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'DONE' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'CANCELLED' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }
}
