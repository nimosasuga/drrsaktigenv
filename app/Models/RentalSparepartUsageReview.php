<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/RentalSparepartUsageReview.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalSparepartUsageReview extends Model
{
    use HasFactory;

    public const STATUS_PENDING_REVIEW = 'PENDING_REVIEW';
    public const STATUS_NEED_SOURCE_SELECTION = 'NEED_SOURCE_SELECTION';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_CANCELLED_BY_JOB_EDIT = 'CANCELLED_BY_JOB_EDIT';

    public const MATCH_NO_JOB_EXACT = 'NO_JOB_EXACT';
    public const MATCH_SN_EXACT = 'SN_EXACT';
    public const MATCH_PART_ONLY = 'PART_ONLY';
    public const MATCH_NOT_FOUND = 'NOT_FOUND';

    protected $fillable = [
        'department',
        'job_id',
        'job_install_part_id',
        'sparepart_stock_id',
        'sparepart_item_id',
        'movement_id',
        'work_date',
        'job_serial_number',
        'job_customer',
        'job_location',
        'no_job',
        'part_number',
        'part_name',
        'qty_requested',
        'match_type',
        'review_status',
        'is_borrowed',
        'borrow_reason',
        'original_allocation_customer',
        'original_allocation_location',
        'original_allocation_type_unit',
        'original_allocation_sn_unit',
        'actual_customer',
        'actual_location',
        'actual_type_unit',
        'actual_sn_unit',
        'mechanic_id',
        'mechanic_name',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'qty_requested' => 'integer',
        'is_borrowed' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function installPart(): BelongsTo
    {
        return $this->belongsTo(JobInstallPart::class, 'job_install_part_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RentalSparepartItem::class, 'sparepart_item_id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(RentalSparepartStock::class, 'sparepart_stock_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(RentalSparepartMovement::class, 'movement_id');
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
