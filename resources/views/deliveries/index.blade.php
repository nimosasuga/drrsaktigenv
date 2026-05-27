<!-- resources/views/deliveries/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header & Action -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Management Delivery Unit</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau jadwal pengiriman dan penarikan unit alat berat.</p>
        </div>

        <!-- Tombol Menuju Form Create -->
        <a href="{{ route('deliveries.create') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-purple-200 focus:ring-4 focus:ring-purple-100">
            <svg class="w-5 h-5 mr-2 text-purple-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Data Delivery
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
        <form action="{{ route('deliveries.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">

            <div class="w-full sm:w-auto flex-1">
                <label for="month_filter"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter
                    Bulan</label>
                <input type="month" name="month_filter" id="month_filter" value="{{ request('month_filter') }}"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
            </div>

            <div class="w-full sm:w-auto flex-1">
                <label for="customer_filter"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter
                    Customer</label>
                <select name="customer_filter" id="customer_filter"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                    <option value="">Semua Customer</option>
                    @if(isset($customers) && count($customers) > 0)
                    @foreach($customers as $cust)
                    <option value="{{ $cust }}" {{ request('customer_filter')==$cust ? 'selected' : '' }}>{{ $cust }}
                    </option>
                    @endforeach
                    @endif
                </select>
            </div>

            <div class="w-full sm:w-auto flex items-center gap-2">
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
                    Terapkan
                </button>
                @if(request()->hasAny(['month_filter', 'customer_filter']))
                <a href="{{ route('deliveries.index') }}"
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
    $groupedDeliveries = isset($deliveries) && $deliveries->count() > 0
    ? $deliveries->groupBy(function($del) {
    $month = $del->date ? \Carbon\Carbon::parse($del->date)->translatedFormat('F Y') : ($del->work_date ?
    \Carbon\Carbon::parse($del->work_date)->translatedFormat('F Y') : 'Tanpa Tanggal');
    return $month . ' - ' . ($del->customer ?? 'Unknown Customer');
    })
    : collect([]);
    @endphp

    <div class="space-y-8">
        @forelse ($groupedDeliveries as $groupName => $groupItems)
        <!-- Group Container -->
        <div>
            <!-- Group Header -->
            <div class="flex items-center gap-3 mb-4 pl-1">
                <span class="w-2 h-6 bg-purple-500 rounded-full"></span>
                <h2 class="text-lg font-bold text-slate-800 tracking-tight">{{ $groupName }}</h2>
                <span class="text-xs font-bold bg-slate-200 text-slate-600 px-2.5 py-1 rounded-lg">{{
                    $groupItems->count() }} Data</span>
            </div>

            <!-- Grid Cards for Group -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($groupItems as $del)
                <div
                    class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col h-full">
                    <!-- Aksen Warna Kiri -->
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-purple-400"></div>

                    <div class="flex items-start justify-between mb-4 border-b border-slate-50 pb-4 pl-3">
                        <div class="flex items-center gap-3 pr-2">
                            <div class="h-10 w-10 rounded-full bg-purple-50 flex items-center justify-center shrink-0">
                                <!-- Ikon Truk Pengiriman -->
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">{{ $del->category_job ??
                                    $del->job_type ?? 'Delivery Job' }}</h3>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">S/N Unit: <span
                                        class="text-slate-700 font-bold">{{ $del->serial_number }}</span></p>
                            </div>
                        </div>
                        <!-- Status Badge -->
                        @if(isset($del->status_unit))
                        @if($del->status_unit === 'RFU' || $del->status_unit === 'DELIVERED')
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">{{
                            $del->status_unit }}</span>
                        @elseif($del->status_unit === 'BREAKDOWN' || $del->status_unit === 'PENDING')
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">{{
                            $del->status_unit }}</span>
                        @else
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">{{
                            $del->status_unit }}</span>
                        @endif
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="flex-1 grid grid-cols-2 gap-y-3 gap-x-2 mb-4 pl-3">
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Lokasi /
                                Site</p>
                            <p class="text-sm font-bold text-slate-700">{{ $del->location ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Tanggal
                            </p>
                            <p class="text-sm font-medium text-slate-800">
                                @php
                                $delDate = $del->date ?? $del->work_date;
                                @endphp
                                {{ $delDate ? \Carbon\Carbon::parse($delDate)->translatedFormat('d M Y') : '-' }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Tipe
                                Unit</p>
                            <p class="text-sm font-bold text-slate-700">{{ $del->unit_type ?? '-' }}</p>
                        </div>
                        @if(isset($del->job_type) && !empty($del->job_type))
                        <div class="col-span-2">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe
                                Pekerjaan</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(explode(',', $del->job_type) as $jtype)
                                @if(trim($jtype) != '')
                                <span
                                    class="bg-slate-50 text-slate-600 px-2 py-0.5 rounded border border-slate-200 text-[10px] font-bold uppercase">{{
                                    trim($jtype) }}</span>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Card Footer -->
                    <div class="pt-3 border-t border-slate-50 flex items-center justify-between pl-3 mt-auto">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-[10px]">
                                {{ substr($del->pic ?? $del->user->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="text-xs font-medium text-slate-500">{{ $del->pic ?? $del->user->name ??
                                'Unknown' }}</span>
                        </div>
                        <a href="{{ route('deliveries.show', $del->id) }}"
                            class="text-xs font-bold text-purple-600 hover:text-purple-700 transition-colors">Lihat
                            Detail &rarr;</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div
            class="bg-white rounded-3xl p-10 border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-purple-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 14h8m-8 4h8M5 8h14M3 8l1.5-4h15L21 8M3 8v10a2 2 0 002 2h14a2 2 0 002-2V8"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Data Tidak Ditemukan</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">Riwayat Delivery Unit kosong atau tidak ada data
                yang cocok dengan filter Anda.</p>
            @if(request()->hasAny(['month_filter', 'customer_filter']))
            <a href="{{ route('deliveries.index') }}"
                class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition-colors">Hapus
                Filter</a>
            @else
            <a href="{{ route('deliveries.create') }}"
                class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold transition-colors">Tambah
                Data Delivery</a>
            @endif
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(isset($deliveries) && $deliveries->count() > 0)
    <div class="mt-8">
        {{ $deliveries->links() }}
    </div>
    @endif
</div>
@endsection