<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/RentalSparepartMovement.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalSparepartMovement extends Model
{
    use HasFactory;

    public const TYPE_IN = 'IN';
    public const TYPE_OUT = 'OUT';
    public const TYPE_TRANSFER = 'TRANSFER';
    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';
    public const TYPE_REALLOCATION = 'REALLOCATION';

    protected $fillable = [
        'department',
        'movement_type',
        'movement_date',
        'sparepart_item_id',
        'sparepart_stock_id',
        'from_location_id',
        'to_location_id',
        'part_number_snapshot',
        'part_name_snapshot',
        'qty',
        'no_job',
        'source_customer',
        'source_type_unit',
        'source_sn_unit',
        'allocation_customer',
        'allocation_type_unit',
        'allocation_sn_unit',
        'actual_customer',
        'actual_type_unit',
        'actual_sn_unit',
        'is_cross_allocation',
        'pic_user_id',
        'pic_name',
        'remarks',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'qty' => 'integer',
        'is_cross_allocation' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(RentalSparepartItem::class, 'sparepart_item_id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(RentalSparepartStock::class, 'sparepart_stock_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }
}
