<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\UserSubscription;
use App\Models\User;

class AdminController extends Controller
{
    // Menampilkan daftar pembayaran yang butuh verifikasi
    public function pendingSubscriptions()
    {
        // Ambil data payment berserta relasinya
        $payments = Payment::with(['user', 'package', 'subscription'])
            ->where('payment_status', 'waiting_verification')
            ->latest()
            ->get();

        return view('admin.subscriptions', compact('payments'));
    }

    // Memproses persetujuan (Approve) lisensi
    public function approveSubscription($id)
    {
        $payment = Payment::findOrFail($id);

        // 1. Update status Pembayaran
        $payment->update([
            'payment_status' => 'paid',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        // 2. Update status Langganan (Aktif 1 Bulan)
        $payment->subscription->update([
            'status' => 'active',
            'started_at' => now(),
            'expired_at' => now()->addMonth(), // Tambah 1 Bulan
        ]);

        // 3. Update status User (Jadi Verified)
        $payment->user->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Pembayaran berhasil diverifikasi. Lisensi ' . $payment->user->name . ' sekarang Aktif!');
    }
}
