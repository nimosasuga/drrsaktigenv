<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\UserSubscription;
use App\Models\User;

class AdminController extends Controller
{
    // Menampilkan daftar pembayaran yang butuh verifikasi
    public function pendingSubscriptions()
    {
        // Ambil data payment beserta relasinya agar Super Admin bisa melihat detail sebelum approve.
        $payments = Payment::with(['user', 'package', 'subscription'])
            ->where('payment_status', 'waiting_verification')
            ->latest('updated_at')
            ->get();

        $summary = [
            'total_queue' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'submitted_today' => $payments->filter(function ($payment) {
                return ($payment->paid_at && $payment->paid_at->isToday())
                    || ($payment->updated_at && $payment->updated_at->isToday());
            })->count(),
            'oldest_waiting' => $payments->sortBy('updated_at')->first(),
        ];

        return view('admin.subscriptions', compact('payments', 'summary'));
    }

    // Memproses persetujuan (Approve) lisensi
    public function approveSubscription($id)
    {
        $payment = Payment::with(['user', 'package', 'subscription'])->findOrFail($id);

        if ($payment->payment_status !== 'waiting_verification') {
            return back()->withErrors([
                'error' => 'Pembayaran ini tidak berada dalam status menunggu verifikasi.',
            ]);
        }

        if (!$payment->subscription || !$payment->package || !$payment->user) {
            return back()->withErrors([
                'error' => 'Data pembayaran tidak lengkap. Cek relasi user, paket, dan subscription sebelum approve.',
            ]);
        }

        DB::transaction(function () use ($payment) {
            $durationMonths = max(1, (int) ($payment->package->duration_months ?? 1));

            // 1. Update status Pembayaran
            $payment->update([
                'payment_status' => 'paid',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            // 2. Update status Langganan sesuai durasi paket
            $payment->subscription->update([
                'status' => 'active',
                'started_at' => now(),
                'expired_at' => now()->addMonths($durationMonths),
            ]);

            // 3. Update status User menjadi verified
            $payment->user->update([
                'is_verified' => true,
                'verified_at' => now(),
            ]);
        });

        return back()->with('success', 'Pembayaran berhasil diverifikasi. Lisensi ' . $payment->user->name . ' sekarang aktif.');
    }
}
