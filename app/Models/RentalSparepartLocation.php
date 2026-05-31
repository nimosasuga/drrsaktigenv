<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/RentalSparepartLocation.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalSparepartLocation extends Model
{
    use HasFactory;

    public const DEPARTMENT = 'RENTAL';

    protected $fillable = [
        'department',
        'location_code',
        'location_name',
        'cabinet',
        'shelf',
        'box',
        'remarks',
    ];

    protected static function booted(): void
    {
        static::saving(function (RentalSparepartLocation $location) {
            $location->department = strtoupper(trim((string) ($location->department ?: self::DEPARTMENT)));
            $location->location_code = strtoupper(trim((string) $location->location_code));
        });
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(RentalSparepartStock::class, 'location_id');
    }
}
