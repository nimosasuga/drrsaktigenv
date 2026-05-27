<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('content')

<!-- Header & Profil User -->
<div
    class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 mb-8 relative overflow-hidden ring-1 ring-slate-900/5">
    <!-- Dekorasi Background -->
    <div
        class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20">
    </div>
    <div
        class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20">
    </div>

    <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start justify-between">
        <div class="flex items-center flex-col sm:flex-row text-center sm:text-left">
            <div
                class="h-20 w-20 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-3xl shadow-inner border border-blue-200 shrink-0">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-6">
                <h1
                    class="text-3xl font-bold text-slate-900 tracking-tight flex items-center justify-center sm:justify-start">
                    Selamat Datang, {{ explode(' ', trim($user->name))[0] }}!
                    @if($user->is_verified || $user->status_user === 'super_admin')
                    <!-- Badge Biru Verified -->
                    <svg class="w-6 h-6 text-blue-500 ml-2 shrink-0" viewBox="0 0 24 24" fill="currentColor"
                        title="Akun Terverifikasi">
                        <path fill-rule="evenodd"
                            d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 11.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                            clip-rule="evenodd" />
                    </svg>
                    @endif
                </h1>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 uppercase tracking-wider border border-slate-200">
                        Role: {{ str_replace('_', ' ', $user->status_user) }}
                    </span>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        NRPP: {{ $user->nrpp }}
                    </span>
                    @if($user->branch)
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        Branch: {{ $user->branch }}
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 sm:mt-0 text-center sm:text-right flex flex-col items-center sm:items-end">
            @if($user->status_user === 'super_admin')
            <div
                class="inline-flex items-center px-4 py-2 rounded-xl bg-purple-50 border border-purple-100 text-purple-700">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z"
                        clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 opacity-80">Status Lisensi
                    </p>
                    <p class="text-sm font-bold">Akses Penuh (Bypass)</p>
                </div>
            </div>
            @elseif($subscription)
            <div
                class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 shadow-sm">
                <svg class="w-8 h-8 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                    </path>
                </svg>
                <div class="text-left">
                    <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Lisensi Terverifikasi</p>
                    <p class="text-sm font-bold">Aktif hingga {{ $subscription->expired_at->format('d M Y') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<h2 class="text-lg font-bold text-slate-800 mb-4 px-1">Ringkasan Aset Perusahaan</h2>

<!-- Grid Kartu Ringkasan (Stats Cards) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

    <!-- Kartu 1: Total -->
    <div
        class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow ring-1 ring-slate-900/5">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500">Total Unit Terdaftar</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($totalAssets, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Kartu 2: Rental -->
    <div
        class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow ring-1 ring-slate-900/5">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500">Status RENTAL</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($rentalAssets, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Kartu 3: Backup -->
    <div
        class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow ring-1 ring-slate-900/5">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-amber-50 text-amber-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500">Status BACKUP</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($backupAssets, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Kartu 4: Ditarik -->
    <div
        class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow ring-1 ring-slate-900/5">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-red-50 text-red-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500">Status DITARIK</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($ditarikAssets, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action (Akses Cepat) -->
<div
    class="bg-linear-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 sm:p-10 shadow-lg text-white relative overflow-hidden">
    <!-- Dekorasi Gelombang -->
    <div class="absolute right-0 bottom-0 opacity-10">
        <svg width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M300 200V0C260.603 24.3642 225.264 63.854 213.91 106.671C200.749 156.295 244.381 184.811 300 200Z"
                fill="white" />
        </svg>
    </div>

    <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between">
        <div class="text-center sm:text-left mb-6 sm:mb-0">
            <h3 class="text-2xl font-bold mb-2">Akses Cepat Manajemen Aset</h3>
            <p class="text-blue-100 max-w-lg">Lihat detail keseluruhan unit, pantau riwayat operasional, dan cari
                berdasarkan Nomor Serial dengan cepat.</p>
        </div>
        <a href="{{ route('assets.index') }}"
            class="inline-flex items-center px-6 py-3 border-2 border-white rounded-xl text-base font-bold text-white hover:bg-white hover:text-blue-700 focus:outline-none transition-colors">
            Buka Tabel Aset
            <svg class="ml-2 -mr-1 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3">
                </path>
            </svg>
        </a>
    </div>
</div>
@endsection
