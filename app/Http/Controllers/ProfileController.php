<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\UserSubscription;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil data langganan jika bukan Super Admin
        $subscription = null;
        if ($user->status_user !== 'super_admin') {
            $subscription = UserSubscription::with('package')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        return view('profile.index', compact('user', 'subscription'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek apakah password lama sesuai
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini yang Anda masukkan salah.']);
        }

        // Update password baru menggunakan properti dan save() agar dikenali IDE
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui!');
    }
}
