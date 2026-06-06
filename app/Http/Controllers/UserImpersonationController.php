<?php
// PATH FILE: app/Http/Controllers/UserImpersonationController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserImpersonationController extends Controller
{
    public function start(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser) {
            abort(403);
        }

        if ($request->session()->has('impersonator_id')) {
            return back()->with('error', 'Selesaikan mode masuk sebagai user terlebih dahulu sebelum berpindah user.');
        }

        if ((int) $currentUser->id === (int) $user->id) {
            return back()->with('error', 'Tidak perlu masuk sebagai akun sendiri.');
        }

        if (!$this->canImpersonate($currentUser, $user)) {
            abort(403, 'Anda tidak memiliki izin untuk masuk sebagai user ini.');
        }

        $request->session()->put([
            'impersonator_id' => $currentUser->id,
            'impersonator_name' => $currentUser->name,
            'impersonator_status_user' => $currentUser->status_user,
            'impersonated_user_id' => $user->id,
            'impersonated_user_name' => $user->name,
            'impersonated_at' => now()->toDateTimeString(),
        ]);

        Auth::loginUsingId($user->id);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Anda sedang masuk sebagai ' . $user->name . '.');
    }

    public function stop(Request $request)
    {
        $impersonatorId = $request->session()->get('impersonator_id');

        if (!$impersonatorId) {
            return redirect()->route('dashboard');
        }

        $impersonator = User::find($impersonatorId);

        if (!$impersonator) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'nrpp' => 'Session admin asal tidak ditemukan. Silakan login ulang.',
            ]);
        }

        Auth::loginUsingId($impersonator->id);
        $request->session()->forget([
            'impersonator_id',
            'impersonator_name',
            'impersonator_status_user',
            'impersonated_user_id',
            'impersonated_user_name',
            'impersonated_at',
        ]);
        $request->session()->regenerate();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Anda sudah kembali ke akun admin.');
    }

    public function status(Request $request)
    {
        return response()->json([
            'active' => $request->session()->has('impersonator_id'),
            'impersonator_name' => $request->session()->get('impersonator_name'),
            'impersonator_status_user' => $request->session()->get('impersonator_status_user'),
            'impersonated_user_name' => Auth::user()?->name,
            'started_at' => $request->session()->get('impersonated_at'),
            'stop_url' => route('impersonation.stop'),
        ]);
    }

    private function canImpersonate(User $currentUser, User $targetUser): bool
    {
        $currentRole = $this->normalizeRole($currentUser->status_user);
        $targetRole = $this->normalizeRole($targetUser->status_user);

        if ($currentRole === 'super_admin') {
            return true;
        }

        if ($currentRole === 'admin') {
            return in_array($targetRole, ['mekanik', 'koordinator', 'sect_head'], true);
        }

        return false;
    }

    private function normalizeRole(?string $role): string
    {
        $role = strtolower(trim((string) $role));
        $role = str_replace(['-', ' '], '_', $role);

        if ($role === 'secthead') {
            return 'sect_head';
        }

        if ($role === 'superadmin') {
            return 'super_admin';
        }

        return $role;
    }
}
