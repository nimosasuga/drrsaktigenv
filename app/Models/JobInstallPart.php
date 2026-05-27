<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobInstallPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'part_number',
        'part_name',
        'qty',
        'remarks',
        'no_job',
        'no_pr'
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
