<!-- PATH FILE: resources/views/command-center/index.blade.php -->
@extends('layouts.app')

@section('content')
@php
    $monthLabels = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $maxMonthly = max($monthlyTotals ?: [1]);
    $maxPic = max(array_column($picScores, 'total') ?: [1]);
@endphp

<div class="mx-auto w-full max-w-7xl overflow-x-hidden px-1 pb-28 sm:px-0">
    <div class="mb-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-black uppercase tracking-[0.25em] text-blue-600">Command Center</p>
                <h1 class="mt-2 wrap-break-word text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Koordinator & Sect Head Intelligence</h1>
                <p class="mt-2 max-w-3xl text-sm font-semibold leading-relaxed text-slate-500">
                    Pusat kendali statistik performa, ekspor data, dan import CSV Excel-friendly untuk modul operasional DRR SAKTI GEN V.
                </p>
            </div>
            <a href="{{ route('command-center.index') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-black text-slate-700 shadow-sm sm:w-fit">Reset Filter</a>
        </div>

        <section class="mt-5 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="mb-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Filter Analisa</h2>
                <p class="mt-1 text-xs font-bold text-slate-500">Status memakai standar RFU, Breakdown, Monitoring, dan Waiting Part.</p>
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

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-command-center.stat-card
            label="Total Aktivitas Filter"
            :value="number_format($summary['total_records'])"
            description="Mengikuti filter aktif."
            border-class="border-blue-100"
        />
        <x-command-center.stat-card
            label="Modul Terbaca"
            :value="number_format($summary['total_modules'])"
            description="Jumlah modul dalam filter."
            border-class="border-indigo-100"
        />
        <x-command-center.stat-card
            label="Asset Aktif"
            :value="number_format($summary['asset_active'])"
            description="Status selain DITARIK."
            border-class="border-emerald-100"
            value-class="text-emerald-700"
        />
        <x-command-center.stat-card
            label="Asset Ditarik"
            :value="number_format($summary['asset_withdrawn'])"
            description="Unit keluar dari proses aktif."
            border-class="border-rose-100"
            value-class="text-rose-700"
        />
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-3">
        <section class="rounded-3xl border border-slate-100 bg-white p-4 shadow-sm xl:col-span-2">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div class="min-w-0"><h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Grafik Aktivitas Bulanan</h2><p class="mt-1 text-xs font-bold text-slate-500">Tahun {{ $filters['year'] }}.</p></div><span class="w-max rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">Peak: {{ number_format($summary['peak_month_total']) }}</span></div>
            <div class="grid grid-cols-6 items-end gap-1 rounded-3xl bg-slate-50 p-2 sm:grid-cols-12 sm:gap-2 sm:p-3">
                @foreach($monthLabels as $month => $label)
                    @php $value = (int) ($monthlyTotals[$month] ?? 0); $height = $maxMonthly > 0 ? max(8, round(($value / $maxMonthly) * 140)) : 8; $mobileHeight = min(104, $height); $selected = (int) ($filters['month'] ?? 0) === (int) $month; @endphp
                    <div class="flex min-w-0 flex-col items-center gap-1 sm:gap-2"><div class="flex h-28 w-full items-end justify-center rounded-2xl {{ $selected ? 'bg-blue-50 ring-2 ring-blue-200' : 'bg-white' }} p-1 shadow-inner sm:h-36"><div class="w-full rounded-xl bg-blue-600 sm:hidden" style="height: {{ $mobileHeight }}px"></div><div class="hidden w-full rounded-xl bg-blue-600 sm:block" style="height: {{ $height }}px"></div></div><p class="text-[9px] font-black {{ $selected ? 'text-blue-700' : 'text-slate-500' }} sm:text-[10px]">{{ $label }}</p><p class="text-[9px] font-black text-slate-900 sm:text-[10px]">{{ $value }}</p></div>
                @endforeach
            </div>
        </section>
        <section class="rounded-3xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="mb-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Top PIC Performance</h2>
                <p class="mt-1 text-xs font-bold text-slate-500">Ranking aktivitas tercatat.</p>
            </div>
            <div class="space-y-2">
                @forelse($picScores as $index => $pic)
                    @php $width = $maxPic > 0 ? max(8, round(($pic['total'] / $maxPic) * 100)) : 8; @endphp
                    <x-command-center.metric-card
                        :title="'#' . ($index + 1) . ' ' . $pic['name']"
                        :value="$pic['total']"
                        background-class="bg-slate-50"
                        padding-class="p-3"
                    >
                        <div class="h-2 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full bg-blue-600" style="width: {{ $width }}%"></div>
                        </div>
                    </x-command-center.metric-card>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm font-bold text-slate-500">Belum ada data PIC pada filter ini.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-5 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Analisa Performa Tajam</h2>
                <p class="mt-1 text-xs font-bold text-slate-500">Hijau = RFU tinggi, merah = Breakdown tinggi, amber = Monitoring / Waiting Part perlu diawasi.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-[11px] font-black">
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">RFU Aman</span>
                <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-700">Breakdown Risiko</span>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-700">Monitoring / Waiting</span>
            </div>
        </div>

        <div class="grid gap-3 xl:grid-cols-3">
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Distribusi Status</h3>
                <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                    @forelse($performanceInsights['status_distribution'] as $row)
                        @php
                            $statusClass = match ($row['status']) {
                                'RFU' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
                                'Breakdown' => 'border-rose-100 bg-rose-50 text-rose-700',
                                'Monitoring', 'Waiting Part' => 'border-amber-100 bg-amber-50 text-amber-700',
                                default => 'border-slate-100 bg-white text-slate-700',
                            };
                        @endphp
                        <x-command-center.metric-card
                            :title="$row['status']"
                            :value="number_format($row['total'])"
                            border-class=""
                            :background-class="$statusClass"
                            title-class=""
                            badge-class="bg-transparent text-current"
                        />
                    @empty
                        <p class="text-sm font-bold text-slate-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Tipe Pekerjaan Update Job</h3>
                <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                    @forelse($performanceInsights['job_type_distribution'] as $row)
                        <x-command-center.metric-card
                            :title="$row['job_type']"
                            :value="number_format($row['total'])"
                        />
                    @empty
                        <p class="text-sm font-bold text-slate-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Produktivitas Bulanan</h3>
                <div class="mt-3 space-y-2">
                    @forelse($performanceInsights['monthly_productivity'] as $row)
                        <x-command-center.metric-card
                            :title="$row['pic'] . ' · ' . ($monthLabels[$row['month']] ?? $row['month'])"
                            :value="number_format($row['total'])"
                            :with-border="false"
                        />
                    @empty
                        <p class="text-sm font-bold text-slate-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-3 grid gap-3 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Status per PIC</h3>
                <div class="mt-3 space-y-2">
                    @forelse($performanceInsights['status_by_pic'] as $row)
                        @php
                            $riskClass = $row['risk_rate'] >= 50
                                ? 'bg-rose-50 text-rose-700 border-rose-100'
                                : ($row['risk_rate'] >= 25 ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100');
                        @endphp
                        <x-command-center.metric-card
                            :title="$row['pic']"
                            :value="'Risk ' . $row['risk_rate'] . '%'"
                            padding-class="p-3"
                            :badge-class="'border px-3 py-1 text-[11px] font-black ' . $riskClass"
                        >
                            <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] font-black sm:grid-cols-4">
                                <span class="wrap-break-word rounded-xl bg-emerald-50 px-2 py-1 text-emerald-700">RFU {{ $row['rfu'] }}</span>
                                <span class="wrap-break-word rounded-xl bg-rose-50 px-2 py-1 text-rose-700">BD {{ $row['breakdown'] }}</span>
                                <span class="wrap-break-word rounded-xl bg-amber-50 px-2 py-1 text-amber-700">Mon {{ $row['monitoring'] }}</span>
                                <span class="wrap-break-word rounded-xl bg-amber-50 px-2 py-1 text-amber-700">WP {{ $row['waiting_part'] }}</span>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-emerald-600" style="width: {{ min(100, $row['rfu_rate']) }}%"></div>
                            </div>
                            <p class="mt-1 text-[11px] font-bold text-slate-500">RFU Rate {{ $row['rfu_rate'] }}%</p>
                        </x-command-center.metric-card>
                    @empty
                        <p class="text-sm font-bold text-slate-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Beban Customer / Location</h3>
                <div class="mt-3 space-y-2">
                    @forelse($performanceInsights['customer_location_load'] as $row)
                        @php
                            $loadClass = $row['total'] >= 20 ? 'text-rose-700 bg-rose-50' : ($row['total'] >= 10 ? 'text-amber-700 bg-amber-50' : 'text-blue-700 bg-blue-50');
                        @endphp
                        <x-command-center.metric-card
                            :title="$row['customer']"
                            :value="number_format($row['total'])"
                            :subtitle="$row['location']"
                            :with-border="false"
                            :badge-class="'text-[11px] font-black ' . $loadClass"
                        />
                    @empty
                        <p class="text-sm font-bold text-slate-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-3 grid gap-3 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Unit Paling Sering Bermasalah</h3>
                <div class="mt-3 space-y-2">
                    @forelse($performanceInsights['troubled_units'] as $row)
                        <x-command-center.metric-card
                            :title="$row['serial_number']"
                            :value="number_format($row['total']) . 'x'"
                            :subtitle="$row['unit_type'] . ' · ' . $row['customer'] . ' · ' . $row['location']"
                            border-class="border-rose-100"
                            badge-class="bg-rose-50 text-[11px] text-rose-700"
                        />
                    @empty
                        <p class="text-sm font-bold text-slate-500">Belum ada data.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Rekomendasi Part Terbanyak</h3>
                <div class="mt-3 space-y-2">
                    @forelse($performanceInsights['top_recommendations'] as $row)
                        <x-command-center.metric-card
                            :title="$row['part_name']"
                            :value="'Qty ' . number_format($row['qty_total'])"
                            :subtitle="'PN: ' . $row['part_number'] . ' · ' . $row['total'] . ' rekomendasi'"
                            :with-border="false"
                            badge-class="bg-blue-50 text-[11px] text-blue-700"
                        />
                    @empty
                        <p class="text-sm font-bold text-slate-500">Belum ada data rekomendasi part.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="mt-5 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
        <div class="mb-4"><h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Import / Export Excel-Friendly</h2><p class="mt-1 text-xs font-bold text-slate-500">Export mengikuti filter aktif. Import bersifat insert-only, tidak overwrite data lama.</p></div>
        <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
            @foreach($moduleStats as $stat)
                <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="wrap-break-word text-sm font-black text-slate-900">{{ $stat['label'] }}</h3>
                            <p class="mt-1 wrap-break-word text-xs font-bold text-slate-500">{{ $stat['table'] }} · {{ number_format($stat['year_total']) }} data sesuai filter</p>
                        </div>
                        <a href="{{ route($stat['route']) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm sm:w-auto">Buka</a>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <a href="{{ route('command-center.export', array_merge(['module' => $stat['key']], $exportQuery)) }}" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-center text-xs font-black text-white shadow-lg shadow-blue-900/20">Export CSV</a>
                        <span class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-xs font-black text-slate-700">Import CSV</span>
                    </div>
                    <form action="{{ route('command-center.import', $stat['key']) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                        @csrf
                        <input type="file" name="file" accept=".csv,.txt" required class="block w-full max-w-full text-xs font-bold text-slate-700 file:mr-2 file:rounded-xl file:border-0 file:bg-white file:px-3 file:py-2 file:text-xs file:font-black file:text-slate-700">
                        <button type="submit" class="w-full rounded-2xl bg-amber-600 px-4 py-2.5 text-xs font-black text-white">Upload CSV</button>
                    </form>
                    @if(!empty($stat['status_counts']))
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($stat['status_counts'] as $status => $total)
                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-black text-slate-600 shadow-sm">{{ $status }}: {{ $total }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
