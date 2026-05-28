<!-- resources/views/update-jobs/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Update Job</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan pantau riwayat pekerjaan mekanik berdasarkan bulan, PIC, customer, lokasi, dan unit.</p>
        </div>

        <a href="{{ route('update-jobs.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-lg shadow-blue-200">
            + Buat Job Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl shadow-sm">
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Job</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($summary['total_jobs'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Bulan</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($summary['total_months'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">PIC</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($summary['total_pics'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Customer/Lokasi</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($summary['total_customer_locations'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-3xl border border-emerald-100 shadow-sm p-5">
            <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">RFU</p>
            <p class="text-3xl font-extrabold text-emerald-700 mt-2">{{ number_format($summary['total_rfu'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-3xl border border-red-100 shadow-sm p-5">
            <p class="text-xs font-bold text-red-600 uppercase tracking-wider">Breakdown</p>
            <p class="text-3xl font-extrabold text-red-700 mt-2">{{ number_format($summary['total_breakdown'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 mb-8">
        <form action="{{ route('update-jobs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Bulan</label>
                <input type="month" name="month_filter" value="{{ request('month_filter') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tahun</label>
                <select name="year_filter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @forelse($years as $year)
                        <option value="{{ $year }}" {{ (int) request('year_filter', $selectedYear) === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @empty
                        <option value="{{ now()->year }}">{{ now()->year }}</option>
                    @endforelse
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Customer</label>
                <select name="customer_filter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust }}" {{ request('customer_filter') == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">PIC</label>
                <select name="pic_filter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    <option value="">Semua PIC</option>
                    @foreach($pics as $pic)
                        <option value="{{ $pic }}" {{ request('pic_filter') == $pic ? 'selected' : '' }}>{{ $pic }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Lokasi</label>
                <select name="location_filter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location }}" {{ request('location_filter') == $location ? 'selected' : '' }}>{{ $location }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                <select name="status_filter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status_filter') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari S/N, unit type, PIC, customer, lokasi, problem, action..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            </div>
            <div class="md:col-span-2 xl:col-span-2 flex flex-col sm:flex-row gap-2">
                <button type="submit" class="w-full px-6 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800">Terapkan Filter</button>
                @if(request()->hasAny(['month_filter', 'year_filter', 'customer_filter', 'pic_filter', 'location_filter', 'status_filter', 'search']))
                    <a href="{{ route('update-jobs.index') }}" class="w-full text-center px-4 py-2.5 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm font-semibold hover:bg-red-100">Reset</a>
                @endif
            </div>
        </form>
        <p class="mt-4 text-xs text-slate-500">Default sistem menampilkan tahun berjalan agar halaman tetap ringan saat data membesar.</p>
    </div>

    <div class="space-y-5">
        @forelse ($groupedJobs as $monthGroup)
            <details class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden" open>
                <summary class="cursor-pointer list-none p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 hover:bg-slate-50">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">{{ $monthGroup['name'] }}</h2>
                        <p class="text-sm text-slate-500 mt-1">{{ $monthGroup['total'] }} job · {{ $monthGroup['pic_total'] }} PIC · {{ $monthGroup['customer_location_total'] }} customer/lokasi</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $monthGroup['total'] }} Data</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">RFU {{ $monthGroup['rfu_total'] }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">BD {{ $monthGroup['breakdown_total'] }}</span>
                    </div>
                </summary>

                <div class="px-4 sm:px-6 pb-6 space-y-4 bg-slate-50/60">
                    @foreach($monthGroup['pics'] as $picGroup)
                        <details class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <summary class="cursor-pointer list-none p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 hover:bg-slate-50">
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900">{{ $picGroup['name'] }}</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $picGroup['total'] }} job · {{ $picGroup['customer_location_total'] }} customer/lokasi</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $picGroup['total'] }} Data</span>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">RFU {{ $picGroup['rfu_total'] }}</span>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700">BD {{ $picGroup['breakdown_total'] }}</span>
                                </div>
                            </summary>

                            <div class="p-4 pt-0 space-y-3">
                                @foreach($picGroup['customer_locations'] as $customerLocationGroup)
                                    <details class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden">
                                        <summary class="cursor-pointer list-none p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 hover:bg-slate-100/70">
                                            <div>
                                                <h4 class="text-sm font-extrabold text-slate-900">{{ $customerLocationGroup['name'] }}</h4>
                                                <p class="text-xs text-slate-500 mt-1">{{ $customerLocationGroup['unit_total'] }} unit unik · {{ $customerLocationGroup['total'] }} job</p>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-white text-slate-700 border border-slate-200">{{ $customerLocationGroup['total'] }} Data</span>
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">RFU {{ $customerLocationGroup['rfu_total'] }}</span>
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">BD {{ $customerLocationGroup['breakdown_total'] }}</span>
                                            </div>
                                        </summary>

                                        <div class="p-4 pt-0 grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                            @foreach($customerLocationGroup['jobs'] as $job)
                                                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col h-full">
                                                    <div class="flex-1">
                                                        <div class="flex items-start justify-between gap-3 mb-3">
                                                            <div>
                                                                <h5 class="text-sm font-extrabold text-slate-900 leading-tight">{{ $job->unit_type ?? '-' }}</h5>
                                                                <p class="text-xs text-slate-500 mt-1">S/N: <span class="font-bold text-slate-700">{{ $job->serial_number ?? '-' }}</span></p>
                                                            </div>
                                                            <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-50 text-slate-600 border border-slate-100">{{ $job->status_unit ?? 'Progress' }}</span>
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                                            <div><p class="text-[10px] font-semibold text-slate-400 uppercase">Tanggal</p><p class="text-xs font-bold text-slate-700">{{ $job->work_date ? \Carbon\Carbon::parse($job->work_date)->translatedFormat('d M Y') : '-' }}</p></div>
                                                            <div><p class="text-[10px] font-semibold text-slate-400 uppercase">HM</p><p class="text-xs font-bold text-slate-700">{{ number_format((float) $job->hour_meter, 0, ',', '.') }}</p></div>
                                                            <div><p class="text-[10px] font-semibold text-slate-400 uppercase">Job Type</p><p class="text-xs font-bold text-slate-700">{{ $job->job_type ?? 'General' }}</p></div>
                                                            <div><p class="text-[10px] font-semibold text-slate-400 uppercase">PIC</p><p class="text-xs font-bold text-slate-700">{{ $job->pic ?? '-' }}</p></div>
                                                        </div>
                                                        <div class="space-y-2">
                                                            <div><p class="text-[10px] font-semibold text-slate-400 uppercase">Problem</p><p class="text-xs text-slate-600 leading-relaxed">{{ \Illuminate\Support\Str::limit($job->problem ?? '-', 90) }}</p></div>
                                                            <div><p class="text-[10px] font-semibold text-slate-400 uppercase">Action</p><p class="text-xs text-slate-600 leading-relaxed">{{ \Illuminate\Support\Str::limit($job->action ?? '-', 90) }}</p></div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                                                        <span class="text-[11px] font-medium text-slate-500">#{{ $job->id }}</span>
                                                        <a href="{{ route('update-jobs.show', $job->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat Detail &rarr;</a>
                                                    </div>
                                                </div>
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
            <div class="bg-white rounded-3xl p-10 border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center">
                <h3 class="text-lg font-bold text-slate-900 mb-1">Data Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">Riwayat Update Job kosong atau tidak ada data yang cocok dengan filter Anda.</p>
                @if(request()->hasAny(['month_filter', 'year_filter', 'customer_filter', 'pic_filter', 'location_filter', 'status_filter', 'search']))
                    <a href="{{ route('update-jobs.index') }}" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold">Hapus Filter</a>
                @else
                    <a href="{{ route('update-jobs.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold">Buat Job Pertama</a>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection
