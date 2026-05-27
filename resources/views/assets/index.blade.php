<!-- resources/views/assets/index.blade.php -->
@extends('layouts.app')

@section('content')
@php
$user = Auth::user();

$assetManageRoles = ['super_admin', 'admin', 'koordinator', 'sect_head'];

$canManageAsset = in_array(strtolower((string) ($user->role ?? '')), $assetManageRoles, true)
|| in_array(strtolower((string) ($user->status_user ?? '')), $assetManageRoles, true);
@endphp
<div class="max-w-5xl mx-auto">

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Aset (Unit)</h1>
            <p class="text-sm text-slate-500 mt-1">Pilih lokasi untuk melihat rincian customer dan unit.</p>
        </div>
        @if($canManageAsset)
        <a href="{{ route('assets.create') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-blue-200 focus:ring-4 focus:ring-blue-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Unit Baru
        </a>
        @endif
    </div>

    {{-- Filter Unit Asset --}}
    <div class="mb-6 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('assets.index') }}" class="grid gap-3 lg:grid-cols-5">

            <div class="lg:col-span-2">
                <label for="search" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                    Cari Unit
                </label>

                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Serial number, unit type, customer, lokasi, branch..."
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label for="filter_customer"
                    class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                    Customer
                </label>

                <select name="filter_customer" id="filter_customer"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua Customer</option>

                    @foreach($customers as $item)
                    <option value="{{ $item }}" {{ request('filter_customer')===$item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter_location"
                    class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                    Lokasi
                </label>

                <select name="filter_location" id="filter_location"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua Lokasi</option>

                    @foreach($locations as $item)
                    <option value="{{ $item }}" {{ request('filter_location')===$item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter_status" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                    Status
                </label>

                <select name="filter_status" id="filter_status"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua Status</option>

                    @foreach($statuses as $item)
                    <option value="{{ $item }}" {{ request('filter_status')===$item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row lg:col-span-5 lg:justify-end">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    Filter
                </button>

                <a href="{{ route('assets.index') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
                    Reset
                </a>
            </div>

        </form>
    </div>

    <!-- Data List Accordion Grouping -->
    <div x-data="{ activeLocation: null }" class="space-y-4">
        @forelse($groupedData as $location => $customers)
        @php
        $totalUnitLokasi = array_sum($customers);
        @endphp

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-300"
            :class="activeLocation === '{{ $location }}' ? 'ring-2 ring-blue-500/20 shadow-md' : ''">
            <!-- Accordion Header (Lokasi) -->
            <button @click="activeLocation = activeLocation === '{{ $location }}' ? null : '{{ $location }}'"
                class="w-full px-5 sm:px-6 py-4 flex items-center justify-between bg-white hover:bg-slate-50 transition-colors outline-none focus:outline-none focus:bg-slate-50">
                <div class="flex items-center gap-4 text-left">
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-800">{{ $location }}</h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">{{ count($customers) }} Customer terdaftar
                            di site ini</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <span
                        class="hidden sm:inline-block px-3.5 py-1.5 bg-slate-900 text-white text-xs font-bold rounded-xl shadow-sm">{{
                        $totalUnitLokasi }} Total Unit</span>
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 transition-transform duration-300"
                        :class="activeLocation === '{{ $location }}' ? 'rotate-180 bg-blue-100 text-blue-600' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </div>
            </button>

            <!-- Accordion Body (List Customer) -->
            <div x-show="activeLocation === '{{ $location }}'" x-collapse>
                <div
                    class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 border-t border-slate-100 bg-slate-50/50">
                    <!-- Mobile Total Indicator (Hidden on desktop) -->
                    <div class="sm:hidden col-span-1 mb-2">
                        <span
                            class="inline-block px-3 py-1 bg-slate-900 text-white text-xs font-bold rounded-lg shadow-sm">Total:
                            {{ $totalUnitLokasi }} Unit</span>
                    </div>

                    @foreach($customers as $customer => $total)
                    <a href="{{ route('assets.index', ['location' => $location, 'customer' => $customer]) }}"
                        class="flex flex-col p-5 border border-slate-200 rounded-2xl hover:border-blue-400 hover:shadow-md transition-all group bg-white relative overflow-hidden">
                        <div
                            class="absolute right-0 top-0 w-16 h-16 bg-blue-50 rounded-bl-[100%] opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <div class="flex items-start justify-between mb-4 relative z-10">
                            <div
                                class="p-2.5 bg-slate-100 rounded-xl group-hover:bg-blue-600 group-hover:text-white text-slate-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <span
                                class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">{{
                                $total }} Unit</span>
                        </div>
                        <h3
                            class="font-bold text-slate-800 text-sm mb-1 group-hover:text-blue-700 transition-colors relative z-10">
                            {{ $customer }}</h3>
                        <p
                            class="text-xs text-blue-600 font-semibold flex items-center mt-auto pt-4 border-t border-slate-100 group-hover:border-transparent transition-colors relative z-10">
                            Buka Daftar Unit <span
                                class="group-hover:translate-x-1 transition-transform ml-1">&rarr;</span>
                        </p>
                    </a>
                    @endforeach
                </div>
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
            <h3 class="text-lg font-bold text-slate-900 mb-1">Tidak Ada Data Unit</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">Database Manajemen Aset masih kosong. Silakan
                tambahkan unit baru.</p>
            @if($canManageAsset)
            <a href="{{ route('assets.create') }}"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">Tambah
                Data Asset</a>
            @endif
        </div>
        @endforelse
    </div>
</div>
@endsection