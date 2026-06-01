<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Middleware/EnsureRentalSparepartManager.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRentalSparepartManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $role = strtolower(trim((string) ($user->status_user ?? $user->role ?? '')));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        $isGlobalAdmin = in_array($role, ['admin', 'super_admin'], true);
        $isRentalManager = in_array($role, ['koordinator', 'sect_head'], true) && $department === 'RENTAL';

        abort_unless(
            $isGlobalAdmin || $isRentalManager,
            403,
            'Modul Management Sparepart dan Recommendation Control hanya untuk admin, super admin, koordinator RENTAL, dan sect head RENTAL.'
        );

        return $next($request);
    }
}
