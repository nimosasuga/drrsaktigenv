<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/RentalSparepartStock.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalSparepartStock extends Model
{
    use HasFactory;

    public const DEPARTMENT = 'RENTAL';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_ARCHIVED = 'ARCHIVED';

    protected $fillable = [
        'department',
        'stock_lifecycle_status',
        'sparepart_item_id',
        'location_id',
        'qty_on_hand',
        'qty_reserved',
        'archived_qty_on_hand',
        'source_no_job',
        'source_customer',
        'source_location',
        'source_type_unit',
        'source_sn_unit',
        'allocation_customer',
        'allocation_location',
        'allocation_type_unit',
        'allocation_sn_unit',
        'remarks',
        'archived_by',
        'archived_by_name',
        'archived_at',
        'archive_note',
        'restored_by',
        'restored_by_name',
        'restored_at',
        'restore_note',
    ];

    protected $casts = [
        'qty_on_hand' => 'integer',
        'qty_reserved' => 'integer',
        'archived_qty_on_hand' => 'integer',
        'archived_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (RentalSparepartStock $stock) {
            $stock->department = strtoupper(trim((string) ($stock->department ?: self::DEPARTMENT)));
            $stock->stock_lifecycle_status = strtoupper(trim((string) ($stock->stock_lifecycle_status ?: self::STATUS_ACTIVE)));
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('stock_lifecycle_status', self::STATUS_ACTIVE);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('stock_lifecycle_status', self::STATUS_ARCHIVED);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RentalSparepartItem::class, 'sparepart_item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(RentalSparepartLocation::class, 'location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(RentalSparepartMovement::class, 'sparepart_stock_id');
    }

    public function usageReviews(): HasMany
    {
        return $this->hasMany(RentalSparepartUsageReview::class, 'sparepart_stock_id');
    }

    public function getQtyAvailableAttribute(): int
    {
        return max(0, (int) $this->qty_on_hand - (int) $this->qty_reserved);
    }

    public function hasAvailableQty(int $qty): bool
    {
        return $this->qty_available >= $qty;
    }
}
