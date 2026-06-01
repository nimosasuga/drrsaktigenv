<?php
// app/Http/Controllers/SubscriptionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SubscriptionPackage;
use App\Models\UserSubscription;
use App\Models\Payment;
use App\Models\PaymentSetting;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->status_user === 'super_admin' || UserSubscription::where('user_id', $user->id)->where('status', 'active')->where('expired_at', '>', now())->exists()) {
            return redirect()->route('dashboard');
        }

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

        $package = SubscriptionPackage::where('role_name', $user->status_user)->where('is_active', true)->firstOrFail();

        return view('subscription.index', compact('package'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $package = SubscriptionPackage::findOrFail($request->package_id);
        $paymentSetting = PaymentSetting::current();

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_package_id' => $package->id,
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_package_id' => $package->id,
            'user_subscription_id' => $subscription->id,
            'payment_method' => $paymentSetting->payment_method,
            'receiver_name' => $paymentSetting->receiver_name,
            'receiver_number' => $paymentSetting->receiver_number,
            'amount' => $package->price,
            'payment_status' => 'waiting_payment',
        ]);

        return redirect()->route('subscription.payment', $payment->id);
    }

    public function payment($id)
    {
        $payment = Payment::with('package')->findOrFail($id);

        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        if ($payment->payment_status !== 'waiting_payment') {
            return redirect()->route('subscription.waiting');
        }

        $paymentSetting = PaymentSetting::current();

        return view('subscription.payment', compact('payment', 'paymentSetting'));
    }

    public function confirmPayment($id)
    {
        $payment = Payment::with(['package', 'user'])->findOrFail($id);

        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        $payment->update([
            'payment_status' => 'waiting_verification',
            'paid_at' => now(),
        ]);

        $paymentSetting = PaymentSetting::current();
        $user = Auth::user();
        $packageName = $payment->package->package_name ?? '-';
        $amount = 'Rp' . number_format((float) $payment->amount, 0, ',', '.');

        $message = implode("\n", [
            'Halo Admin DRR SAKTI, saya sudah melakukan pembayaran lisensi.',
            '',
            'Nama: ' . ($user->name ?? '-'),
            'Email: ' . ($user->email ?? '-'),
            'Paket: ' . $packageName,
            'Nominal: ' . $amount,
            'Metode: ' . ($payment->payment_method ?? $paymentSetting->payment_method ?? '-'),
            'Kode Pembayaran: #' . $payment->id,
            '',
            'Mohon dibantu verifikasi pembayaran saya. Terima kasih.',
        ]);

        $whatsappUrl = 'https://wa.me/' . $paymentSetting->adminWhatsappNumber() . '?text=' . urlencode($message);

        return redirect()->away($whatsappUrl);
    }

    public function waiting()
    {
        $user = Auth::user();

        $hasActive = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expired_at', '>', now())
            ->exists();

        if ($hasActive) {
            return redirect()->route('dashboard');
        }

        return view('subscription.waiting');
    }
}
