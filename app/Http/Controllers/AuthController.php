<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $rentalAssets = UnitAsset::where('status', 'RENTAL')->orWhere('status', 'Ready')->count();
        $ditarikAssets = UnitAsset::where('status', 'DITARIK')->orWhere('status', 'Breakdown')->count();
        $backupAssets = UnitAsset::where('status', 'BACKUP')->orWhere('status', 'Standby')->count();

        // Mengambil data lisensi user (Jika bukan Super Admin)
        $subscription = null;
        if ($user->status_user !== 'super_admin') {
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        return view('dashboard', compact('user', 'totalAssets', 'rentalAssets', 'ditarikAssets', 'backupAssets', 'subscription'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
