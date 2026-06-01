<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Observers/JobRecommendationObserver.php
|--------------------------------------------------------------------------
*/

namespace App\Observers;

use App\Models\JobRecommendation;
use App\Support\SparepartRecommendationControlService;

class JobRecommendationObserver
{
    public function created(JobRecommendation $recommendation): void
    {
        app(SparepartRecommendationControlService::class)->createFromJobRecommendation($recommendation);
    }
}
