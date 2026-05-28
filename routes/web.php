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
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CheckSuperAdmin;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute yang butuh Login / Authenticated
Route::middleware(['auth'])->group(function () {

    // 1. Alur Langganan
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/buy', [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::get('/subscription/payment/{id}', [SubscriptionController::class, 'payment'])->name('subscription.payment');
    Route::post('/subscription/payment/{id}/confirm', [SubscriptionController::class, 'confirmPayment'])->name('subscription.confirm');
    Route::get('/subscription/waiting', [SubscriptionController::class, 'waiting'])->name('subscription.waiting');

    // 2. Rute Khusus Super Admin
    Route::middleware([CheckSuperAdmin::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/verifikasi-lisensi', [AdminController::class, 'pendingSubscriptions'])->name('subscriptions');
        Route::post('/verifikasi-lisensi/{id}/approve', [AdminController::class, 'approveSubscription'])->name('subscriptions.approve');

        // Rute CRUD Manajemen Pengguna
        Route::resource('users', UserController::class);
    });

    // 3. Rute Aplikasi Utama
    // Harus lolos subscription, kecuali super_admin karena sudah bypass di middleware CheckSubscription.
    Route::middleware([CheckSubscription::class])->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

        // Rute Manajemen Aset
        Route::resource('assets', UnitAssetController::class);

        // API Pencarian Asset S/N untuk Form Update Job
        Route::get('/update-jobs/search-assets', [\App\Http\Controllers\JobController::class, 'searchAssets'])
            ->name('update-jobs.search-assets');

        // API Histori Rekomendasi Part berdasarkan S/N untuk Form Update Job
        Route::get('/update-jobs/recommendation-history', [\App\Http\Controllers\JobController::class, 'recommendationHistory'])
            ->name('update-jobs.recommendation-history');

        // Rute Manajemen Update Job
        Route::resource('update-jobs', \App\Http\Controllers\JobController::class);

        // Rute Manajemen Battery
        Route::get('/batteries/search-assets', [\App\Http\Controllers\BatteryController::class, 'searchAssets'])
            ->name('batteries.search-assets');
        Route::resource('batteries', \App\Http\Controllers\BatteryController::class);

        // Rute Management Charger
        Route::get('/chargers/search-assets', [\App\Http\Controllers\ChargerController::class, 'searchAssets'])
            ->name('chargers.search-assets');
        Route::resource('chargers', \App\Http\Controllers\ChargerController::class);

        // Rute Delivery Unit
        Route::get('/deliveries/search-assets', [DeliveryController::class, 'searchAssets'])
            ->name('deliveries.search-assets');
        Route::resource('deliveries', DeliveryController::class);

        // Rute Kalender
        Route::get('/calendar', function () {
            return view('calendar.index');
        })->name('calendar.index');

        // Rute Pengingat
        Route::get('/reminders', function () {
            return view('reminders.index');
        })->name('reminders.index');

        // Rute Profil
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});
