<?php
// app/Http/Middleware/CheckSubscription.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSubscription;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // 1. Super Admin bypass semua pengecekan
        if ($user->status_user === 'super_admin') {
            return $next($request);
        }

        // 2. Cek apakah punya langganan aktif
        $hasActive = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expired_at', '>', now())
            ->exists();

        if ($hasActive) {
            return $next($request);
        }

        // 3. Jika sedang mengakses halaman langganan/logout, biarkan lewat (mencegah infinite loop)
        if ($request->routeIs('subscription.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // 4. Jika belum aktif dan mencoba akses dashboard, lempar ke halaman pilih paket
        return redirect()->route('subscription.index');
    }
}
