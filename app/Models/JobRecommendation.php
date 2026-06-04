<?php

namespace App\Models;

use App\Support\SparepartRecommendationControlService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'part_number',
        'part_name',
        'qty',
        'remarks'
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    protected static function booted(): void
    {
        static::created(function (JobRecommendation $recommendation) {
            app(SparepartRecommendationControlService::class)->createFromJobRecommendation($recommendation);
        });

        static::updated(function (JobRecommendation $recommendation) {
            app(SparepartRecommendationControlService::class)->syncFromJobRecommendation($recommendation);
        });

        static::deleting(function (JobRecommendation $recommendation) {
            app(SparepartRecommendationControlService::class)->cancelFromJobRecommendation($recommendation);
        });
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
