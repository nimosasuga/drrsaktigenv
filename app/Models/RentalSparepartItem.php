<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/RentalSparepartItem.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalSparepartItem extends Model
{
    use HasFactory;

    public const DEPARTMENT = 'RENTAL';

    protected $fillable = [
        'department',
        'part_number',
        'part_name',
        'default_type_unit',
        'min_stock',
        'remarks',
    ];

    protected $casts = [
        'min_stock' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (RentalSparepartItem $item) {
            $item->department = strtoupper(trim((string) ($item->department ?: self::DEPARTMENT)));
            $item->part_number = strtoupper(trim((string) $item->part_number));
        });

        static::updating(function (RentalSparepartItem $item) {
            $item->department = strtoupper(trim((string) ($item->department ?: self::DEPARTMENT)));
            $item->part_number = strtoupper(trim((string) $item->part_number));
        });
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(RentalSparepartStock::class, 'sparepart_item_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(RentalSparepartMovement::class, 'sparepart_item_id');
    }

    public function usageReviews(): HasMany
    {
        return $this->hasMany(RentalSparepartUsageReview::class, 'sparepart_item_id');
    }
}
