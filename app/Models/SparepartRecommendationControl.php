<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/SparepartRecommendationControl.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparepartRecommendationControl extends Model
{
    use HasFactory;

    public const DEPARTMENT_RENTAL = 'RENTAL';
    public const DEPARTMENT_SERVICE = 'SERVICE';

    public const STATUS_RECOMMENDED = 'RECOMMENDED';
    public const STATUS_REVIEWED = 'REVIEWED';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_NEED_SUPPLY = 'NEED_SUPPLY';
    public const STATUS_SUPPLIED = 'SUPPLIED';
    public const STATUS_PARTIAL_INSTALLED = 'PARTIAL_INSTALLED';
    public const STATUS_INSTALLED = 'INSTALLED';
    public const STATUS_CLOSED = 'CLOSED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const SUPPLY_NOT_SUPPLIED = 'NOT_SUPPLIED';
    public const SUPPLY_NEED_SUPPLY = 'NEED_SUPPLY';
    public const SUPPLY_PARTIAL_SUPPLIED = 'PARTIAL_SUPPLIED';
    public const SUPPLY_SUPPLIED = 'SUPPLIED';
    public const SUPPLY_NOT_REQUIRED = 'NOT_REQUIRED';

    public const SOURCE_STOCK = 'STOCK';
    public const SOURCE_PURCHASE = 'PURCHASE';
    public const SOURCE_MANUAL = 'MANUAL';
    public const SOURCE_BORROWED = 'BORROWED';

    protected $fillable = [
        'department',
        'job_id',
        'job_recommendation_id',
        'source_stock_id',
        'installed_job_id',
        'work_date',
        'serial_number',
        'customer',
        'location',
        'unit_type',
        'part_number',
        'part_name',
        'qty_recommended',
        'qty_supplied',
        'qty_installed',
        'recommendation_status',
        'supply_status',
        'source_type',
        'is_cross_allocation',
        'recommended_by',
        'recommended_by_name',
        'reviewed_by',
        'reviewed_by_name',
        'reviewed_at',
        'supplied_by',
        'supplied_by_name',
        'supplied_at',
        'installed_at',
        'closed_at',
        'remarks',
        'review_note',
        'supply_note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'qty_recommended' => 'integer',
        'qty_supplied' => 'integer',
        'qty_installed' => 'integer',
        'is_cross_allocation' => 'boolean',
        'reviewed_at' => 'datetime',
        'supplied_at' => 'datetime',
        'installed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (SparepartRecommendationControl $control) {
            $control->department = strtoupper(trim((string) $control->department));
            $control->part_number = $control->part_number !== null ? strtoupper(trim((string) $control->part_number)) : null;
            $control->serial_number = $control->serial_number !== null ? strtoupper(trim((string) $control->serial_number)) : null;
            $control->recommendation_status = strtoupper(trim((string) ($control->recommendation_status ?: self::STATUS_RECOMMENDED)));
            $control->supply_status = strtoupper(trim((string) ($control->supply_status ?: self::SUPPLY_NOT_SUPPLIED)));
        });
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(JobRecommendation::class, 'job_recommendation_id');
    }

    public function sourceStock(): BelongsTo
    {
        return $this->belongsTo(RentalSparepartStock::class, 'source_stock_id');
    }

    public function installedJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'installed_job_id');
    }

    public function recommendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function suppliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplied_by');
    }

    public function isClosed(): bool
    {
        return in_array($this->recommendation_status, [self::STATUS_CLOSED, self::STATUS_CANCELLED, self::STATUS_REJECTED], true);
    }
}
