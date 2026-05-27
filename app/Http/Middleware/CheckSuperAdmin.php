<?php
// app/Http/Middleware/CheckSuperAdmin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Pastikan hanya super_admin yang bisa lewat
        if (!$user || $user->status_user !== 'super_admin') {
            abort(403, 'Akses Ditolak. Halaman ini khusus Super Admin.');
        }

        return $next($request);
    }
}
