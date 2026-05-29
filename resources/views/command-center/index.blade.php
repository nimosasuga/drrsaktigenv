<!-- PATH FILE: resources/views/command-center/index.blade.php -->
@extends('layouts.app')

@section('content')
@php
$monthLabels = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 =>
'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
$maxMonthly = max($monthlyTotals ?: [1]);
$maxPic = max(array_column($picScores, 'total') ?: [1]);
@endphp

<div class="mx-auto max-w-7xl pb-28">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-black uppercase tracking-[0.25em] text-blue-600">Command Center</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Koordinator & Sect Head Intelligence</h1>
            <p class="mt-2 max-w-3xl text-sm font-semibold leading-relaxed text-slate-500">
                Pusat kendali statistik performa, ekspor data, dan import CSV Excel-friendly untuk modul operasional DRR
                SAKTI GEN V.
            </p>
        </div>

        <form method="GET" action="{{ route('command-center.index') }}"
            class="grid gap-3 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Modul</label>
                <select name="module"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                    <option value="all" {{ $filters['module']==='all' ? 'selected' : '' }}>Semua Modul</option>
                    @foreach($modules as $moduleKey => $module)
                    <option value="{{ $moduleKey }}" {{ $filters['module']===$moduleKey ? 'selected' : '' }}>
                        {{ $module['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Tahun</label>
                <select name="year"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                    @foreach($years as $year)
                    <option value="{{ $year }}" {{ (int) $filters['year']===(int) $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Bulan</label>
                <select name="month"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                    <option value="">Semua Bulan</option>
                    @foreach($monthLabels as $monthNumber => $monthLabel)
                    <option value="{{ $monthNumber }}" {{ (int) ($filters['month'] ?? 0)===(int) $monthNumber
                        ? 'selected' : '' }}>
                        {{ $monthLabel }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Status</label>
                <select name="status"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                    <option value="">Semua Status</option>
                    @foreach($filterOptions['statuses'] as $status)
                    <option value="{{ $status }}" {{ $filters['status']===$status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">PIC</label>
                <select name="pic"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                    <option value="">Semua PIC</option>
                    @foreach($filterOptions['pics'] as $pic)
                    <option value="{{ $pic }}" {{ $filters['pic']===$pic ? 'selected' : '' }}>
                        {{ $pic }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Customer</label>
                <select name="customer"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                    <option value="">Semua Customer</option>
                    @foreach($filterOptions['customers'] as $customer)
                    <option value="{{ $customer }}" {{ $filters['customer']===$customer ? 'selected' : '' }}>
                        {{ $customer }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Location</label>
                <select name="location"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                    <option value="">Semua Location</option>
                    @foreach($filterOptions['locations'] as $location)
                    <option value="{{ $location }}" {{ $filters['location']===$location ? 'selected' : '' }}>
                        {{ $location }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-900/20">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div
        class="mb-5 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700 shadow-sm">
        {{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="mb-5 rounded-3xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700 shadow-sm">{{
        $errors->first() }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Total Aktivitas {{ $filters['year'] }}
            </p>
            <p class="mt-3 text-4xl font-black text-slate-950">{{ number_format($summary['total_records']) }}</p>
            <p class="mt-2 text-xs font-bold text-slate-500">Akumulasi semua modul operasional.</p>
        </div>
        <div class="rounded-3xl border border-indigo-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Modul Terbaca</p>
            <p class="mt-3 text-4xl font-black text-slate-950">{{ number_format($summary['total_modules']) }}</p>
            <p class="mt-2 text-xs font-bold text-slate-500">Asset, Job, Battery, Charger, Delivery, Penarikan.</p>
        </div>
        <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Asset Aktif</p>
            <p class="mt-3 text-4xl font-black text-emerald-700">{{ number_format($summary['asset_active']) }}</p>
            <p class="mt-2 text-xs font-bold text-slate-500">Status selain DITARIK.</p>
        </div>
        <div class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Asset Ditarik</p>
            <p class="mt-3 text-4xl font-black text-rose-700">{{ number_format($summary['asset_withdrawn']) }}</p>
            <p class="mt-2 text-xs font-bold text-slate-500">Unit sudah keluar dari proses aktif.</p>
        </div>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Grafik Aktivitas Bulanan</h2>
                    <p class="mt-1 text-xs font-bold text-slate-500">Gabungan seluruh modul pada tahun {{ $selectedYear
                        }}.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">Peak: {{
                    number_format($summary['peak_month_total']) }}</span>
            </div>

            <div class="grid grid-cols-12 items-end gap-2 rounded-3xl bg-slate-50 p-4">
                @foreach($monthLabels as $month => $label)
                @php
                $value = (int) ($monthlyTotals[$month] ?? 0);
                $height = $maxMonthly > 0 ? max(8, round(($value / $maxMonthly) * 160)) : 8;
                @endphp
                <div class="flex flex-col items-center gap-2">
                    <div class="flex h-44 w-full items-end justify-center rounded-2xl bg-white p-1 shadow-inner"
                        title="{{ $label }}: {{ $value }} aktivitas">
                        <div class="w-full rounded-xl bg-blue-600" style="height: {{ $height }}px"></div>
                    </div>
                    <p class="text-[10px] font-black text-slate-500">{{ $label }}</p>
                    <p class="text-[10px] font-black text-slate-900">{{ $value }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Top PIC Performance</h2>
                <p class="mt-1 text-xs font-bold text-slate-500">Ranking berbasis jumlah aktivitas tercatat.</p>
            </div>

            <div class="space-y-3">
                @forelse($picScores as $index => $pic)
                @php $width = $maxPic > 0 ? max(8, round(($pic['total'] / $maxPic) * 100)) : 8; @endphp
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="truncate text-sm font-black text-slate-900">#{{ $index + 1 }} {{ $pic['name'] }}</p>
                        <p class="text-sm font-black text-blue-700">{{ $pic['total'] }}</p>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                        <div class="h-full rounded-full bg-blue-600" style="width: {{ $width }}%"></div>
                    </div>
                </div>
                @empty
                <div class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm font-bold text-slate-500">
                    Belum ada data PIC pada tahun ini.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-5 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="mb-5">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Import / Export Excel-Friendly</h2>
            <p class="mt-1 text-xs font-bold text-slate-500">Format saat ini CSV UTF-8 yang bisa dibuka langsung di
                Excel. Import bersifat insert-only, tidak overwrite data lama.</p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
            @foreach($moduleStats as $stat)
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-black text-slate-900">{{ $stat['label'] }}</h3>
                        <p class="mt-1 text-xs font-bold text-slate-500">{{ $stat['table'] }} · {{
                            number_format($stat['year_total']) }} data tahun {{ $filters['year'] }}</p>
                    </div>
                    <a href="{{ route($stat['route']) }}"
                        class="rounded-xl bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm">Buka</a>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('command-center.export', array_merge(['module' => $stat['key']], $exportQuery)) }}"
                        class="rounded-2xl bg-blue-600 px-4 py-2.5 text-center text-xs font-black text-white shadow-lg shadow-blue-900/20">Export
                        CSV</a>
                    <button type="button"
                        onclick="document.getElementById('import-{{ $stat['key'] }}').classList.toggle('hidden')"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700">Import</button>
                </div>

                <form id="import-{{ $stat['key'] }}" action="{{ route('command-center.import', $stat['key']) }}"
                    method="POST" enctype="multipart/form-data"
                    class="mt-3 hidden space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                    @csrf
                    <p class="text-xs font-bold leading-relaxed text-amber-800">Gunakan file CSV hasil export sebagai
                        template. Jangan edit kolom header sembarangan.</p>
                    <input type="file" name="file" accept=".csv,.txt" required
                        class="block w-full text-xs font-bold text-slate-700 file:mr-3 file:rounded-xl file:border-0 file:bg-white file:px-3 file:py-2 file:text-xs file:font-black file:text-slate-700">
                    <button type="submit"
                        onclick="return confirm('Import akan menambah data baru. Tidak ada overwrite otomatis. Lanjutkan?')"
                        class="w-full rounded-2xl bg-amber-600 px-4 py-2.5 text-xs font-black text-white">Upload
                        CSV</button>
                </form>

                @if(!empty($stat['status_counts']))
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($stat['status_counts'] as $status => $total)
                    <span class="rounded-full bg-white px-3 py-1 text-[11px] font-black text-slate-600 shadow-sm">{{
                        $status }}: {{ $total }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
