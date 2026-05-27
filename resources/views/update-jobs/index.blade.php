<!-- resources/views/update-jobs/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header & Action -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Update Job</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan pantau riwayat pekerjaan mekanik.</p>
        </div>

        <!-- Tombol Menuju Form Create -->
        <a href="{{ route('update-jobs.create') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-blue-200 focus:ring-4 focus:ring-blue-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Buat Job Baru
        </a>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div
        class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- ========================================== -->
    <!-- FILTER SECTION                             -->
    <!-- ========================================== -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 mb-8">
        <form action="{{ route('update-jobs.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">

            <div class="w-full sm:w-auto flex-1">
                <label for="month_filter"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter
                    Bulan</label>
                <input type="month" name="month_filter" id="month_filter" value="{{ request('month_filter') }}"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            </div>

            <div class="w-full sm:w-auto flex-1">
                <label for="customer_filter"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter
                    Customer</label>
                <select name="customer_filter" id="customer_filter"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $cust)
                    <option value="{{ $cust }}" {{ request('customer_filter')==$cust ? 'selected' : '' }}>{{ $cust }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto flex items-center gap-2">
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
                    Terapkan
                </button>
                @if(request()->hasAny(['month_filter', 'customer_filter']))
                <a href="{{ route('update-jobs.index') }}"
                    class="w-full sm:w-auto text-center px-4 py-2.5 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors">
                    Reset
                </a>
                @endif
            </div>

        </form>
    </div>

    <!-- ========================================== -->
    <!-- DATA LIST (GROUPED BY MONTH & CUSTOMER)    -->
    <!-- ========================================== -->
    @php
    // Logika Pengelompokan Data berdasarkan Bulan & Customer
    $groupedJobs = $jobs->groupBy(function($job) {
    $month = $job->work_date ? \Carbon\Carbon::parse($job->work_date)->translatedFormat('F Y') : 'Tanpa Tanggal';
    return $month . ' - ' . $job->customer;
    });
    @endphp

    <div class="space-y-8">
        @forelse ($groupedJobs as $groupName => $groupItems)
        <!-- Group Container -->
        <div>
            <!-- Group Header -->
            <div class="flex items-center gap-3 mb-4 pl-1">
                <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                <h2 class="text-lg font-bold text-slate-800 tracking-tight">{{ $groupName }}</h2>
                <span class="text-xs font-bold bg-slate-200 text-slate-600 px-2.5 py-1 rounded-lg">{{
                    $groupItems->count() }} Data</span>
            </div>

            <!-- Grid Cards for Group -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($groupItems as $job)
                <div
                    class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col h-full">
                    <!-- Aksen Warna Kiri -->
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-400"></div>

                    <div class="flex items-start justify-between mb-4 border-b border-slate-50 pb-4 pl-3">
                        <div class="flex items-center gap-3 pr-2">
                            <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">{{ $job->unit_type }}</h3>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">S/N Unit: <span
                                        class="text-slate-700 font-bold">{{ $job->serial_number }}</span></p>
                            </div>
                        </div>
                        <!-- Status Badge -->
                        @if($job->status_unit === 'RFU')
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">RFU</span>
                        @elseif($job->status_unit === 'B/D')
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">B/D</span>
                        @else
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">{{
                            $job->status_unit ?? 'Progress' }}</span>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="flex-1 grid grid-cols-2 gap-y-3 gap-x-2 mb-4 pl-3">
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Lokasi
                            </p>
                            <p class="text-sm font-bold text-slate-700">{{ $job->location ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Tanggal
                            </p>
                            <p class="text-sm font-medium text-slate-800">{{ $job->work_date ?
                                \Carbon\Carbon::parse($job->work_date)->translatedFormat('d M Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">HM Unit
                            </p>
                            <p class="text-sm font-bold text-slate-700">{{ number_format($job->hour_meter, 0, ',', '.')
                                }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Tipe
                                Pekerjaan</p>
                            <span
                                class="inline-block bg-slate-50 text-slate-600 px-2 py-0.5 rounded border border-slate-200 text-[10px] font-bold uppercase">{{
                                $job->job_type ?? 'General' }}</span>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="pt-3 border-t border-slate-50 flex items-center justify-between pl-3 mt-auto">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-[10px]">
                                {{ substr($job->pic, 0, 1) }}
                            </div>
                            <span class="text-xs font-medium text-slate-500">{{ $job->pic }}</span>
                        </div>
                        <a href="{{ route('update-jobs.show', $job->id) }}"
                            class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">Lihat Detail
                            &rarr;</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div
            class="bg-white rounded-3xl p-10 border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Data Tidak Ditemukan</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">Riwayat Update Job kosong atau tidak ada data yang
                cocok dengan filter Anda.</p>
            @if(request()->hasAny(['month_filter', 'customer_filter']))
            <a href="{{ route('update-jobs.index') }}"
                class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition-colors">Hapus
                Filter</a>
            @else
            <a href="{{ route('update-jobs.create') }}"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">Buat
                Job Pertama</a>
            @endif
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $jobs->links() }}
    </div>
</div>
@endsection
