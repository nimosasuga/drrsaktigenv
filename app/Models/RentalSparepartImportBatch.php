<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/RentalSparepartImportBatch.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalSparepartImportBatch extends Model
{
    use HasFactory;

    public const STATUS_IMPORTED = 'IMPORTED';

    protected $fillable = [
        'batch_code',
        'department',
        'imported_by',
        'imported_by_name',
        'status',
        'total_rows',
        'total_qty',
        'unique_parts',
        'existing_parts',
        'new_parts',
        'unique_locations',
        'existing_locations',
        'new_locations',
        'merge_stock_rows',
        'new_stock_rows',
        'summary_json',
    ];

    protected $casts = [
        'summary_json' => 'array',
    ];

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(RentalSparepartMovement::class, 'import_batch_id');
    }
}
