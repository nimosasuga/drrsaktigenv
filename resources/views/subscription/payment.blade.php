<!-- resources/views/subscription/payment.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4 sm:px-6">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-lg overflow-hidden relative ring-1 ring-slate-900/5">
        <div class="h-2 w-full bg-blue-600"></div>

        <div class="p-8 sm:p-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900">Instruksi Pembayaran</h2>
                <p class="text-slate-500 mt-2">Selesaikan pembayaran Anda, lalu konfirmasi ke Admin melalui WhatsApp.</p>
            </div>

            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 mb-6">
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-slate-200">
                    <span class="text-slate-500 font-medium">Total Tagihan</span>
                    <span class="text-3xl font-extrabold text-slate-900">Rp{{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-sm text-slate-500">Metode Pembayaran</span>
                        <span class="text-sm font-bold text-slate-900 bg-blue-100 px-3 py-1 rounded-full">
                            {{ $payment->payment_method ?: ($paymentSetting->payment_method ?? 'Transfer Manual') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-sm text-slate-500">Nomor Tujuan</span>
                        <span class="text-lg font-bold text-slate-900 tracking-wider text-right">
                            {{ $payment->receiver_number ?: ($paymentSetting->receiver_number ?? '-') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-sm text-slate-500">Atas Nama</span>
                        <span class="text-sm font-bold text-slate-900 text-right">{{ $payment->receiver_name ?: ($paymentSetting->receiver_name ?? '-') }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pt-2">
                        <span class="text-sm text-slate-500">Paket Lisensi</span>
                        <span class="text-sm font-medium text-slate-700 text-right">
                            {{ $payment->package->package_name }} (Role: {{ ucfirst($payment->package->role_name) }})
                        </span>
                    </div>
                </div>
            </div>

            @if(($paymentSetting->is_qris_active ?? false) && $paymentSetting->qris_image_path)
                <div class="bg-white rounded-3xl p-5 border border-slate-200 mb-6 text-center shadow-sm">
                    <p class="text-sm font-black text-slate-900">QRIS Pembayaran</p>
                    <p class="mt-1 text-xs text-slate-500">Scan QRIS di bawah ini sesuai nominal tagihan.</p>
                    <img src="{{ $paymentSetting->qrisUrl() }}" alt="QRIS Pembayaran" class="mx-auto mt-4 w-full max-w-xs rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                </div>
            @endif

            @if($paymentSetting->payment_note ?? null)
                <div class="bg-blue-50 rounded-2xl p-5 border border-blue-200 mb-6">
                    <p class="text-sm font-bold text-blue-800">Catatan Pembayaran</p>
                    <p class="text-sm text-blue-700 mt-1 leading-relaxed whitespace-pre-line">{{ $paymentSetting->payment_note }}</p>
                </div>
            @endif

            <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-200 mb-6">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100">
                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-800">Konfirmasi Pembayaran via WhatsApp</p>
                        <p class="text-sm text-emerald-700 mt-1 leading-relaxed">
                            Setelah transfer berhasil, klik tombol konfirmasi. Sistem akan mengubah status pembayaran menjadi
                            <strong>Menunggu Verifikasi</strong> dan membuka chat WhatsApp Admin.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-8 rounded-r-xl">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700">
                            Klik tombol di bawah ini <strong>HANYA JIKA</strong> Anda sudah berhasil transfer sesuai nominal tagihan. Jangan asal klik, nanti admin ngejar bukti transfer.
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('subscription.confirm', $payment->id) }}">
                @csrf
                <button type="submit" class="w-full inline-flex justify-center items-center py-4 px-8 border border-transparent rounded-2xl shadow-lg shadow-emerald-600/30 text-lg font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all transform hover:-translate-y-1">
                    Saya Sudah Membayar - Konfirmasi via WhatsApp
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
