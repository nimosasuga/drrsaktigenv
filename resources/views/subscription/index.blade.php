<!-- resources/views/subscription/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-slate-900 mb-4">Aktivasi Lisensi Sistem</h2>
        <p class="text-lg text-slate-600">Akun Anda belum memiliki lisensi aktif. Silakan pilih paket lisensi yang
            sesuai dengan peran (role) Anda untuk melanjutkan akses ke Dashboard DRR SAKTI GEN V.</p>
    </div>

    <!-- Pricing Card Style Enterprise -->
    <div
        class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden relative ring-1 ring-slate-900/5">
        <!-- Aksesoris Warna Biru di Atas -->
        <div class="h-2 w-full bg-blue-600"></div>

        <div class="p-8 sm:p-12 text-center">
            <p class="text-sm font-bold text-blue-600 uppercase tracking-widest mb-2">PAKET KHUSUS {{
                strtoupper($package->role_name) }}</p>
            <h3 class="text-2xl font-bold text-slate-900 mb-6">{{ $package->package_name }}</h3>

            <div class="flex justify-center items-baseline mb-8">
                <span class="text-5xl font-extrabold text-slate-900">Rp{{ number_format($package->price, 0, ',', '.')
                    }}</span>
                <span class="text-xl font-medium text-slate-500 ml-2">/ bulan</span>
            </div>

            <ul class="space-y-4 text-left max-w-sm mx-auto mb-10">
                <li class="flex items-start">
                    <svg class="h-6 w-6 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-slate-600">Akses penuh ke sistem sesuai role <b>{{
                            ucfirst($package->role_name) }}</b></span>
                </li>
                <li class="flex items-start">
                    <svg class="h-6 w-6 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-slate-600">Manajemen Aset dan Pelaporan</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-6 w-6 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-slate-600">Verifikasi akun otomatis (Badge Verified)</span>
                </li>
            </ul>

            <form method="POST" action="{{ route('subscription.store') }}">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package->id }}">
                <button type="submit"
                    class="w-full inline-flex justify-center items-center py-4 px-8 border border-transparent rounded-2xl shadow-lg shadow-blue-600/30 text-lg font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-1">
                    Beli Lisensi Sekarang
                    <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>
            <p class="mt-4 text-xs text-slate-400">Proses pembayaran aman dan cepat via GOPAY.</p>
        </div>
    </div>
</div>
@endsection
