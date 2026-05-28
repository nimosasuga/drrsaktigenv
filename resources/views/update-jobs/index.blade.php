<!-- PATH FILE: resources/views/update-jobs/index.blade.php -->
@extends('layouts.app')

@section('content')
<style>
    .drr-soft-shell { position: relative; isolation: isolate; }
    .drr-soft-shell::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -2;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(37, 99, 235, .16), transparent 28%),
            radial-gradient(circle at 92% 16%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 55% 95%, rgba(239, 68, 68, .08), transparent 34%),
            linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    }
    .drr-particle { position:absolute; width:7px; height:7px; border-radius:9999px; pointer-events:none; background:rgba(37,99,235,.18); box-shadow:0 10px 28px rgba(37,99,235,.22); animation:drr-float 6s ease-in-out infinite; }
    .drr-particle:nth-child(2) { width:5px; height:5px; background:rgba(16,185,129,.20); animation-delay:-2s; }
    .drr-particle:nth-child(3) { width:10px; height:10px; background:rgba(239,68,68,.14); animation-delay:-4s; }
    @keyframes drr-float { 0%,100%{ transform:translate3d(0,0,0); opacity:.45; } 50%{ transform:translate3d(0,-12px,0); opacity:.9; } }
    .drr-card { background:rgba(255,255,255,.90); border:1px solid rgba(226,232,240,.88); box-shadow:0 14px 34px rgba(15,23,42,.07), 0 1px 0 rgba(255,255,255,.85) inset; backdrop-filter:blur(14px); }
    .drr-press { transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
    .drr-press:active { transform:scale(.985); }
    @media (hover:hover) { .drr-press:hover { transform:translateY(-1px); box-shadow:0 18px 38px rgba(15,23,42,.10); } }
</style>

<div class="drr-soft-shell mx-auto max-w-7xl space-y-3 pb-28 sm:space-y-5 sm:pb-8">
    <span class="drr-particle left-[7%] top-24"></span>
    <span class="drr-particle right-[12%] top-40"></span>
    <span class="drr-particle left-[56%] top-72"></span>

    <div class="rounded-3xl border border-white/70 bg-white/70 px-4 py-3 shadow-lg shadow-slate-900/5 backdrop-blur-xl">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="truncate text-lg font-black tracking-tight text-slate-950 sm:text-2xl">Update Job</h1>
                <p class="truncate text-xs font-semibold text-slate-500 sm:text-sm">Pantau BD dan troubleshooting mekanik dengan cepat.</p>
            </div>
            <div class="hidden rounded-2xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 sm:block">Operational Insight</div>
        </div>
    </div>

    @if(session('success'))
        <div class="drr-card rounded-2xl px-4 py-3 text-xs font-bold text-emerald-700 sm:text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-3xl border border-red-100 bg-gradient-to-br from-red-500 to-rose-600 p-4 text-white shadow-2xl shadow-red-900/20">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-red-100">Total Unit BD</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="text-4xl font-black leading-none">{{ number_format($summary['total_bd_units'] ?? 0, 0, ',', '.') }}</p>
                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-black">Unit unik</span>
            </div>
            <p class="mt-3 text-xs font-semibold text-red-50">Unit breakdown berdasarkan data filter aktif.</p>
        </div>

        <div class="drr-card rounded-3xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-500">Troubleshooting</p>
                    <p class="mt-1 text-xs font-bold text-slate-500">{{ $summary['current_month_label'] ?? 'Bulan berjalan' }}</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black text-blue-700">Bulan ini</span>
            </div>
            <p class="mt-4 text-4xl font-black leading-none text-slate-950">{{ number_format($summary['troubleshooting_current_month'] ?? 0, 0, ',', '.') }}</p>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-blue-600" style="width: {{ min(100, max(8, (($summary['troubleshooting_current_month'] ?? 0) * 12))) }}%"></div>
            </div>
        </div>

        <div class="drr-card rounded-3xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Pembanding</p>
                    <p class="mt-1 text-xs font-bold text-slate-500">{{ $summary['previous_month_label'] ?? 'Bulan sebelumnya' }}</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black text-slate-700">Sebelumnya</span>
            </div>
            <p class="mt-4 text-4xl font-black leading-none text-slate-950">{{ number_format($summary['troubleshooting_previous_month'] ?? 0, 0, ',', '.') }}</p>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-slate-500" style="width: {{ min(100, max(8, (($summary['troubleshooting_previous_month'] ?? 0) * 12))) }}%"></div>
            </div>
        </div>

        @php
            $delta = $summary['troubleshooting_delta'] ?? 0;
            $trend = $summary['troubleshooting_trend'] ?? 'Stabil';
            $trendClass = $delta > 0 ? 'from-amber-500 to-orange-600 text-white' : ($delta < 0 ? 'from-emerald-500 to-green-600 text-white' : 'from-slate-700 to-slate-900 text-white');
            $trendNote = $delta > 0 ? 'Naik, perlu perhatian.' : ($delta < 0 ? 'Turun, performa membaik.' : 'Stabil dari bulan sebelumnya.');
        @endphp
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $trendClass }} p-4 shadow-2xl shadow-slate-900/15">
            <div class="absolute -bottom-8 -right-8 h-28 w-28 rounded-full bg-white/10"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-80">Trend TS</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="text-4xl font-black leading-none">{{ $delta > 0 ? '+' : '' }}{{ $delta }}</p>
                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-black">{{ $trend }}</span>
            </div>
            <p class="mt-3 text-xs font-semibold opacity-90">{{ $trendNote }}</p>
        </div>
    </div>

    <details class="drr-card overflow-hidden rounded-3xl">
        <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-black text-slate-900">
            <span>Filter Data</span><span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] text-slate-600">Tap</span>
        </summary>
        <form action="{{ route('update-jobs.index') }}" method="GET" class="grid grid-cols-1 gap-3 border-t border-slate-100 p-4 sm:grid-cols-2 xl:grid-cols-6">
            <input type="month" name="month_filter" value="{{ request('month_filter') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
            <select name="year_filter" class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                @forelse($years as $year)<option value="{{ $year }}" {{ (int) request('year_filter', $selectedYear) === (int) $year ? 'selected' : '' }}>{{ $year }}</option>@empty<option value="{{ now()->year }}">{{ now()->year }}</option>@endforelse
            </select>
            <select name="customer_filter" class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"><option value="">Semua Customer</option>@foreach($customers as $cust)<option value="{{ $cust }}" {{ request('customer_filter') == $cust ? 'selected' : '' }}>{{ $cust }}</option>@endforeach</select>
            <select name="pic_filter" class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"><option value="">Semua PIC</option>@foreach($pics as $pic)<option value="{{ $pic }}" {{ request('pic_filter') == $pic ? 'selected' : '' }}>{{ $pic }}</option>@endforeach</select>
            <select name="location_filter" class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"><option value="">Semua Lokasi</option>@foreach($locations as $location)<option value="{{ $location }}" {{ request('location_filter') == $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach</select>
            <select name="status_filter" class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"><option value="">Semua Status</option>@foreach($statuses as $status)<option value="{{ $status }}" {{ request('status_filter') == $status ? 'selected' : '' }}>{{ $status }}</option>@endforeach</select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari S/N, PIC, customer, lokasi..." class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100 sm:col-span-2 xl:col-span-4">
            <div class="grid grid-cols-2 gap-2 sm:col-span-2 xl:col-span-2"><button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-black text-white shadow-lg shadow-slate-900/15 hover:bg-slate-800">Terapkan</button><a href="{{ route('update-jobs.index') }}" class="rounded-2xl border border-red-100 bg-red-50 px-4 py-2 text-center text-sm font-black text-red-600 hover:bg-red-100">Reset</a></div>
        </form>
    </details>

    <div class="space-y-3">
        @forelse ($groupedJobs as $monthGroup)
            <details class="drr-card drr-press overflow-hidden rounded-3xl" open>
                <summary class="cursor-pointer list-none px-4 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h2 class="truncate text-base font-black text-slate-950 sm:text-lg">{{ $monthGroup['name'] }}</h2><p class="text-[11px] font-semibold text-slate-500">{{ $monthGroup['total'] }} job · {{ $monthGroup['pic_total'] }} PIC · {{ $monthGroup['customer_location_total'] }} area</p></div><div class="flex shrink-0 gap-1"><span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-700">R {{ $monthGroup['rfu_total'] }}</span><span class="rounded-full bg-red-50 px-2 py-1 text-[10px] font-black text-red-700">B {{ $monthGroup['breakdown_total'] }}</span></div></div></summary>
                <div class="space-y-3 border-t border-slate-100 bg-white/45 p-3">
                    @foreach($monthGroup['pics'] as $picGroup)
                        <details class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 shadow-md shadow-slate-900/5">
                            <summary class="cursor-pointer list-none px-3 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h3 class="truncate text-sm font-black text-slate-900">{{ $picGroup['name'] }}</h3><p class="text-[11px] font-semibold text-slate-500">{{ $picGroup['total'] }} job · {{ $picGroup['customer_location_total'] }} area</p></div><div class="flex shrink-0 gap-1"><span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-700">{{ $picGroup['rfu_total'] }}</span><span class="rounded-full bg-red-50 px-2 py-1 text-[10px] font-black text-red-700">{{ $picGroup['breakdown_total'] }}</span></div></div></summary>
                            <div class="space-y-2 border-t border-slate-100 bg-slate-50/80 p-2">
                                @foreach($picGroup['customer_locations'] as $customerLocationGroup)
                                    <details class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                        <summary class="cursor-pointer list-none px-3 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h4 class="truncate text-xs font-black uppercase tracking-wide text-slate-800">{{ $customerLocationGroup['name'] }}</h4><p class="text-[11px] font-semibold text-slate-500">{{ $customerLocationGroup['unit_total'] }} unit · {{ $customerLocationGroup['total'] }} job</p></div><span class="shrink-0 rounded-full bg-blue-50 px-2 py-1 text-[10px] font-black text-blue-700">Lihat</span></div></summary>
                                        <div class="space-y-2 border-t border-slate-100 bg-slate-50 p-2 sm:grid sm:grid-cols-2 sm:gap-2 sm:space-y-0 xl:grid-cols-3">
                                            @foreach($customerLocationGroup['jobs'] as $job)
                                                @php $status = strtoupper((string) ($job->status_unit ?? '')); $statusClass = $status === 'RFU' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : (in_array($status, ['B/D', 'BD', 'BREAKDOWN']) ? 'bg-red-50 text-red-700 border-red-100' : 'bg-amber-50 text-amber-700 border-amber-100'); @endphp
                                                <a href="{{ route('update-jobs.show', $job->id) }}" class="drr-press block rounded-2xl border border-white bg-white p-3 shadow-lg shadow-slate-900/8 active:scale-[0.99]"><div class="flex items-start justify-between gap-2"><div class="min-w-0"><p class="truncate text-sm font-black text-slate-950">{{ $job->unit_type ?? '-' }}</p><p class="truncate text-xs font-bold text-slate-500">SN {{ $job->serial_number ?? '-' }}</p></div><span class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-black {{ $statusClass }}">{{ $job->status_unit ?? '-' }}</span></div><div class="mt-3 grid grid-cols-3 gap-2 text-[11px]"><div><p class="font-bold text-slate-400">Tanggal</p><p class="font-black text-slate-700">{{ $job->work_date ? \Carbon\Carbon::parse($job->work_date)->format('d/m/y') : '-' }}</p></div><div><p class="font-bold text-slate-400">HM</p><p class="font-black text-slate-700">{{ number_format((float) $job->hour_meter, 0, ',', '.') }}</p></div><div><p class="font-bold text-slate-400">Type</p><p class="truncate font-black text-slate-700">{{ $job->job_type ?? '-' }}</p></div></div><div class="mt-3 rounded-xl bg-slate-50/90 p-2 ring-1 ring-slate-100"><p class="line-clamp-2 text-[11px] font-medium leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit($job->problem ?? '-', 90) }}</p></div><div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2"><span class="text-[11px] font-bold text-slate-500">#{{ $job->id }}</span><span class="text-[11px] font-black text-blue-600">Lihat Detail</span></div></a>
                                            @endforeach
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </details>
        @empty
            <div class="drr-card rounded-3xl p-8 text-center"><h3 class="text-base font-black text-slate-900">Data Tidak Ditemukan</h3><p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">Riwayat Update Job kosong atau tidak ada data yang cocok dengan filter.</p><a href="{{ route('update-jobs.index') }}" class="mt-4 inline-flex rounded-2xl bg-slate-900 px-5 py-2 text-sm font-black text-white">Reset Filter</a></div>
        @endforelse
    </div>

    <a href="{{ route('update-jobs.create') }}" class="fixed bottom-24 right-4 z-40 flex h-14 w-14 items-center justify-center rounded-3xl bg-blue-600 text-3xl font-black leading-none text-white shadow-2xl shadow-blue-700/35 ring-8 ring-blue-500/10 transition active:scale-95 hover:bg-blue-700 sm:bottom-8 sm:right-8" aria-label="Buat Job Baru"><span class="-mt-1">+</span></a>
</div>
@endsection
