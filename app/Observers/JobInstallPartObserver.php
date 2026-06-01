<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Observers/JobInstallPartObserver.php
|--------------------------------------------------------------------------
*/

namespace App\Observers;

use App\Models\JobInstallPart;
use App\Support\RentalSparepartUsageReviewService;

class JobInstallPartObserver
{
    public function created(JobInstallPart $installPart): void
    {
        app(RentalSparepartUsageReviewService::class)->createFromJobInstallPart($installPart);
    }

    public function deleting(JobInstallPart $installPart): void
    {
        app(RentalSparepartUsageReviewService::class)->cancelPendingForJobInstallPart($installPart);
    }
}
