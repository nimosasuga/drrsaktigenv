<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| routes/web.php
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UnitAssetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\PenarikanController;
use App\Http\Controllers\CommandCenterController;
use App\Http\Controllers\CommandCenterCsvController;
use App\Http\Controllers\UpdateJobShareController;
use App\Http\Controllers\OperationalShareController;
use App\Http\Controllers\UpdateJobExtraFieldController;
use App\Http\Controllers\UpdateJobAssetSearchController;
use App\Http\Controllers\UpdateJobPreventiveMaintenanceCheckController;
use App\Http\Controllers\UpdateJobSaveController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\RentalSparepartController;
use App\Http\Controllers\RentalSparepartAssetSearchController;
use App\Http\Controllers\RentalSparepartOutController;
use App\Http\Controllers\RentalSparepartMovementController;
use App\Http\Controllers\RentalSparepartUsageReviewController;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CheckSuperAdmin;

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/buy', [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::get('/subscription/payment/{id}', [SubscriptionController::class, 'payment'])->name('subscription.payment');
    Route::post('/subscription/payment/{id}/confirm', [SubscriptionController::class, 'confirmPayment'])->name('subscription.confirm');
    Route::get('/subscription/waiting', [SubscriptionController::class, 'waiting'])->name('subscription.waiting');

    Route::middleware([CheckSuperAdmin::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/verifikasi-lisensi', [AdminController::class, 'pendingSubscriptions'])->name('subscriptions');
        Route::post('/verifikasi-lisensi/{id}/approve', [AdminController::class, 'approveSubscription'])->name('subscriptions.approve');
        Route::resource('users', UserController::class);
    });

    Route::middleware([CheckSubscription::class])->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
        Route::resource('assets', UnitAssetController::class);
        Route::get('/update-jobs/search-assets', UpdateJobAssetSearchController::class)->name('update-jobs.search-assets');
        Route::get('/update-jobs/check-preventive-maintenance', UpdateJobPreventiveMaintenanceCheckController::class)->name('update-jobs.check-preventive-maintenance');
        Route::get('/update-jobs/extra-fields/asset', [UpdateJobExtraFieldController::class, 'asset'])->name('update-jobs.extra-fields.asset');
        Route::get('/update-jobs/{id}/extra-fields', [UpdateJobExtraFieldController::class, 'job'])->name('update-jobs.extra-fields.job');
        Route::get('/update-jobs/recommendation-history', [\App\Http\Controllers\JobController::class, 'recommendationHistory'])->name('update-jobs.recommendation-history');
        Route::get('/update-jobs/{id}/share-message', [UpdateJobShareController::class, 'message'])->name('update-jobs.share-message');
        Route::post('/update-jobs', [UpdateJobSaveController::class, 'store'])->name('update-jobs.store');
        Route::put('/update-jobs/{id}', [UpdateJobSaveController::class, 'update'])->name('update-jobs.update');
        Route::patch('/update-jobs/{id}', [UpdateJobSaveController::class, 'update']);
        Route::resource('update-jobs', \App\Http\Controllers\JobController::class)->except(['store', 'update']);
        Route::get('/batteries/search-assets', [\App\Http\Controllers\BatteryController::class, 'searchAssets'])->name('batteries.search-assets');
        Route::get('/batteries/{id}/share-message', [OperationalShareController::class, 'battery'])->name('batteries.share-message');
        Route::resource('batteries', \App\Http\Controllers\BatteryController::class);
        Route::get('/chargers/search-assets', [\App\Http\Controllers\ChargerController::class, 'searchAssets'])->name('chargers.search-assets');
        Route::get('/chargers/{id}/share-message', [OperationalShareController::class, 'charger'])->name('chargers.share-message');
        Route::resource('chargers', \App\Http\Controllers\ChargerController::class);
        Route::get('/deliveries/search-assets', [DeliveryController::class, 'searchAssets'])->name('deliveries.search-assets');
        Route::get('/deliveries/{id}/share-message', [OperationalShareController::class, 'delivery'])->name('deliveries.share-message');
        Route::resource('deliveries', DeliveryController::class);
        Route::get('/penarikans/search-assets', [PenarikanController::class, 'searchAssets'])->name('penarikans.search-assets');
        Route::get('/penarikans/{id}/share-message', [OperationalShareController::class, 'penarikan'])->name('penarikans.share-message');
        Route::resource('penarikans', PenarikanController::class);
        Route::get('/rental-spareparts', [RentalSparepartController::class, 'index'])->name('rental-spareparts.index');
        Route::get('/rental-spareparts/assets/search', RentalSparepartAssetSearchController::class)->name('rental-spareparts.assets.search');
        Route::get('/rental-spareparts/in/create', [RentalSparepartController::class, 'createIn'])->name('rental-spareparts.in.create');
        Route::post('/rental-spareparts/in', [RentalSparepartController::class, 'storeIn'])->name('rental-spareparts.in.store');
        Route::get('/rental-spareparts/out/create', [RentalSparepartOutController::class, 'create'])->name('rental-spareparts.out.create');
        Route::post('/rental-spareparts/out', [RentalSparepartOutController::class, 'store'])->name('rental-spareparts.out.store');
        Route::get('/rental-spareparts/movements', [RentalSparepartMovementController::class, 'index'])->name('rental-spareparts.movements.index');
        Route::get('/rental-spareparts/reviews', [RentalSparepartUsageReviewController::class, 'index'])->name('rental-spareparts.reviews.index');
        Route::post('/rental-spareparts/reviews/{review}/approve', [RentalSparepartUsageReviewController::class, 'approve'])->name('rental-spareparts.reviews.approve');
        Route::post('/rental-spareparts/reviews/{review}/reject', [RentalSparepartUsageReviewController::class, 'reject'])->name('rental-spareparts.reviews.reject');
        Route::get('/command-center', [CommandCenterController::class, 'index'])->name('command-center.index');
        Route::get('/command-center/export/{module}', [CommandCenterCsvController::class, 'export'])->name('command-center.export');
        Route::post('/command-center/import/{module}', [CommandCenterCsvController::class, 'import'])->name('command-center.import');
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/planning', [CalendarController::class, 'planning'])->name('calendar.planning');
        Route::get('/calendar/piket', [CalendarController::class, 'piket'])->name('calendar.piket');
        Route::post('/calendar/plannings', [CalendarController::class, 'store'])->name('calendar.plannings.store');
        Route::patch('/calendar/plannings/{planning}/status', [CalendarController::class, 'updateStatus'])->name('calendar.plannings.status');
        Route::delete('/calendar/plannings/{planning}', [CalendarController::class, 'destroy'])->name('calendar.plannings.destroy');
        Route::post('/calendar/piket', [CalendarController::class, 'storePiket'])->name('calendar.piket.store');
        Route::patch('/calendar/piket/{piket}/defer', [CalendarController::class, 'deferPiket'])->name('calendar.piket.defer');
        Route::delete('/calendar/piket/{piket}', [CalendarController::class, 'destroyPiket'])->name('calendar.piket.destroy');
        Route::get('/reminders', fn() => view('reminders.index'))->name('reminders.index');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});
