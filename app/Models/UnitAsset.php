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

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';

    public const LEGACY_ACTIVE_STATUSES = ['RENTAL', 'BACKUP', 'READY', 'STANDBY'];
    public const LEGACY_INACTIVE_STATUSES = ['DITARIK', 'BREAKDOWN'];

    protected $fillable = [
        'supported_by',
        'customer',
        'location',
        'branch',
        'department',
        'serial_number',
        'unit_type',
        'year',
        'battery_type',
        'battery_brand',
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

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            'RENTAL',
            'BACKUP',
            'DITARIK',
        ];
    }

    public static function activeStatusValues(): array
    {
        return array_merge([self::STATUS_ACTIVE], self::LEGACY_ACTIVE_STATUSES);
    }

    public static function inactiveStatusValues(): array
    {
        return array_merge([self::STATUS_INACTIVE], self::LEGACY_INACTIVE_STATUSES);
    }

    public static function activeStatusSql(string $column = 'status'): string
    {
        $values = collect(self::activeStatusValues())
            ->map(fn ($status) => "'" . str_replace("'", "''", $status) . "'")
            ->implode(', ');

        return "UPPER(TRIM(COALESCE({$column}, ''))) IN ({$values})";
    }

    public static function inactiveStatusSql(string $column = 'status'): string
    {
        $values = collect(self::inactiveStatusValues())
            ->map(fn ($status) => "'" . str_replace("'", "''", $status) . "'")
            ->implode(', ');

        return "UPPER(TRIM(COALESCE({$column}, ''))) IN ({$values})";
    }

    public function isActiveUnit(): bool
    {
        return in_array(strtoupper(trim((string) $this->status)), self::activeStatusValues(), true);
    }

    public function isInactiveUnit(): bool
    {
        return in_array(strtoupper(trim((string) $this->status)), self::inactiveStatusValues(), true);
    }
}
