<?php

namespace App\Providers;

use App\Models\JobInstallPart;
use App\Observers\JobInstallPartObserver;
use App\Support\DepartmentPartnerOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JobInstallPart::observe(JobInstallPartObserver::class);

        View::composer([
            'update-jobs.create',
            'update-jobs.edit',
            'batteries.create',
            'batteries.edit',
            'chargers.create',
            'chargers.edit',
            'deliveries.create',
            'deliveries.edit',
            'penarikans.create',
            'penarikans.edit',
        ], function ($view) {
            if (!Auth::check()) {
                return;
            }

            $user = Auth::user();
            $data = $view->getData();
            $branch = $data['branch'] ?? ($user->branch ?? 'HO / Pusat');

            $view->with('partners', DepartmentPartnerOptions::forUser($user, $branch));
        });
    }
}
