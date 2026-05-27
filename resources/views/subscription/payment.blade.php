<!-- resources/views/subscription/payment.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4 sm:px-6">
    <div
        class="bg-white rounded-3xl border border-slate-200 shadow-lg overflow-hidden relative ring-1 ring-slate-900/5">
        <div class="h-2 w-full bg-blue-600"></div>

        <div class="p-8 sm:p-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900">Instruksi Pembayaran</h2>
                <p class="text-slate-500 mt-2">Selesaikan pembayaran Anda agar lisensi dapat diaktifkan.</p>
            </div>

            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 mb-8">
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-slate-200">
                    <span class="text-slate-500 font-medium">Total Tagihan</span>
                    <span class="text-3xl font-extrabold text-slate-900">Rp{{ number_format($payment->amount, 0, ',',
                        '.') }}</span>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Metode Pembayaran</span>
                        <span class="text-sm font-bold text-slate-900 bg-blue-100 px-3 py-1 rounded-full">{{
                            $payment->payment_method }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Nomor Tujuan</span>
                        <span class="text-lg font-bold text-slate-900 tracking-wider">{{ $payment->receiver_number
                            }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Atas Nama</span>
                        <span class="text-sm font-bold text-slate-900">{{ $payment->receiver_name }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm text-slate-500">Paket Lisensi</span>
                        <span class="text-sm font-medium text-slate-700">{{ $payment->package->package_name }} (Role: {{
                            ucfirst($payment->package->role_name) }})</span>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-8 rounded-r-xl">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700">Pastikan Anda mentransfer sesuai dengan nominal <strong>Total
                                Tagihan</strong> ke nomor tujuan di atas. Klik tombol di bawah ini <strong>HANYA
                                JIKA</strong> Anda sudah berhasil mentransfer.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('subscription.confirm', $payment->id) }}">
                @csrf
                <button type="submit"
                    class="w-full inline-flex justify-center items-center py-4 px-8 border border-transparent rounded-2xl shadow-lg shadow-blue-600/30 text-lg font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-1">
                    Saya Sudah Membayar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
