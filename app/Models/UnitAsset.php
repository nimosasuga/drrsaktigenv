<?php
// app/Models/UnitAsset.php

namespace App\Models;

use App\Models\Job;
use App\Support\DepartmentScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class UnitAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'supported_by',
        'customer',
        'location',
        'branch',
        'department',
        'serial_number',
        'unit_type',
        'year',
        'status',
        'delivery',
        'jenis_unit',
        'note',
        'qr_token',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('department', function ($query) {
            DepartmentScope::apply($query, (new static())->getTable());
        });

        static::creating(function (UnitAsset $asset) {
            if (empty($asset->department)) {
                $asset->department = DepartmentScope::valueForCreate(Auth::user());
            }
        });
    }

    public function jobHistories(): HasMany
    {
        return $this->hasMany(Job::class, 'serial_number', 'serial_number')
            ->orderByDesc('work_date')
            ->orderByDesc('created_at');
    }
}
