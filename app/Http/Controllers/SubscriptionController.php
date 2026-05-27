<?php
// app/Http/Controllers/SubscriptionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SubscriptionPackage;
use App\Models\UserSubscription;
use App\Models\Payment;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Jika user adalah Super Admin atau sudah aktif, arahkan ke dashboard
        if ($user->status_user === 'super_admin' || UserSubscription::where('user_id', $user->id)->where('status', 'active')->where('expired_at', '>', now())->exists()) {
            return redirect()->route('dashboard');
        }

        // Cek jika sudah punya tagihan yang belum selesai (mencegah beli paket dobel)
        $pendingSub = UserSubscription::where('user_id', $user->id)->where('status', 'pending')->first();
        if ($pendingSub) {
            $payment = Payment::where('user_subscription_id', $pendingSub->id)->first();
            if ($payment && $payment->payment_status === 'waiting_payment') {
                return redirect()->route('subscription.payment', $payment->id);
            }
            if ($payment && $payment->payment_status === 'waiting_verification') {
                return redirect()->route('subscription.waiting');
            }
        }

        // Ambil paket yang sesuai dengan role user
        $package = SubscriptionPackage::where('role_name', $user->status_user)->where('is_active', true)->firstOrFail();

        return view('subscription.index', compact('package'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $package = SubscriptionPackage::findOrFail($request->package_id);

        // Buat Langganan Pending
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_package_id' => $package->id,
            'status' => 'pending',
        ]);

        // Buat Tagihan Pembayaran
        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_package_id' => $package->id,
            'user_subscription_id' => $subscription->id,
            'amount' => $package->price,
            'payment_status' => 'waiting_payment',
        ]);

        return redirect()->route('subscription.payment', $payment->id);
    }

    public function payment($id)
    {
        $payment = Payment::with('package')->findOrFail($id);

        // Proteksi: Hanya user pemilik tagihan yang boleh melihat
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        // Jika status sudah berubah, lempar ke ruang tunggu
        if ($payment->payment_status !== 'waiting_payment') {
            return redirect()->route('subscription.waiting');
        }

        return view('subscription.payment', compact('payment'));
    }

    public function confirmPayment($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        $payment->update(['payment_status' => 'waiting_verification']);

        return redirect()->route('subscription.waiting');
    }

    public function waiting()
    {
        $user = Auth::user();

        // Cek status terbaru
        $hasActive = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expired_at', '>', now())
            ->exists();

        // Jika diam-diam sudah di-approve Super Admin, lempar ke Dashboard!
        if ($hasActive) {
            return redirect()->route('dashboard');
        }

        return view('subscription.waiting');
    }
}
