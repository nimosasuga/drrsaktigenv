<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\UnitAsset;
use App\Models\UserSubscription;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nrpp' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['nrpp' => $credentials['nrpp'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'nrpp' => 'NRPP atau kata sandi yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('nrpp');
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Mengambil statistik asli dari database
        $totalAssets = UnitAsset::count();
        $activeAssets = UnitAsset::whereRaw(UnitAsset::activeStatusSql())->count();
        $inactiveAssets = UnitAsset::whereRaw(UnitAsset::inactiveStatusSql())->count();
        $otherAssets = max($totalAssets - $activeAssets - $inactiveAssets, 0);
        $pmMonth = now();
        $pmEligibleAssets = UnitAsset::query()
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->whereRaw(UnitAsset::activeStatusSql())
            ->count();
        $pmDoneAssets = UnitAsset::query()
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->whereRaw(UnitAsset::activeStatusSql())
            ->whereIn('serial_number', Job::query()
                ->select('serial_number')
                ->whereNotNull('serial_number')
                ->where('serial_number', '!=', '')
                ->whereYear('work_date', $pmMonth->year)
                ->whereMonth('work_date', $pmMonth->month)
                ->where(function ($query) {
                    $query->where('job_type', 'Preventive Maintenance')
                        ->orWhere('job_type', 'PM')
                        ->orWhere('job_type', 'like', '%Preventive Maintenance%');
                }))
            ->count();
        $pmPendingAssets = max($pmEligibleAssets - $pmDoneAssets, 0);
        $pmCompletionRate = $pmEligibleAssets > 0
            ? round(($pmDoneAssets / $pmEligibleAssets) * 100)
            : 0;

        // Mengambil data lisensi user (Jika bukan Super Admin)
        $subscription = null;
        if ($user->status_user !== 'super_admin') {
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        return view('dashboard', compact(
            'user',
            'totalAssets',
            'activeAssets',
            'inactiveAssets',
            'otherAssets',
            'pmEligibleAssets',
            'pmDoneAssets',
            'pmPendingAssets',
            'pmCompletionRate',
            'subscription'
        ));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
