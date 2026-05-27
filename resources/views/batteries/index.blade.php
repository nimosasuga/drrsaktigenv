<!-- resources/views/batteries/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header & Action -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Battery</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola peremajaan, cek, dan penarikan battery unit.</p>
        </div>

        <!-- Tombol Menuju Form Create -->
        <a href="{{ route('batteries.create') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-emerald-200 focus:ring-4 focus:ring-emerald-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Data Battery
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
        <form action="{{ route('batteries.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">

            <div class="w-full sm:w-auto flex-1">
                <label for="month_filter"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter
                    Bulan</label>
                <input type="month" name="month_filter" id="month_filter" value="{{ request('month_filter') }}"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            </div>

            <div class="w-full sm:w-auto flex-1">
                <label for="customer_filter"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter
                    Customer</label>
                <select name="customer_filter" id="customer_filter"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
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
                <a href="{{ route('batteries.index') }}"
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
    // Logika Pengelompokan Data (Collection Grouping)
    $groupedBatteries = $batteries->groupBy(function($bat) {
    $month = $bat->date ? \Carbon\Carbon::parse($bat->date)->translatedFormat('F Y') : 'Tanpa Tanggal';
    return $month . ' - ' . $bat->customer;
    });
    @endphp

    <div class="space-y-8">
        @forelse ($groupedBatteries as $groupName => $groupItems)
        <!-- Group Container -->
        <div>
            <!-- Group Header -->
            <div class="flex items-center gap-3 mb-4 pl-1">
                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                <h2 class="text-lg font-bold text-slate-800 tracking-tight">{{ $groupName }}</h2>
                <span class="text-xs font-bold bg-slate-200 text-slate-600 px-2.5 py-1 rounded-lg">{{
                    $groupItems->count() }} Data</span>
            </div>

            <!-- Grid Cards for Group -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($groupItems as $bat)
                <div
                    class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col h-full">
                    <!-- Aksen Warna Kiri -->
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-400"></div>

                    <div class="flex items-start justify-between mb-4 border-b border-slate-50 pb-4 pl-3">
                        <div class="flex items-center gap-3 pr-2">
                            <div class="h-10 w-10 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 11h2v2h-2zm-4 0h2v2H7zm8 0h2v2h-2zM22 10v4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">{{ $bat->category_job ??
                                    'Battery Job' }}</h3>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">S/N Unit: {{ $bat->serial_number }}
                                </p>
                            </div>
                        </div>
                        <!-- Status Badge -->
                        @if($bat->status_unit === 'RFU')
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">RFU</span>
                        @elseif($bat->status_unit === 'BREAKDOWN')
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">B/D</span>
                        @else
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">{{
                            $bat->status_unit ?? 'Process' }}</span>
                        @endif
                    </div>

                    <!-- Card Body (Flex Grow to push footer to bottom) -->
                    <div class="flex-1 grid grid-cols-2 gap-y-3 gap-x-2 mb-4 pl-3">
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">S/N
                                Baterai</p>
                            <p class="text-sm font-bold text-emerald-700">{{ $bat->sn_battery ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Tanggal
                            </p>
                            <p class="text-sm font-medium text-slate-800">{{ $bat->date ?
                                \Carbon\Carbon::parse($bat->date)->translatedFormat('d M Y') : '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe
                                Pekerjaan</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(explode(',', $bat->job_type) as $jtype)
                                @if(trim($jtype) != '')
                                <span
                                    class="bg-slate-50 text-slate-600 px-2 py-0.5 rounded border border-slate-200 text-[10px] font-bold uppercase">{{
                                    trim($jtype) }}</span>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="pt-3 border-t border-slate-50 flex items-center justify-between pl-3 mt-auto">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-[10px]">
                                {{ substr($bat->pic, 0, 1) }}
                            </div>
                            <span class="text-xs font-medium text-slate-500">{{ $bat->pic }}</span>
                        </div>
                        <a href="{{ route('batteries.show', $bat->id) }}"
                            class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition-colors">Lihat
                            Detail &rarr;</a>
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
                        d="M4 7v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Data Tidak Ditemukan</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">Riwayat battery kosong atau tidak ada data yang
                cocok dengan filter Anda.</p>
            @if(request()->hasAny(['month_filter', 'customer_filter']))
            <a href="{{ route('batteries.index') }}"
                class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition-colors">Hapus
                Filter</a>
            @else
            <a href="{{ route('batteries.create') }}"
                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors">Tambah
                Data Battery</a>
            @endif
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $batteries->links() }}
    </div>
</div>
@endsection
