<!-- resources/views/admin/subscriptions.blade.php -->
@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi Pembayaran Lisensi</h1>
        <p class="mt-1 text-sm text-slate-500">
            Periksa detail pembayaran user sebelum approve. Jangan jadi tombol sakti tanpa lihat nominal dulu.
        </p>
    </div>

    <a href="{{ route('admin.subscriptions') }}"
        class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
        Refresh Antrean
    </a>
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

<!-- Alert Error -->
@if($errors->any())
<div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md shadow-sm">
    <div class="flex items-start">
        <svg class="h-5 w-5 text-red-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
        </svg>
        <div>
            <p class="text-sm font-bold text-red-700">Gagal memproses approval</p>
            <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

<!-- Ringkasan Antrean -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm ring-1 ring-slate-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Antrean</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $summary['total_queue'] ?? $payments->count() }}</p>
        <p class="mt-1 text-sm text-slate-500">Pembayaran menunggu approval</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm ring-1 ring-slate-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Nominal</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900">
            Rp{{ number_format($summary['total_amount'] ?? $payments->sum('amount'), 0, ',', '.') }}
        </p>
        <p class="mt-1 text-sm text-slate-500">Akumulasi antrean saat ini</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm ring-1 ring-slate-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Submit Hari Ini</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $summary['submitted_today'] ?? 0 }}</p>
        <p class="mt-1 text-sm text-slate-500">Konfirmasi masuk hari ini</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm ring-1 ring-slate-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Antrean Terlama</p>
        @if(!empty($summary['oldest_waiting']))
            <p class="mt-2 text-lg font-extrabold text-slate-900">
                {{ $summary['oldest_waiting']->updated_at?->diffForHumans() ?? '-' }}
            </p>
            <p class="mt-1 text-sm text-slate-500">
                {{ $summary['oldest_waiting']->user->name ?? 'User tidak ditemukan' }}
            </p>
        @else
            <p class="mt-2 text-3xl font-extrabold text-slate-900">-</p>
            <p class="mt-1 text-sm text-slate-500">Tidak ada antrean</p>
        @endif
    </div>
</div>

<div class="space-y-4">
    @forelse($payments as $payment)
        @php
            $user = $payment->user;
            $package = $payment->package;
            $subscription = $payment->subscription;
            $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
            $durationMonths = max(1, (int) ($package->duration_months ?? 1));
            $expiredPreview = now()->addMonths($durationMonths)->format('d M Y');
        @endphp

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-700 font-extrabold">
                        {{ $initial }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900">{{ $user->name ?? 'User tidak ditemukan' }}</h2>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                {{ strtoupper($payment->payment_status) }}
                            </span>
                        </div>
                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
                            <span>NRPP: <strong class="text-slate-700">{{ $user->nrpp ?? '-' }}</strong></span>
                            <span>Branch: <strong class="text-slate-700">{{ $user->branch ?? '-' }}</strong></span>
                            <span>Role: <strong class="text-slate-700">{{ strtoupper($user->status_user ?? '-') }}</strong></span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.subscriptions.approve', $payment->id) }}"
                    onsubmit="return confirm('Pastikan dana sudah masuk. Aktifkan lisensi untuk {{ $user->name ?? 'user ini' }}?');">
                    @csrf
                    <button type="submit"
                        class="w-full lg:w-auto inline-flex items-center justify-center px-5 py-3 border border-transparent rounded-xl shadow-lg shadow-emerald-600/20 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Approve & Aktifkan Lisensi
                    </button>
                </form>
            </div>

            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Kode Payment</p>
                    <p class="mt-2 text-base font-extrabold text-slate-900">#{{ $payment->id }}</p>
                    <p class="mt-1 text-xs text-slate-500">Dibuat: {{ $payment->created_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>

                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Paket Lisensi</p>
                    <p class="mt-2 text-base font-extrabold text-slate-900">{{ $package->package_name ?? '-' }}</p>
                    <p class="mt-1 text-xs text-slate-500">Durasi: {{ $durationMonths }} bulan · Exp preview: {{ $expiredPreview }}</p>
                </div>

                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nominal</p>
                    <p class="mt-2 text-base font-extrabold text-slate-900">Rp{{ number_format($payment->amount, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-slate-500">Metode: {{ $payment->payment_method ?? 'Transfer Manual' }}</p>
                </div>

                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Waktu Konfirmasi</p>
                    <p class="mt-2 text-base font-extrabold text-slate-900">
                        {{ $payment->paid_at?->format('d M Y H:i') ?? $payment->updated_at?->format('d M Y H:i') ?? '-' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">{{ $payment->updated_at?->diffForHumans() ?? '-' }}</p>
                </div>
            </div>

            <div class="px-5 sm:px-6 pb-5 sm:pb-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Rekening / Nomor Tujuan Pembayaran</p>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div>
                            <p class="text-blue-700/70">Metode</p>
                            <p class="font-bold text-blue-900">{{ $payment->payment_method ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-blue-700/70">Nomor Tujuan</p>
                            <p class="font-bold text-blue-900 tracking-wide">{{ $payment->receiver_number ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-blue-700/70">Atas Nama</p>
                            <p class="font-bold text-blue-900">{{ $payment->receiver_name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Subscription</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ strtoupper($subscription->status ?? '-') }}</p>
                    <p class="mt-1 text-xs text-slate-500">
                        Setelah approve: aktif sampai {{ $expiredPreview }}
                    </p>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm ring-1 ring-slate-900/5 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-slate-700 font-bold">Belum ada antrean pembayaran saat ini.</p>
            <p class="mt-1 text-sm text-slate-500">Kalau kosong, berarti admin bisa ngopi sebentar. Tapi jangan lama-lama.</p>
        </div>
    @endforelse
</div>
@endsection
