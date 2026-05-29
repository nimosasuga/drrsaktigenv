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
        Route::get('/update-jobs/search-assets', [\App\Http\Controllers\JobController::class, 'searchAssets'])->name('update-jobs.search-assets');
        Route::get('/update-jobs/recommendation-history', [\App\Http\Controllers\JobController::class, 'recommendationHistory'])->name('update-jobs.recommendation-history');
        Route::resource('update-jobs', \App\Http\Controllers\JobController::class);
        Route::get('/batteries/search-assets', [\App\Http\Controllers\BatteryController::class, 'searchAssets'])->name('batteries.search-assets');
        Route::resource('batteries', \App\Http\Controllers\BatteryController::class);
        Route::get('/chargers/search-assets', [\App\Http\Controllers\ChargerController::class, 'searchAssets'])->name('chargers.search-assets');
        Route::resource('chargers', \App\Http\Controllers\ChargerController::class);
        Route::get('/deliveries/search-assets', [DeliveryController::class, 'searchAssets'])->name('deliveries.search-assets');
        Route::resource('deliveries', DeliveryController::class);
        Route::get('/penarikans/search-assets', [PenarikanController::class, 'searchAssets'])->name('penarikans.search-assets');
        Route::resource('penarikans', PenarikanController::class);
        Route::get('/calendar', fn() => view('calendar.index'))->name('calendar.index');
        Route::get('/reminders', fn() => view('reminders.index'))->name('reminders.index');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});
