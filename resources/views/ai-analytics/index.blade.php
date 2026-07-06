@extends('layouts.app')

@section('content')
@php
    $agingMax = max($agingAnalysis['buckets'] ?: [1]);
    $picMax = max(array_column($picCapacity, 'total') ?: [1]);
    $customerRiskMax = max(array_column($customerRiskMatrix, 'risk_score') ?: [1]);
    $pmGapMax = max(array_column($pmGapByCustomer, 'pending') ?: [1]);
    $monthlyMax = max($monthlyTotals ?: [1]);

    $priorityClass = function ($priority) {
        return match ($priority) {
            'P1' => 'border-red-100 bg-red-50 text-red-700',
            'P2' => 'border-amber-100 bg-amber-50 text-amber-700',
            default => 'border-blue-100 bg-blue-50 text-blue-700',
        };
    };

    $loadClass = function ($label) {
        return match ($label) {
            'Banyak masalah' => 'border-red-100 bg-red-50 text-red-700',
            'Beban tinggi' => 'border-amber-100 bg-amber-50 text-amber-700',
            default => 'border-emerald-100 bg-emerald-50 text-emerald-700',
        };
    };
@endphp

<div class="mx-auto max-w-7xl space-y-5 pb-28">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_360px] lg:p-7">
            <div class="min-w-0">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-blue-600">Pantauan Pintar</p>
                <h1 class="mt-2 wrap-break-word text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    Mana yang Harus Dikerjakan Dulu?
                </h1>
                <p class="mt-2 max-w-3xl text-sm font-semibold leading-relaxed text-slate-500">
                    Halaman ini membantu admin membaca unit yang sering masalah, job yang belum selesai, PM yang belum dikerjakan, dan PIC yang bebannya tinggi.
                </p>
            </div>

            <form method="GET" action="{{ route('ai-analytics.index') }}" class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Tahun</label>
                        <select name="year" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ (int) $filters['year'] === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Bulan</label>
                        <select name="month" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                            <option value="">Semua</option>
                            @foreach($monthLabels as $month => $label)
                                <option value="{{ $month }}" {{ (int) ($filters['month'] ?? 0) === (int) $month ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('ai-analytics.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-black text-slate-700 hover:bg-slate-100">Reset</a>
                    <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-black text-white hover:bg-slate-800">Lihat</button>
                </div>
            </form>
        </div>
    </section>

    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Nilai Kondisi</p>
            <p class="mt-2 text-4xl font-black text-emerald-700">{{ $summary['health_score'] }}</p>
            <p class="mt-1 text-xs font-bold text-slate-500">Makin tinggi makin aman</p>
        </div>
        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Perkiraan Kerjaan</p>
            <p class="mt-2 text-4xl font-black text-blue-700">{{ number_format($forecast['projection'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-bold text-slate-500">{{ $forecast['direction'] }} · perubahan {{ $forecast['growth_rate'] }}%</p>
        </div>
        <div class="rounded-3xl border border-red-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Job Lama Belum Selesai</p>
            <p class="mt-2 text-4xl font-black text-red-700">{{ number_format($agingAnalysis['critical_total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-bold text-slate-500">Belum RFU lebih dari 8 hari</p>
        </div>
        <div class="rounded-3xl border border-cyan-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">PM Belum Dikerjakan</p>
            <p class="mt-2 text-4xl font-black text-cyan-700">{{ number_format($pmOverview['pending'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-bold text-slate-500">{{ $pmOverview['rate'] }}% sudah jalan bulan ini</p>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Saran Kerja Hari Ini</h2>
                    <p class="mt-1 text-xs font-bold text-slate-500">Urutan pekerjaan yang sebaiknya dikejar dulu.</p>
                </div>
                <span class="w-max rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ count($actionPlan) }} aksi</span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($actionPlan as $action)
                    <article class="rounded-2xl border p-4 {{ $priorityClass($action['priority']) }}">
                        <div class="flex items-start gap-3">
                            <span class="shrink-0 rounded-full bg-white/70 px-2.5 py-1 text-xs font-black">{{ $action['priority'] }}</span>
                            <div class="min-w-0">
                                <p class="wrap-break-word text-sm font-black">{{ $action['title'] }}</p>
                                <p class="mt-1 wrap-break-word text-xs font-bold leading-relaxed opacity-80">{{ $action['body'] }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center">
                        <p class="text-sm font-black text-slate-800">Belum ada pekerjaan darurat.</p>
                        <p class="mt-1 text-xs font-bold text-slate-500">Data pada filter ini masih terkendali.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Perkiraan Kerjaan & Lonjakan</h2>
            <p class="mt-1 text-xs font-bold text-slate-500">Melihat bulan mana yang naik/turun tidak biasa.</p>

            <div class="mt-5 rounded-3xl bg-slate-50 p-3">
                <svg viewBox="0 0 680 155" class="h-52 w-full" role="img" aria-label="Perkiraan aktivitas">
                    <polyline points="20,130 660,130" fill="none" stroke="#e2e8f0" stroke-width="2" />
                    <polyline points="{{ $linePoints }}" fill="none" stroke="#2563eb" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach($monthlyTotals as $month => $value)
                        @php
                            $x = 20 + (($month - 1) * 58);
                            $y = 130 - ($monthlyMax > 0 ? (($value / $monthlyMax) * 100) : 0);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#2563eb" />
                        <text x="{{ $x }}" y="149" text-anchor="middle" fill="#64748b" font-size="10" font-weight="800">{{ $monthLabels[$month] }}</text>
                    @endforeach
                </svg>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-blue-50 p-3">
                    <p class="text-[10px] font-black uppercase tracking-wide text-blue-500">Rata-rata 3 bulan</p>
                    <p class="mt-1 text-2xl font-black text-blue-800">{{ $forecast['last_average'] }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Arah</p>
                    <p class="mt-1 text-2xl font-black text-slate-900">{{ $forecast['direction'] }}</p>
                </div>
                <div class="rounded-2xl bg-indigo-50 p-3">
                    <p class="text-[10px] font-black uppercase tracking-wide text-indigo-500">Perkiraan</p>
                    <p class="mt-1 text-2xl font-black text-indigo-800">{{ number_format($forecast['projection'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-4 space-y-2">
                @forelse($anomalies as $anomaly)
                    <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-3 rounded-2xl border border-slate-100 bg-white px-3 py-2">
                        <div class="min-w-0">
                            <p class="text-sm font-black text-slate-900">{{ $anomaly['label'] }} · {{ $anomaly['direction'] }}</p>
                            <p class="text-xs font-bold text-slate-500">Berbeda {{ $anomaly['severity'] }}% dari rata-rata</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">{{ number_format($anomaly['total'], 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="rounded-2xl bg-slate-50 p-3 text-sm font-bold text-slate-500">Tidak ada lonjakan besar pada filter ini.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Umur Job Belum RFU</h2>
            <p class="mt-1 text-xs font-bold text-slate-500">Berapa lama job belum selesai.</p>
            <div class="mt-4 space-y-3">
                @foreach($agingAnalysis['buckets'] as $label => $total)
                    @php $width = $agingMax > 0 ? max(6, round(($total / $agingMax) * 100)) : 6; @endphp
                    <div>
                        <div class="flex justify-between gap-3 text-xs font-black">
                            <span class="text-slate-700">{{ $label }}</span>
                            <span class="text-slate-500">{{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1 h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $label === '15+ hari' ? 'bg-red-500' : ($label === '8-14 hari' ? 'bg-amber-500' : 'bg-blue-500') }}" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Job yang Paling Lama Menggantung</h2>
            <div class="mt-4 grid gap-2 md:grid-cols-2">
                @forelse($agingAnalysis['stale_jobs'] as $job)
                    <a href="{{ $job['route'] }}" class="rounded-2xl border border-slate-100 bg-slate-50 p-3 hover:bg-blue-50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="wrap-break-word text-sm font-black text-slate-950">{{ $job['serial_number'] }}</p>
                                <p class="mt-1 wrap-break-word text-xs font-bold text-slate-500">{{ $job['customer'] }} · {{ $job['location'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-xs font-black text-red-700">{{ $job['age_days'] }} hari</span>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <x-status-badge :status="$job['status']" size="xs" />
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-slate-600">{{ $job['pic'] }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center md:col-span-2">
                        <p class="text-sm font-black text-slate-800">Tidak ada job lama yang menggantung.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Beban Kerja PIC</h2>
            <p class="mt-1 text-xs font-bold text-slate-500">Siapa yang bebannya tinggi dan banyak masalah.</p>
            <div class="mt-4 space-y-3">
                @forelse($picCapacity as $pic)
                    @php $width = $picMax > 0 ? max(8, round(($pic['total'] / $picMax) * 100)) : 8; @endphp
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="wrap-break-word text-sm font-black text-slate-950">{{ $pic['name'] }}</p>
                                <p class="mt-1 text-xs font-bold text-slate-500">Masalah {{ $pic['risk_rate'] }}%</p>
                            </div>
                            <span class="shrink-0 rounded-full border px-2.5 py-1 text-xs font-black {{ $loadClass($pic['load_label']) }}">{{ $pic['load_label'] }}</span>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full bg-blue-600" style="width: {{ $width }}%"></div>
                        </div>
                        <div class="mt-3 grid grid-cols-4 gap-2 text-center text-[10px] font-black">
                            <span class="rounded-xl bg-white px-2 py-1 text-slate-700">{{ $pic['total'] }} total</span>
                            <span class="rounded-xl bg-emerald-50 px-2 py-1 text-emerald-700">{{ $pic['rfu_total'] }} RFU</span>
                            <span class="rounded-xl bg-red-50 px-2 py-1 text-red-700">{{ $pic['breakdown_total'] }} BD</span>
                            <span class="rounded-xl bg-amber-50 px-2 py-1 text-amber-700">{{ $pic['waiting_part_total'] }} WP</span>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-500">Belum ada data PIC.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Customer yang Perlu Diawasi</h2>
            <p class="mt-1 text-xs font-bold text-slate-500">Customer dengan job banyak dan status masalah.</p>
            <div class="mt-4 space-y-3">
                @forelse($customerRiskMatrix as $row)
                    @php $width = $customerRiskMax > 0 ? max(8, round(($row['risk_score'] / $customerRiskMax) * 100)) : 8; @endphp
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="wrap-break-word text-sm font-black text-slate-950">{{ $row['customer'] }}</p>
                                <p class="mt-1 wrap-break-word text-xs font-bold text-slate-500">{{ $row['unit_total'] }} unit · {{ $row['total'] }} job</p>
                            </div>
                            <span class="shrink-0 rounded-full border px-2.5 py-1 text-xs font-black {{ $priorityClass($row['priority']) }}">{{ $row['priority'] }}</span>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full bg-red-500" style="width: {{ $width }}%"></div>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-500">Belum ada customer yang perlu diawasi.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">PM Belum Selesai per Customer</h2>
            <p class="mt-1 text-xs font-bold text-slate-500">Customer mana yang PM-nya paling banyak belum dikerjakan.</p>
            <div class="mt-4 space-y-3">
                @forelse($pmGapByCustomer as $row)
                    @php $width = $pmGapMax > 0 ? max(8, round(($row['pending'] / $pmGapMax) * 100)) : 8; @endphp
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="wrap-break-word text-sm font-black text-slate-950">{{ $row['customer'] }}</p>
                                <p class="mt-1 text-xs font-bold text-slate-500">{{ $row['done'] }}/{{ $row['eligible'] }} sudah PM · {{ $row['rate'] }}%</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-black text-rose-700">{{ $row['pending'] }} pending</span>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full bg-rose-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-500">Tidak ada PM yang tertinggal per customer.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Progress PM Bulan Ini</h2>
            <div class="mt-5 flex items-center justify-center">
                <div class="grid h-44 w-44 place-items-center rounded-full" style="background: conic-gradient(#0891b2 {{ $pmOverview['rate'] }}%, #e2e8f0 0);">
                    <div class="grid h-28 w-28 place-items-center rounded-full bg-white text-center shadow-inner">
                        <div>
                            <p class="text-3xl font-black text-cyan-700">{{ $pmOverview['rate'] }}%</p>
                            <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">PM</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-2 text-center">
                <div class="rounded-2xl bg-cyan-50 p-3">
                    <p class="text-xl font-black text-cyan-800">{{ number_format($pmOverview['done'], 0, ',', '.') }}</p>
                    <p class="text-[10px] font-black uppercase text-cyan-600">Sudah PM</p>
                </div>
                <div class="rounded-2xl bg-rose-50 p-3">
                    <p class="text-xl font-black text-rose-800">{{ number_format($pmOverview['pending'], 0, ',', '.') }}</p>
                    <p class="text-[10px] font-black uppercase text-rose-600">Belum PM</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
