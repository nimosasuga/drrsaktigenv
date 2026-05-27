<!-- resources/views/subscription/waiting.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6">
    <div
        class="bg-white rounded-3xl border border-slate-200 shadow-lg overflow-hidden text-center p-10 sm:p-14 relative ring-1 ring-slate-900/5">

        <!-- Animasi Ping / Loading Sederhana dengan Tailwind -->
        <div class="relative flex justify-center items-center w-24 h-24 mx-auto mb-8">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-20"></span>
            <div class="relative inline-flex rounded-full h-20 w-20 bg-blue-50 items-center justify-center">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <h2 class="text-3xl font-bold text-slate-900 mb-4">Menunggu Verifikasi</h2>
        <p class="text-lg text-slate-600 max-w-md mx-auto mb-10 leading-relaxed">
            Terima kasih! Konfirmasi pembayaran Anda telah kami terima dan saat ini sedang dalam antrean verifikasi oleh
            Super Admin.
        </p>

        <div class="bg-slate-50 rounded-2xl p-6 inline-block mx-auto border border-slate-100">
            <p class="text-sm text-slate-500 font-medium mb-1">Status Lisensi Saat Ini:</p>
            <span
                class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-amber-100 text-amber-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                PENDING APPROVAL
            </span>
        </div>

        <div class="mt-10 pt-8 border-t border-slate-100">
            <button onclick="window.location.reload();"
                class="inline-flex items-center px-6 py-3 border border-slate-300 shadow-sm text-sm font-medium rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Cek Status Terbaru
            </button>
        </div>
    </div>
</div>
@endsection
