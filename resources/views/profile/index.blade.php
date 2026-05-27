<!-- resources/views/profile/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Profil Pengguna</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola informasi akun dan kata sandi Anda.</p>
    </div>
</div>

<!-- Alert Sukses -->
@if(session('success'))
<div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-r-md shadow-sm">
    <div class="flex items-center">
        <svg class="h-5 w-5 text-emerald-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

    <!-- Kolom Kiri: Info Profil & Lisensi -->
    <div class="xl:col-span-1 space-y-6">

        <!-- Kartu Identitas Utama -->
        <div
            class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden ring-1 ring-slate-900/5 relative">
            <div class="h-24 bg-linear-to-r from-blue-600 to-indigo-700"></div>
            <div class="px-6 pb-6 relative">
                <div class="flex justify-center -mt-12 mb-4">
                    <div class="h-24 w-24 rounded-2xl bg-white p-1.5 shadow-lg border border-slate-100">
                        <div
                            class="h-full w-full rounded-xl bg-blue-50 flex items-center justify-center text-blue-700 font-bold text-4xl">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center justify-center">
                        {{ $user->name }}
                        @if($user->is_verified || $user->status_user === 'super_admin')
                        <!-- Badge Biru Verified -->
                        <svg class="w-5 h-5 text-blue-500 ml-1.5 shrink-0" viewBox="0 0 24 24" fill="currentColor"
                            title="Akun Terverifikasi">
                            <path fill-rule="evenodd"
                                d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 11.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                                clip-rule="evenodd" />
                        </svg>
                        @endif
                    </h2>
                    <p class="text-sm text-slate-500 font-medium mt-1">ID: {{ $user->nrpp }}</p>

                    <div
                        class="mt-4 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 uppercase tracking-wider border border-slate-200">
                        Role: {{ str_replace('_', ' ', $user->status_user) }}
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <dl class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Cabang (Branch)</dt>
                            <dd class="font-medium text-slate-900">{{ $user->branch ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Status Akun</dt>
                            <dd>
                                @if($user->is_verified)
                                <span class="text-emerald-600 font-bold flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Terverifikasi
                                </span>
                                @else
                                <span class="text-amber-600 font-bold">Belum Diverifikasi</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Bergabung Sejak</dt>
                            <dd class="font-medium text-slate-900">{{ $user->created_at->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Kartu Detail Lisensi -->
        @if($user->status_user === 'super_admin')
        <div class="bg-purple-50 rounded-2xl border border-purple-100 p-6 shadow-sm">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                    </path>
                </svg>
                <h3 class="font-bold text-purple-900">Hak Akses Super Admin</h3>
            </div>
            <p class="text-sm text-purple-700">Akun ini memiliki hak akses penuh ke seluruh fitur sistem dan tidak
                memerlukan perpanjangan lisensi bulanan.</p>
        </div>
        @elseif($subscription)
        <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                        </path>
                    </svg>
                    <h3 class="font-bold text-emerald-900">Lisensi Aktif</h3>
                </div>
                <span class="px-2 py-1 text-xs font-bold bg-emerald-200 text-emerald-800 rounded-lg">1 Bulan</span>
            </div>

            <div class="space-y-3 mt-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-emerald-700 opacity-80">Paket</span>
                    <span class="font-bold text-emerald-900">{{ $subscription->package->package_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-emerald-700 opacity-80">Mulai Aktif</span>
                    <span class="font-bold text-emerald-900">{{ $subscription->started_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-emerald-200/50">
                    <span class="text-emerald-700 opacity-80">Berakhir Pada</span>
                    <span class="font-bold text-red-600">{{ $subscription->expired_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Kolom Kanan: Pengaturan Akun (Ganti Password) -->
    <div class="xl:col-span-2">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm ring-1 ring-slate-900/5">
            <div class="p-6 sm:p-8 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900">Pengaturan Keamanan</h2>
                <p class="mt-1 text-sm text-slate-500">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak
                    agar tetap aman.</p>
            </div>

            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                <div class="p-6 sm:p-8 space-y-6">

                    @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
                        <div class="flex">
                            <div class="shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <ul class="text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-slate-700">Kata Sandi Saat
                            Ini</label>
                        <input type="password" name="current_password" id="current_password" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi Baru</label>
                        <input type="password" name="password" id="password" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                        <p class="mt-1.5 text-xs text-slate-500">Minimal 8 karakter. Disarankan menggunakan kombinasi
                            angka dan huruf.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi
                            Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                </div>

                <div class="bg-slate-50 px-6 py-5 sm:px-8 border-t border-slate-200 rounded-b-3xl">
                    <button type="submit"
                        class="inline-flex justify-center rounded-xl border border-transparent bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
