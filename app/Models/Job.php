<?php
// app/Models/Job.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UnitAsset;


class Job extends Model
{

    use HasFactory;

    // WAJIB DITAMBAHKAN AGAR TIDAK BENTROK DENGAN TABEL JOBS SYSTEM LARAVEL
    protected $table = 'update_jobs';

    protected $fillable = [
        'user_id',
        'branch',
        'status_mekanik',
        'pic',
        'partner',
        'vehicle_type',
        'nopol',
        'in_time',
        'out_time',
        'work_date',
        'serial_number',
        'unit_type',
        'year',
        'hour_meter',
        'nomor_lambung',
        'customer',
        'location',
        'job_type',
        'status_unit',
        'problem_date',
        'rfu_date',
        'lead_time_rfu',
        'pm',
        'rm',
        'problem',
        'action'
    ];

    protected $casts = [
        'work_date' => 'date',
        'problem_date' => 'date',
        'rfu_date' => 'date',
        'in_time' => 'datetime:H:i',
        'out_time' => 'datetime:H:i',
        'hour_meter' => 'integer',
        'lead_time_rfu' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Job $job) {
            if (request()->has('year')) {
                $job->year = request()->input('year');
            }

            if (request()->has('nomor_lambung')) {
                $job->nomor_lambung = request()->input('nomor_lambung');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(JobRecommendation::class);
    }

    public function installParts(): HasMany
    {
        return $this->hasMany(JobInstallPart::class);
    }

    public function unitAsset(): BelongsTo
    {
        return $this->belongsTo(UnitAsset::class, 'serial_number', 'serial_number');
    }

}
