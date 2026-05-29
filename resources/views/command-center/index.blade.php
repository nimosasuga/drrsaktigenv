<!-- PATH FILE: resources/views/command-center/index.blade.php -->
@extends('layouts.app')

@section('content')
@php
    $monthLabels = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $maxMonthly = max($monthlyTotals ?: [1]);
    $maxPic = max(array_column($picScores, 'total') ?: [1]);
@endphp

<div class="mx-auto max-w-7xl pb-28">
    <div class="mb-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.25em] text-blue-600">Command Center</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Koordinator & Sect Head Intelligence</h1>
                <p class="mt-2 max-w-3xl text-sm font-semibold leading-relaxed text-slate-500">
                    Pusat kendali statistik performa, ekspor data, dan import CSV Excel-friendly untuk modul operasional DRR SAKTI GEN V.
                </p>
            </div>
            <a href="{{ route('command-center.index') }}" class="w-fit rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm">Reset Filter</a>
        </div>

        <section class="mt-5 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Filter Analisa</h2>
                <p class="mt-1 text-xs font-bold text-slate-500">Filter ini memengaruhi statistik, grafik, ranking PIC, analisa performa, dan export CSV.</p>
            </div>

            <form method="GET" action="{{ route('command-center.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Modul</label>
                    <select name="module" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                        <option value="all" {{ $filters['module'] === 'all' ? 'selected' : '' }}>Semua Modul</option>
                        @foreach($modules as $moduleKey => $module)
                            <option value="{{ $moduleKey }}" {{ $filters['module'] === $moduleKey ? 'selected' : '' }}>{{ $module['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Tahun</label>
                    <select name="year" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ (int) $filters['year'] === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Bulan</label>
                    <select name="month" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                        <option value="">Semua Bulan</option>
                        @foreach($monthLabels as $monthNumber => $monthLabel)
                            <option value="{{ $monthNumber }}" {{ (int) ($filters['month'] ?? 0) === (int) $monthNumber ? 'selected' : '' }}>{{ $monthLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Status</label>
                    <select name="status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                        <option value="">Semua Status</option>
                        @foreach($filterOptions['statuses'] as $status)
                            <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">PIC</label>
                    <select name="pic" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                        <option value="">Semua PIC</option>
                        @foreach($filterOptions['pics'] as $pic)
                            <option value="{{ $pic }}" {{ $filters['pic'] === $pic ? 'selected' : '' }}>{{ $pic }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Customer</label>
                    <select name="customer" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                        <option value="">Semua Customer</option>
                        @foreach($filterOptions['customers'] as $customer)
                            <option value="{{ $customer }}" {{ $filters['customer'] === $customer ? 'selected' : '' }}>{{ $customer }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Location</label>
                    <select name="location" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                        <option value="">Semua Location</option>
                        @foreach($filterOptions['locations'] as $location)
                            <option value="{{ $location }}" {{ $filters['location'] === $location ? 'selected' : '' }}>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-900/20">Terapkan Filter</button>
                </div>
            </form>
        </section>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700 shadow-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-3xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wider text-slate-400">Total Aktivitas Filter</p><p class="mt-3 text-4xl font-black text-slate-950">{{ number_format($summary['total_records']) }}</p><p class="mt-2 text-xs font-bold text-slate-500">Mengikuti filter aktif.</p></div>
        <div class="rounded-3xl border border-indigo-100 bg-white p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wider text-slate-400">Modul Terbaca</p><p class="mt-3 text-4xl font-black text-slate-950">{{ number_format($summary['total_modules']) }}</p><p class="mt-2 text-xs font-bold text-slate-500">Jumlah modul dalam filter.</p></div>
        <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wider text-slate-400">Asset Aktif</p><p class="mt-3 text-4xl font-black text-emerald-700">{{ number_format($summary['asset_active']) }}</p><p class="mt-2 text-xs font-bold text-slate-500">Status selain DITARIK.</p></div>
        <div class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wider text-slate-400">Asset Ditarik</p><p class="mt-3 text-4xl font-black text-rose-700">{{ number_format($summary['asset_withdrawn']) }}</p><p class="mt-2 text-xs font-bold text-slate-500">Unit sudah keluar dari proses aktif.</p></div>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-5 flex items-center justify-between gap-3"><div><h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Grafik Aktivitas Bulanan</h2><p class="mt-1 text-xs font-bold text-slate-500">Tahun {{ $filters['year'] }}.</p></div><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">Peak: {{ number_format($summary['peak_month_total']) }}</span></div>
            <div class="grid grid-cols-12 items-end gap-2 rounded-3xl bg-slate-50 p-4">
                @foreach($monthLabels as $month => $label)
                    @php $value = (int) ($monthlyTotals[$month] ?? 0); $height = $maxMonthly > 0 ? max(8, round(($value / $maxMonthly) * 160)) : 8; $selected = (int) ($filters['month'] ?? 0) === (int) $month; @endphp
                    <div class="flex flex-col items-center gap-2"><div class="flex h-44 w-full items-end justify-center rounded-2xl {{ $selected ? 'bg-blue-50 ring-2 ring-blue-200' : 'bg-white' }} p-1 shadow-inner"><div class="w-full rounded-xl bg-blue-600" style="height: {{ $height }}px"></div></div><p class="text-[10px] font-black {{ $selected ? 'text-blue-700' : 'text-slate-500' }}">{{ $label }}</p><p class="text-[10px] font-black text-slate-900">{{ $value }}</p></div>
                @endforeach
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="mb-5"><h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Top PIC Performance</h2><p class="mt-1 text-xs font-bold text-slate-500">Ranking berbasis jumlah aktivitas tercatat.</p></div>
            <div class="space-y-3">@forelse($picScores as $index => $pic)@php $width = $maxPic > 0 ? max(8, round(($pic['total'] / $maxPic) * 100)) : 8; @endphp<div class="rounded-2xl border border-slate-100 bg-slate-50 p-3"><div class="flex items-center justify-between gap-3"><p class="truncate text-sm font-black text-slate-900">#{{ $index + 1 }} {{ $pic['name'] }}</p><p class="text-sm font-black text-blue-700">{{ $pic['total'] }}</p></div><div class="mt-3 h-2 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full bg-blue-600" style="width: {{ $width }}%"></div></div></div>@empty<div class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm font-bold text-slate-500">Belum ada data PIC pada filter ini.</div>@endforelse</div>
        </section>
    </div>

    <section class="mt-5 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="mb-5"><h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Analisa Performa Tajam per PIC</h2><p class="mt-1 text-xs font-bold text-slate-500">Analisa ini membaca data lintas Update Job, Battery, Charger, Delivery, dan Penarikan.</p></div>
        <div class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4"><h3 class="text-sm font-black text-slate-900">Produktivitas per Bulan</h3><div class="mt-4 space-y-2">@forelse($performanceInsights['monthly_productivity'] as $row)<div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 text-sm font-bold shadow-sm"><span>{{ $row['pic'] }} · {{ $monthLabels[$row['month']] ?? $row['month'] }}</span><span class="text-blue-700">{{ $row['total'] }}</span></div>@empty<p class="text-sm font-bold text-slate-500">Belum ada data.</p>@endforelse</div></div>
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4"><h3 class="text-sm font-black text-slate-900">Beban Kerja Customer / Location</h3><div class="mt-4 space-y-2">@forelse($performanceInsights['customer_location_load'] as $row)<div class="rounded-2xl bg-white px-4 py-3 shadow-sm"><div class="flex items-center justify-between gap-3 text-sm font-bold"><span>{{ $row['customer'] }}</span><span class="text-blue-700">{{ $row['total'] }}</span></div><p class="mt-1 text-xs font-bold text-slate-500">{{ $row['location'] }}</p></div>@empty<p class="text-sm font-bold text-slate-500">Belum ada data.</p>@endforelse</div></div>
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4"><h3 class="text-sm font-black text-slate-900">Rasio RFU vs BREAKDOWN</h3><div class="mt-4 space-y-2">@forelse($performanceInsights['rfu_breakdown_ratio'] as $row)<div class="rounded-2xl bg-white px-4 py-3 shadow-sm"><div class="flex items-center justify-between text-sm font-black"><span>{{ $row['pic'] }}</span><span class="text-emerald-700">{{ $row['rfu_rate'] }}% RFU</span></div><p class="mt-1 text-xs font-bold text-slate-500">RFU: {{ $row['rfu'] }} · BREAKDOWN: {{ $row['breakdown'] }} · Lainnya: {{ $row['other'] }}</p></div>@empty<p class="text-sm font-bold text-slate-500">Belum ada data.</p>@endforelse</div></div>
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4"><h3 class="text-sm font-black text-slate-900">Unit Paling Sering Bermasalah</h3><div class="mt-4 space-y-2">@forelse($performanceInsights['troubled_units'] as $row)<div class="rounded-2xl bg-white px-4 py-3 shadow-sm"><div class="flex items-center justify-between text-sm font-black"><span>{{ $row['serial_number'] }}</span><span class="text-rose-700">{{ $row['total'] }}</span></div><p class="mt-1 text-xs font-bold text-slate-500">{{ $row['unit_type'] }} · {{ $row['customer'] }} · {{ $row['location'] }}</p></div>@empty<p class="text-sm font-bold text-slate-500">Belum ada data.</p>@endforelse</div></div>
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4 xl:col-span-2"><h3 class="text-sm font-black text-slate-900">Rekomendasi Part Terbanyak</h3><div class="mt-4 grid gap-2 md:grid-cols-2">@forelse($performanceInsights['top_recommendations'] as $row)<div class="rounded-2xl bg-white px-4 py-3 shadow-sm"><div class="flex items-center justify-between gap-3 text-sm font-black"><span>{{ $row['part_name'] }}</span><span class="text-blue-700">Qty {{ $row['qty_total'] }}</span></div><p class="mt-1 text-xs font-bold text-slate-500">PN: {{ $row['part_number'] }} · {{ $row['total'] }} rekomendasi</p></div>@empty<p class="text-sm font-bold text-slate-500">Belum ada data rekomendasi part.</p>@endforelse</div></div>
        </div>
    </section>

    <section class="mt-5 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="mb-5"><h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Import / Export Excel-Friendly</h2><p class="mt-1 text-xs font-bold text-slate-500">Export mengikuti filter aktif. Import bersifat insert-only, tidak overwrite data lama.</p></div>
        <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
            @foreach($moduleStats as $stat)
                <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4"><div class="flex items-start justify-between gap-3"><div><h3 class="text-sm font-black text-slate-900">{{ $stat['label'] }}</h3><p class="mt-1 text-xs font-bold text-slate-500">{{ $stat['table'] }} · {{ number_format($stat['year_total']) }} data sesuai filter</p></div><a href="{{ route($stat['route']) }}" class="rounded-xl bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm">Buka</a></div><div class="mt-4 grid grid-cols-2 gap-2"><a href="{{ route('command-center.export', array_merge(['module' => $stat['key']], $exportQuery)) }}" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-center text-xs font-black text-white shadow-lg shadow-blue-900/20">Export CSV</a><span class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-xs font-black text-slate-700">Import CSV</span></div><form action="{{ route('command-center.import', $stat['key']) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-3">@csrf<input type="file" name="file" accept=".csv,.txt" required class="block w-full text-xs font-bold text-slate-700"><button type="submit" class="w-full rounded-2xl bg-amber-600 px-4 py-2.5 text-xs font-black text-white">Upload CSV</button></form>@if(!empty($stat['status_counts']))<div class="mt-4 flex flex-wrap gap-2">@foreach($stat['status_counts'] as $status => $total)<span class="rounded-full bg-white px-3 py-1 text-[11px] font-black text-slate-600 shadow-sm">{{ $status }}: {{ $total }}</span>@endforeach</div>@endif</div>
            @endforeach
        </div>
    </section>
</div>
@endsection
