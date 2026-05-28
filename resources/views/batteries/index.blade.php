<!-- PATH FILE: resources/views/batteries/index.blade.php -->
@extends('layouts.app')

@section('content')
<style>
    .battery-shell { position: relative; isolation: isolate; }
    .battery-shell::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -2;
        pointer-events: none;
        background:
            radial-gradient(circle at 12% 8%, rgba(16, 185, 129, .18), transparent 28%),
            radial-gradient(circle at 92% 18%, rgba(132, 204, 22, .18), transparent 26%),
            radial-gradient(circle at 52% 96%, rgba(6, 182, 212, .12), transparent 34%),
            linear-gradient(180deg, #f7fee7 0%, #ecfeff 48%, #f8fafc 100%);
    }
    .battery-spark { position:absolute; width:7px; height:7px; border-radius:9999px; pointer-events:none; background:rgba(132,204,22,.28); box-shadow:0 10px 28px rgba(16,185,129,.24); animation:battery-float 5.5s ease-in-out infinite; }
    .battery-spark:nth-child(2) { width:5px; height:5px; background:rgba(6,182,212,.22); animation-delay:-1.8s; }
    .battery-spark:nth-child(3) { width:10px; height:10px; background:rgba(16,185,129,.18); animation-delay:-3.4s; }
    @keyframes battery-float { 0%,100%{ transform:translate3d(0,0,0) scale(1); opacity:.42; } 50%{ transform:translate3d(0,-14px,0) scale(1.15); opacity:.9; } }
    .battery-card { background:rgba(255,255,255,.88); border:1px solid rgba(187,247,208,.72); box-shadow:0 14px 34px rgba(20,83,45,.08), 0 1px 0 rgba(255,255,255,.85) inset; backdrop-filter:blur(14px); }
    .battery-press { transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
    .battery-press:active { transform:scale(.985); }
    @media (hover:hover) { .battery-press:hover { transform:translateY(-1px); box-shadow:0 18px 40px rgba(20,83,45,.12); } }
</style>

<div class="battery-shell mx-auto max-w-7xl space-y-3 pb-28 sm:space-y-5 sm:pb-8">
    <span class="battery-spark left-[8%] top-24"></span>
    <span class="battery-spark right-[14%] top-44"></span>
    <span class="battery-spark left-[58%] top-72"></span>

    <div class="rounded-3xl border border-white/70 bg-white/70 px-4 py-3 shadow-lg shadow-emerald-900/5 backdrop-blur-xl">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="truncate text-lg font-black tracking-tight text-slate-950 sm:text-2xl">Manajemen Battery</h1>
                <p class="truncate text-xs font-semibold text-slate-500 sm:text-sm">Pantau battery, peremajaan, RFU, dan breakdown secara cepat.</p>
            </div>
            <div class="hidden rounded-2xl border border-lime-100 bg-lime-50 px-3 py-2 text-xs font-black text-lime-700 sm:block">Electric Battery View</div>
        </div>
    </div>

    @if(session('success'))
        <div class="battery-card rounded-2xl px-4 py-3 text-xs font-bold text-emerald-700 sm:text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-500 to-lime-500 p-4 text-white shadow-2xl shadow-emerald-900/20">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/12"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-50">Battery Job</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="text-4xl font-black leading-none">{{ number_format($summary['total_jobs'] ?? 0, 0, ',', '.') }}</p>
                <span class="rounded-full bg-white/18 px-3 py-1 text-[11px] font-black">Data</span>
            </div>
            <p class="mt-3 text-xs font-semibold text-emerald-50">Total pekerjaan battery sesuai filter aktif.</p>
        </div>

        <div class="battery-card rounded-3xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-600">Battery SN</p>
                    <p class="mt-1 text-xs font-bold text-slate-500">Unique battery</p>
                </div>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-[10px] font-black text-cyan-700">SN</span>
            </div>
            <p class="mt-4 text-4xl font-black leading-none text-slate-950">{{ number_format($summary['unique_batteries'] ?? 0, 0, ',', '.') }}</p>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-cyan-500" style="width: {{ min(100, max(8, (($summary['unique_batteries'] ?? 0) * 12))) }}%"></div></div>
        </div>

        <div class="battery-card rounded-3xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600">RFU</p>
                    <p class="mt-1 text-xs font-bold text-slate-500">Ready battery unit</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-700">Ready</span>
            </div>
            <p class="mt-4 text-4xl font-black leading-none text-slate-950">{{ number_format($summary['total_rfu'] ?? 0, 0, ',', '.') }}</p>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, max(8, (($summary['total_rfu'] ?? 0) * 12))) }}%"></div></div>
        </div>

        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500 to-orange-600 p-4 text-white shadow-2xl shadow-orange-900/15">
            <div class="absolute -bottom-8 -right-8 h-28 w-28 rounded-full bg-white/10"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-80">Battery BD</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="text-4xl font-black leading-none">{{ number_format($summary['total_breakdown'] ?? 0, 0, ',', '.') }}</p>
                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-black">Alert</span>
            </div>
            <p class="mt-3 text-xs font-semibold opacity-90">Pekerjaan battery dengan status breakdown.</p>
        </div>
    </div>

    <details class="battery-card overflow-hidden rounded-3xl">
        <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-black text-slate-900">
            <span>Filter Battery</span><span class="rounded-full bg-lime-50 px-3 py-1 text-[11px] text-lime-700">Tap</span>
        </summary>
        <form action="{{ route('batteries.index') }}" method="GET" class="grid grid-cols-1 gap-3 border-t border-emerald-100 p-4 sm:grid-cols-2 xl:grid-cols-7">
            <input type="month" name="month_filter" value="{{ request('month_filter') }}" class="rounded-2xl border border-emerald-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
            <select name="year_filter" class="rounded-2xl border border-emerald-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                @forelse($years as $year)<option value="{{ $year }}" {{ (int) request('year_filter', $selectedYear) === (int) $year ? 'selected' : '' }}>{{ $year }}</option>@empty<option value="{{ now()->year }}">{{ now()->year }}</option>@endforelse
            </select>
            <select name="customer_filter" class="rounded-2xl border border-emerald-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"><option value="">Semua Customer</option>@foreach($customers as $cust)<option value="{{ $cust }}" {{ request('customer_filter') == $cust ? 'selected' : '' }}>{{ $cust }}</option>@endforeach</select>
            <select name="pic_filter" class="rounded-2xl border border-emerald-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"><option value="">Semua PIC</option>@foreach($pics as $pic)<option value="{{ $pic }}" {{ request('pic_filter') == $pic ? 'selected' : '' }}>{{ $pic }}</option>@endforeach</select>
            <select name="location_filter" class="rounded-2xl border border-emerald-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"><option value="">Semua Lokasi</option>@foreach($locations as $location)<option value="{{ $location }}" {{ request('location_filter') == $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach</select>
            <select name="status_filter" class="rounded-2xl border border-emerald-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"><option value="">Semua Status</option>@foreach($statuses as $status)<option value="{{ $status }}" {{ request('status_filter') == $status ? 'selected' : '' }}>{{ $status }}</option>@endforeach</select>
            <select name="category_filter" class="rounded-2xl border border-emerald-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"><option value="">Semua Category</option>@foreach($categories as $category)<option value="{{ $category }}" {{ request('category_filter') == $category ? 'selected' : '' }}>{{ $category }}</option>@endforeach</select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari SN battery, SN unit, PIC, customer, lokasi..." class="rounded-2xl border border-emerald-100 bg-white/80 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 sm:col-span-2 xl:col-span-5">
            <div class="grid grid-cols-2 gap-2 sm:col-span-2 xl:col-span-2"><button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-black text-white shadow-lg shadow-emerald-900/15 hover:bg-emerald-700">Terapkan</button><a href="{{ route('batteries.index') }}" class="rounded-2xl border border-red-100 bg-red-50 px-4 py-2 text-center text-sm font-black text-red-600 hover:bg-red-100">Reset</a></div>
        </form>
    </details>

    <div class="space-y-3">
        @forelse ($groupedBatteries as $monthGroup)
            <details class="battery-card battery-press overflow-hidden rounded-3xl" open>
                <summary class="cursor-pointer list-none px-4 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h2 class="truncate text-base font-black text-slate-950 sm:text-lg">{{ $monthGroup['name'] }}</h2><p class="text-[11px] font-semibold text-slate-500">{{ $monthGroup['total'] }} job · {{ $monthGroup['pic_total'] }} PIC · {{ $monthGroup['battery_total'] }} battery</p></div><div class="flex shrink-0 gap-1"><span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-700">R {{ $monthGroup['rfu_total'] }}</span><span class="rounded-full bg-orange-50 px-2 py-1 text-[10px] font-black text-orange-700">B {{ $monthGroup['breakdown_total'] }}</span></div></div></summary>
                <div class="space-y-3 border-t border-emerald-100 bg-white/45 p-3">
                    @foreach($monthGroup['pics'] as $picGroup)
                        <details class="overflow-hidden rounded-2xl border border-emerald-100/80 bg-white/95 shadow-md shadow-emerald-900/5">
                            <summary class="cursor-pointer list-none px-3 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h3 class="truncate text-sm font-black text-slate-900">{{ $picGroup['name'] }}</h3><p class="text-[11px] font-semibold text-slate-500">{{ $picGroup['total'] }} job · {{ $picGroup['battery_total'] }} battery · {{ $picGroup['customer_location_total'] }} area</p></div><div class="flex shrink-0 gap-1"><span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-700">{{ $picGroup['rfu_total'] }}</span><span class="rounded-full bg-orange-50 px-2 py-1 text-[10px] font-black text-orange-700">{{ $picGroup['breakdown_total'] }}</span></div></div></summary>
                            <div class="space-y-2 border-t border-emerald-100 bg-emerald-50/50 p-2">
                                @foreach($picGroup['customer_locations'] as $customerLocationGroup)
                                    <details class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
                                        <summary class="cursor-pointer list-none px-3 py-3"><div class="flex items-center justify-between gap-3"><div class="min-w-0"><h4 class="truncate text-xs font-black uppercase tracking-wide text-slate-800">{{ $customerLocationGroup['name'] }}</h4><p class="text-[11px] font-semibold text-slate-500">{{ $customerLocationGroup['battery_total'] }} battery · {{ $customerLocationGroup['unit_total'] }} unit · {{ $customerLocationGroup['total'] }} job</p></div><span class="shrink-0 rounded-full bg-lime-50 px-2 py-1 text-[10px] font-black text-lime-700">Lihat</span></div></summary>
                                        <div class="space-y-2 border-t border-emerald-100 bg-emerald-50/40 p-2 sm:grid sm:grid-cols-2 sm:gap-2 sm:space-y-0 xl:grid-cols-3">
                                            @foreach($customerLocationGroup['batteries'] as $bat)
                                                @php $status = strtoupper((string) ($bat->status_unit ?? '')); $statusClass = $status === 'RFU' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : (in_array($status, ['B/D', 'BD', 'BREAKDOWN']) ? 'bg-orange-50 text-orange-700 border-orange-100' : 'bg-cyan-50 text-cyan-700 border-cyan-100'); @endphp
                                                <a href="{{ route('batteries.show', $bat->id) }}" class="battery-press block rounded-2xl border border-white bg-white p-3 shadow-lg shadow-emerald-900/8 active:scale-[0.99]"><div class="flex items-start justify-between gap-2"><div class="min-w-0"><p class="truncate text-sm font-black text-slate-950">{{ $bat->category_job ?? 'Battery Job' }}</p><p class="truncate text-xs font-bold text-slate-500">Battery {{ $bat->sn_battery ?? '-' }}</p></div><span class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-black {{ $statusClass }}">{{ $bat->status_unit ?? '-' }}</span></div><div class="mt-3 grid grid-cols-3 gap-2 text-[11px]"><div><p class="font-bold text-slate-400">Tanggal</p><p class="font-black text-slate-700">{{ $bat->date ? \Carbon\Carbon::parse($bat->date)->format('d/m/y') : '-' }}</p></div><div><p class="font-bold text-slate-400">SN Unit</p><p class="truncate font-black text-slate-700">{{ $bat->serial_number ?? '-' }}</p></div><div><p class="font-bold text-slate-400">Type</p><p class="truncate font-black text-slate-700">{{ $bat->battery_type ?? '-' }}</p></div></div><div class="mt-3 rounded-xl bg-lime-50/70 p-2 ring-1 ring-lime-100"><p class="line-clamp-2 text-[11px] font-medium leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit($bat->problem ?? $bat->job_type ?? '-', 90) }}</p></div><div class="mt-3 flex items-center justify-between border-t border-emerald-100 pt-2"><span class="text-[11px] font-bold text-slate-500">#{{ $bat->id }}</span><span class="text-[11px] font-black text-emerald-600">Lihat Detail</span></div></a>
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
            <div class="battery-card rounded-3xl p-8 text-center"><h3 class="text-base font-black text-slate-900">Data Tidak Ditemukan</h3><p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">Riwayat battery kosong atau tidak ada data yang cocok dengan filter.</p><a href="{{ route('batteries.index') }}" class="mt-4 inline-flex rounded-2xl bg-emerald-600 px-5 py-2 text-sm font-black text-white">Reset Filter</a></div>
        @endforelse
    </div>

    <a href="{{ route('batteries.create') }}" class="fixed bottom-24 right-4 z-40 flex h-14 w-14 items-center justify-center rounded-3xl bg-emerald-600 text-3xl font-black leading-none text-white shadow-2xl shadow-emerald-700/35 ring-8 ring-emerald-500/10 transition active:scale-95 hover:bg-emerald-700 sm:bottom-8 sm:right-8" aria-label="Tambah Data Battery"><span class="-mt-1">+</span></a>
</div>
@endsection
