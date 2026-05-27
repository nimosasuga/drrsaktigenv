<?php

namespace App\Models;

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

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
