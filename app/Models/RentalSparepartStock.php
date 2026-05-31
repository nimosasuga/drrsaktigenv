<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/RentalSparepartStock.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalSparepartStock extends Model
{
    use HasFactory;

    public const DEPARTMENT = 'RENTAL';

    protected $fillable = [
        'department',
        'sparepart_item_id',
        'location_id',
        'qty_on_hand',
        'qty_reserved',
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
    ];

    protected $casts = [
        'qty_on_hand' => 'integer',
        'qty_reserved' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (RentalSparepartStock $stock) {
            $stock->department = strtoupper(trim((string) ($stock->department ?: self::DEPARTMENT)));
        });
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
