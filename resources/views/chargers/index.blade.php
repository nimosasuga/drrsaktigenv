<!-- PATH FILE: resources/views/chargers/index.blade.php -->
@extends('layouts.app')

@section('content')
<style>
    .charger-card { background: rgba(255,255,255,.9); border: 1px solid rgba(253,230,138,.8); box-shadow: 0 14px 34px rgba(120,53,15,.08); backdrop-filter: blur(14px); }
    .charger-bg { background: radial-gradient(circle at 10% 10%, rgba(245,158,11,.16), transparent 28%), radial-gradient(circle at 90% 20%, rgba(124,58,237,.13), transparent 30%), linear-gradient(180deg,#fff7ed 0%,#f5f3ff 55%,#f8fafc 100%); }
    .charger-press { transition: transform .16s ease, box-shadow .16s ease; }
    .charger-press:active { transform: scale(.985); }
    @media (hover:hover) { .charger-press:hover { transform: translateY(-1px); box-shadow: 0 18px 40px rgba(88,28,135,.12); } }
</style>

<div class="charger-bg -m-4 min-h-screen p-4 pb-28 sm:m-0 sm:min-h-0 sm:bg-none sm:p-0 sm:pb-8">
<div class="mx-auto max-w-7xl space-y-3 sm:space-y-5">
    <div class="rounded-3xl border border-white/70 bg-white/75 px-4 py-3 shadow-lg shadow-amber-900/5 backdrop-blur-xl">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="truncate text-lg font-black tracking-tight text-slate-950 sm:text-2xl">Management Charger</h1>
                <p class="truncate text-xs font-semibold text-slate-500 sm:text-sm">Pantau charger, voltage issue, RFU, dan breakdown secara cepat.</p>
            </div>
            <div class="hidden rounded-2xl border border-violet-100 bg-violet-50 px-3 py-2 text-xs font-black text-violet-700 sm:block">Voltage View</div>
        </div>
    </div>

    @if(session('success'))
        <div class="charger-card rounded-2xl px-4 py-3 text-xs font-bold text-emerald-700 sm:text-sm">{{ session('success') }}</div>
    @endif

    @php
        $flatChargers = collect();
        foreach ($groupedChargers as $monthGroup) {
            foreach ($monthGroup['pics'] as $picGroup) {
                foreach ($picGroup['customer_locations'] as $customerLocationGroup) {
                    foreach ($customerLocationGroup['chargers'] as $chargerItem) {
                        $flatChargers->push($chargerItem);
                    }
                }
            }
        }
        $popularJobs = $flatChargers->groupBy(fn($item) => $item->category_job ?: ($item->job_type ?: 'Charger Job'))->map(fn($items, $name) => ['name' => $name, 'total' => $items->count()])->sortByDesc('total')->take(3)->values();
        $popularMax = max(1, (int) ($popularJobs->max('total') ?? 1));
    @endphp

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500 to-orange-600 p-4 text-white shadow-2xl shadow-amber-900/20">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-50">Charger Job</p>
            <div class="mt-3 flex items-end justify-between gap-3"><p class="text-4xl font-black leading-none">{{ number_format($summary['total_jobs'] ?? 0, 0, ',', '.') }}</p><span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-black">Data</span></div>
            <p class="mt-3 text-xs font-semibold text-amber-50">Total pekerjaan charger sesuai filter aktif.</p>
        </div>

        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 to-indigo-700 p-4 text-white shadow-2xl shadow-violet-900/15">
            <div class="absolute -bottom-8 -right-8 h-28 w-28 rounded-full bg-white/10"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-80">Charger BD</p>
            <div class="mt-3 flex items-end justify-between gap-3"><p class="text-4xl font-black leading-none">{{ number_format($summary['total_breakdown'] ?? 0, 0, ',', '.') }}</p><span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-black">Alert</span></div>
            <p class="mt-3 text-xs font-semibold opacity-90">Pekerjaan charger dengan status breakdown.</p>
        </div>

        <div class="charger-card rounded-3xl p-4 md:col-span-2">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div><p class="text-[10px] font-black uppercase tracking-[0.18em] text-violet-600">Pekerjaan Populer</p><p class="mt-1 text-xs font-bold text-slate-500">Top 1 sampai 3 berdasarkan data filter aktif</p></div>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-700">Top 3</span>
            </div>
            <div class="overflow-hidden rounded-2xl border border-amber-100 bg-white/80">
                <div class="hidden grid-cols-12 gap-3 border-b border-amber-100 bg-amber-50/70 px-3 py-2 text-[10px] font-black uppercase tracking-wider text-amber-700 sm:grid"><div class="col-span-2">Rank</div><div class="col-span-5">Pekerjaan</div><div class="col-span-2 text-right">Total</div><div class="col-span-3">Grafik</div></div>
                <div class="divide-y divide-amber-100">
                    @forelse($popularJobs as $index => $job)
                        @php $rank=$index+1; $percent=round(($job['total']/$popularMax)*100); $barClass=$rank===1?'bg-amber-500':($rank===2?'bg-violet-500':'bg-indigo-500'); $badgeClass=$rank===1?'bg-amber-600 text-white':($rank===2?'bg-violet-100 text-violet-700':'bg-indigo-100 text-indigo-700'); @endphp
                        <div class="grid grid-cols-1 gap-2 px-3 py-3 sm:grid-cols-12 sm:items-center sm:gap-3">
                            <div class="flex items-center justify-between gap-3 sm:col-span-2 sm:block"><span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full px-2 text-xs font-black {{ $badgeClass }}">#{{ $rank }}</span><span class="text-xs font-black text-slate-900 sm:hidden">{{ number_format($job['total'],0,',','.') }} Data</span></div>
                            <div class="min-w-0 sm:col-span-5"><p class="truncate text-sm font-black text-slate-900">{{ $job['name'] }}</p><p class="text-[11px] font-semibold text-slate-500 sm:hidden">{{ $percent }}% dari pekerjaan teratas</p></div>
                            <div class="hidden text-right text-sm font-black text-slate-800 sm:col-span-2 sm:block">{{ number_format($job['total'],0,',','.') }}</div>
                            <div class="sm:col-span-3"><div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $barClass }}" style="width: {{ max(8,$percent) }}%"></div></div></div>
                        </div>
                    @empty
                        <div class="px-3 py-5 text-center text-sm font-bold text-slate-500">Belum ada data pekerjaan populer.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <details class="charger-card overflow-hidden rounded-3xl">
        <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-black text-slate-900"><span>Filter Charger</span><span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] text-amber-700">Tap</span></summary>
        <form action="{{ route('chargers.index') }}" method="GET" class="grid grid-cols-1 gap-3 border-t border-amber-100 p-4 sm:grid-cols-2 xl:grid-cols-7">
            <input type="month" name="month_filter" value="{{ request('month_filter') }}" class="rounded-2xl border border-amber-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100">
            <select name="year_filter" class="rounded-2xl border border-amber-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100">@forelse($years as $year)<option value="{{ $year }}" {{ (int) request('year_filter', $selectedYear) === (int) $year ? 'selected' : '' }}>{{ $year }}</option>@empty<option value="{{ now()->year }}">{{ now()->year }}</option>@endforelse</select>
            <select name="customer_filter" class="rounded-2xl border border-amber-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100"><option value="">Semua Customer</option>@foreach($customers as $cust)<option value="{{ $cust }}" {{ request('customer_filter') == $cust ? 'selected' : '' }}>{{ $cust }}</option>@endforeach</select>
            <select name="pic_filter" class="rounded-2xl border border-amber-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100"><option value="">Semua PIC</option>@foreach($pics as $pic)<option value="{{ $pic }}" {{ request('pic_filter') == $pic ? 'selected' : '' }}>{{ $pic }}</option>@endforeach</select>
            <select name="location_filter" class="rounded-2xl border border-amber-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100"><option value="">Semua Lokasi</option>@foreach($locations as $location)<option value="{{ $location }}" {{ request('location_filter') == $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach</select>
            <select name="status_filter" class="rounded-2xl border border-amber-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100"><option value="">Semua Status</option>@foreach($statuses as $status)<option value="{{ $status }}" {{ request('status_filter') == $status ? 'selected' : '' }}>{{ $status }}</option>@endforeach</select>
            <select name="category_filter" class="rounded-2xl border border-amber-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100"><option value="">Semua Category</option>@foreach($categories as $category)<option value="{{ $category }}" {{ request('category_filter') == $category ? 'selected' : '' }}>{{ $category }}</option>@endforeach</select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari SN charger, SN unit, PIC, customer, lokasi..." class="rounded-2xl border border-amber-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100 sm:col-span-2 xl:col-span-5">
            <div class="grid grid-cols-2 gap-2 sm:col-span-2 xl:col-span-2"><button type="submit" class="rounded-2xl bg-amber-500 px-4 py-2 text-sm font-black text-white shadow-lg shadow-amber-900/15 hover:bg-amber-600">Terapkan</button><a href="{{ route('chargers.index') }}" class="rounded-2xl border border-red-100 bg-red-50 px-4 py-2 text-center text-sm font-black text-red-600 hover:bg-red-100">Reset</a></div>
        </form>
    </details>

    <div class="space-y-3">
        @forelse ($groupedChargers as $monthGroup)
            <details class="charger-card charger-press overflow-hidden rounded-3xl" open>
                <summary class="cursor-pointer list-none px-4 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h2 class="truncate text-base font-black text-slate-950 sm:text-lg">{{ $monthGroup['name'] }}</h2><p class="text-[11px] font-semibold text-slate-500">{{ $monthGroup['total'] }} job · {{ $monthGroup['pic_total'] }} PIC · {{ $monthGroup['charger_total'] }} charger</p></div><div class="flex shrink-0 gap-1"><span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-700">R {{ $monthGroup['rfu_total'] }}</span><span class="rounded-full bg-violet-50 px-2 py-1 text-[10px] font-black text-violet-700">B {{ $monthGroup['breakdown_total'] }}</span></div></div></summary>
                <div class="space-y-3 border-t border-amber-100 bg-white/45 p-3">
                    @foreach($monthGroup['pics'] as $picGroup)
                        <details class="overflow-hidden rounded-2xl border border-amber-100/80 bg-white/95 shadow-md shadow-amber-900/5">
                            <summary class="cursor-pointer list-none px-3 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h3 class="truncate text-sm font-black text-slate-900">{{ $picGroup['name'] }}</h3><p class="text-[11px] font-semibold text-slate-500">{{ $picGroup['total'] }} job · {{ $picGroup['charger_total'] }} charger · {{ $picGroup['customer_location_total'] }} area</p></div><div class="flex shrink-0 gap-1"><span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-700">{{ $picGroup['rfu_total'] }}</span><span class="rounded-full bg-violet-50 px-2 py-1 text-[10px] font-black text-violet-700">{{ $picGroup['breakdown_total'] }}</span></div></div></summary>
                            <div class="space-y-2 border-t border-amber-100 bg-amber-50/40 p-2">
                                @foreach($picGroup['customer_locations'] as $customerLocationGroup)
                                    <details class="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                                        <summary class="cursor-pointer list-none px-3 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h4 class="truncate text-xs font-black uppercase tracking-wide text-slate-800">{{ $customerLocationGroup['name'] }}</h4><p class="text-[11px] font-semibold text-slate-500">{{ $customerLocationGroup['charger_total'] }} charger · {{ $customerLocationGroup['unit_total'] }} unit · {{ $customerLocationGroup['total'] }} job</p></div><span class="shrink-0 rounded-full bg-violet-50 px-2 py-1 text-[10px] font-black text-violet-700">Lihat</span></div></summary>
                                        <div class="space-y-2 border-t border-amber-100 bg-amber-50/30 p-2 sm:grid sm:grid-cols-2 sm:gap-2 sm:space-y-0 xl:grid-cols-3">
                                            @foreach($customerLocationGroup['chargers'] as $c)
                                                @php $status=strtoupper((string)($c->status_unit ?? '')); $statusClass=$status==='RFU'?'bg-emerald-50 text-emerald-700 border-emerald-100':(in_array($status,['B/D','BD','BREAKDOWN'])?'bg-violet-50 text-violet-700 border-violet-100':'bg-amber-50 text-amber-700 border-amber-100'); @endphp
                                                <a href="{{ route('chargers.show', $c->id) }}" class="charger-press block rounded-2xl border border-white bg-white p-3 shadow-lg shadow-amber-900/8 active:scale-[0.99]"><div class="flex items-start justify-between gap-2"><div class="min-w-0"><p class="truncate text-sm font-black text-slate-950">{{ $c->category_job ?? 'Charger Job' }}</p><p class="truncate text-xs font-bold text-slate-500">Charger {{ $c->sn_charger ?? '-' }}</p></div><span class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-black {{ $statusClass }}">{{ $c->status_unit ?? '-' }}</span></div><div class="mt-3 grid grid-cols-3 gap-2 text-[11px]"><div><p class="font-bold text-slate-400">Tanggal</p><p class="font-black text-slate-700">{{ $c->date ? \Carbon\Carbon::parse($c->date)->format('d/m/y') : '-' }}</p></div><div><p class="font-bold text-slate-400">SN Unit</p><p class="truncate font-black text-slate-700">{{ $c->serial_number ?? '-' }}</p></div><div><p class="font-bold text-slate-400">Type</p><p class="truncate font-black text-slate-700">{{ $c->charger_type ?? '-' }}</p></div></div><div class="mt-3 rounded-xl bg-amber-50/70 p-2 ring-1 ring-amber-100"><p class="line-clamp-2 text-[11px] font-medium leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit($c->problem ?? $c->job_type ?? '-', 90) }}</p></div><div class="mt-3 flex items-center justify-between border-t border-amber-100 pt-2"><span class="text-[11px] font-bold text-slate-500">#{{ $c->id }}</span><span class="text-[11px] font-black text-amber-600">Lihat Detail</span></div></a>
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
            <div class="charger-card rounded-3xl p-8 text-center"><h3 class="text-base font-black text-slate-900">Data Tidak Ditemukan</h3><p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">Riwayat Management Charger kosong atau tidak ada data yang cocok dengan filter.</p><a href="{{ route('chargers.index') }}" class="mt-4 inline-flex rounded-2xl bg-amber-500 px-5 py-2 text-sm font-black text-white">Reset Filter</a></div>
        @endforelse
    </div>

    <a href="{{ route('chargers.create') }}" class="fixed bottom-24 right-4 z-40 flex h-14 w-14 items-center justify-center rounded-3xl bg-amber-500 text-3xl font-black leading-none text-white shadow-2xl shadow-amber-700/35 ring-8 ring-amber-500/10 transition active:scale-95 hover:bg-amber-600 sm:bottom-8 sm:right-8" aria-label="Tambah Data Charger"><span class="-mt-1">+</span></a>
</div>
</div>
@endsection
