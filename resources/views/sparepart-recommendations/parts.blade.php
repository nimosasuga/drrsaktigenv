@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-indigo-600">Recommendation Control</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    Sparepart Recommendation Control Center
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Kontrol rekomendasi sparepart dari Update Job. Jika action Mark Supplied dipakai tanpa memilih
                    existing stock,
                    sistem akan membuat Barang Masuk dan stok sparepart otomatis.
                </p>
            </div>

            <div class="w-full shrink-0 lg:w-[360px]">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('sparepart-recommendations.index') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">
                        ← Kembali
                    </a>

                    <a href="{{ route('update-jobs.index') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                        Update Job
                    </a>

                    <a href="{{ route('rental-spareparts.index') }}"
                        class="col-span-2 inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">
                        Management Sparepart
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-500">Recommended</p>
            <p class="mt-2 text-2xl font-black text-blue-700">{{ number_format($summary['recommended']) }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-500">Need Supply</p>
            <p class="mt-2 text-2xl font-black text-amber-700">{{ number_format($summary['need_supply']) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-500">Supplied</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($summary['supplied']) }}</p>
        </div>
        <div class="rounded-3xl border border-purple-200 bg-purple-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-purple-500">Installed</p>
            <p class="mt-2 text-2xl font-black text-purple-700">{{ number_format($summary['installed']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-300 bg-slate-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Closed</p>
            <p class="mt-2 text-2xl font-black text-slate-700">{{ number_format($summary['closed']) }}</p>
        </div>
    </div>

    @php
    $quickStatusItems = [
    '' => 'All',
    'RECOMMENDED' => 'Recommended',
    'NEED_SUPPLY' => 'Need Supply',
    'SUPPLIED' => 'Supplied',
    'INSTALLED' => 'Installed',
    'CLOSED' => 'Closed',
    ];

    $quickStatusBaseQuery = request()->except(['page', 'recommendation_status']);
    @endphp

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Quick Filter</p>
                <p class="mt-1 text-sm font-semibold text-slate-600">Pilih status rekomendasi yang ingin dikerjakan.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($quickStatusItems as $statusValue => $statusLabel)
                @php
                $isActive = (string) $filters['recommendation_status'] === (string) $statusValue;
                $query = $statusValue === ''
                ? $quickStatusBaseQuery
                : array_merge($quickStatusBaseQuery, ['recommendation_status' => $statusValue]);
                @endphp

                <a href="{{ route('sparepart-recommendations.index', $query) }}"
                    class="inline-flex items-center justify-center rounded-2xl border px-4 py-2 text-xs font-black transition
                                {{ $isActive ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                    {{ $statusLabel }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('sparepart-recommendations.parts') }}" class="grid gap-3 lg:grid-cols-6">
            @if(in_array(strtolower((string) (auth()->user()->status_user ?? auth()->user()->role ?? '')), ['admin',
            'super_admin'], true))
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Department</label>
                <select name="department"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <option value="RENTAL" {{ $filters['department']==='RENTAL' ? 'selected' : '' }}>RENTAL</option>
                    <option value="SERVICE" {{ $filters['department']==='SERVICE' ? 'selected' : '' }}>SERVICE</option>
                </select>
            </div>
            @endif

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] }}"
                    placeholder="SN, customer, part, lokasi, mekanik..."
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">SN Unit</label>
                <input type="text" name="serial_number" value="{{ $filters['serial_number'] }}"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Part Number</label>
                <input type="text" name="part_number" value="{{ $filters['part_number'] }}"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Rec Status</label>
                <select name="recommendation_status"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <option value="">All</option>
                    @foreach($statusOptions as $status)
                    <option value="{{ $status }}" {{ $filters['recommendation_status']===$status ? 'selected' : '' }}>{{
                        $status }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Supply Status</label>
                <select name="supply_status"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <option value="">All</option>
                    @foreach($supplyStatusOptions as $status)
                    <option value="{{ $status }}" {{ $filters['supply_status']===$status ? 'selected' : '' }}>{{ $status
                        }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Date From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Date To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div class="flex items-end gap-2 lg:col-span-3 lg:justify-end">
                <button type="submit"
                    class="inline-flex flex-1 items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white hover:bg-indigo-700 sm:flex-none">
                    Filter
                </button>
                <a href="{{ route('sparepart-recommendations.parts') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @forelse($controls as $control)
        @php
        $recClass = match($control->recommendation_status) {
        'RECOMMENDED', 'REVIEWED' => 'border-blue-200 bg-blue-50 text-blue-700',
        'APPROVED', 'SUPPLIED', 'INSTALLED' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'NEED_SUPPLY', 'PARTIAL_INSTALLED' => 'border-amber-200 bg-amber-50 text-amber-700',
        'REJECTED', 'CANCELLED' => 'border-red-200 bg-red-50 text-red-700',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
        };

        $supplyClass = match($control->supply_status) {
        'SUPPLIED' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'NEED_SUPPLY', 'PARTIAL_SUPPLIED' => 'border-amber-200 bg-amber-50 text-amber-700',
        'NOT_REQUIRED' => 'border-slate-200 bg-slate-50 text-slate-600',
        default => 'border-red-200 bg-red-50 text-red-700',
        };
        @endphp

        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $recClass }}">
                            {{ $control->recommendation_status }}
                        </span>
                        <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $supplyClass }}">
                            {{ $control->supply_status }}
                        </span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                            {{ $control->department }}
                        </span>

                        @if($control->is_cross_allocation)
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-black text-red-700">
                            CROSS ALLOCATION
                        </span>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-col gap-1 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-base font-black text-slate-950 sm:text-lg">
                                {{ $control->part_number ?: '-' }} — {{ $control->part_name ?: '-' }}
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-600">
                                SN {{ $control->serial_number ?: '-' }} • {{ $control->customer ?: '-' }} / {{
                                $control->location ?: '-' }}
                            </p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">
                                {{ $control->unit_type ?: '-' }} • Date {{ $control->work_date ?: '-' }}
                            </p>
                        </div>

                        <div class="mt-2 flex shrink-0 flex-wrap gap-2 lg:mt-0">
                            <div class="rounded-2xl bg-slate-50 px-4 py-2">
                                <p class="text-[10px] font-black uppercase text-slate-400">Recommended</p>
                                <p class="text-lg font-black text-slate-900">{{ number_format($control->qty_recommended)
                                    }}</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 px-4 py-2">
                                <p class="text-[10px] font-black uppercase text-emerald-500">Supplied</p>
                                <p class="text-lg font-black text-emerald-700">{{ number_format($control->qty_supplied)
                                    }}</p>
                            </div>
                            <div class="rounded-2xl bg-purple-50 px-4 py-2">
                                <p class="text-[10px] font-black uppercase text-purple-500">Installed</p>
                                <p class="text-lg font-black text-purple-700">{{ number_format($control->qty_installed)
                                    }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                        <div class="grid gap-2 md:grid-cols-3">
                            <div>
                                <p class="text-xs font-black uppercase text-slate-400">Recommended By</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $control->recommended_by_name ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-black uppercase text-slate-400">Reviewed By</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $control->reviewed_by_name ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-black uppercase text-slate-400">Supplied By</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $control->supplied_by_name ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <details class="mt-4 rounded-2xl border border-slate-200 bg-slate-50">
                <summary
                    class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-black text-indigo-700">
                    <span>Detail / Action</span>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-slate-500">
                        Buka / Tutup
                    </span>
                </summary>

                <div class="border-t border-slate-200 p-4">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Unit & Part Detail
                                </p>

                                <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs font-bold uppercase text-slate-400">Part Number</p>
                                        <p class="font-bold text-slate-800">{{ $control->part_number ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase text-slate-400">Part Name</p>
                                        <p class="font-bold text-slate-800">{{ $control->part_name ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase text-slate-400">Serial Number</p>
                                        <p class="font-bold text-slate-800">{{ $control->serial_number ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase text-slate-400">Unit Type</p>
                                        <p class="font-bold text-slate-800">{{ $control->unit_type ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase text-slate-400">Customer</p>
                                        <p class="font-bold text-slate-800">{{ $control->customer ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase text-slate-400">Location</p>
                                        <p class="font-bold text-slate-800">{{ $control->location ?: '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($control->sourceStock)
                            <div class="rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-800">
                                <p class="text-xs font-black uppercase tracking-wide text-indigo-600">Source Stock</p>
                                <p class="mt-1">
                                    #{{ $control->sourceStock->id }} • {{ $control->sourceStock->item?->part_number }} •
                                    {{ $control->sourceStock->location?->location_name }} • Sisa {{
                                    number_format($control->sourceStock->qty_available) }}
                                </p>
                            </div>
                            @endif

                            @if($control->remarks || $control->review_note || $control->supply_note)
                            <div class="rounded-2xl bg-white px-4 py-3 text-sm leading-6 text-slate-600 shadow-sm">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Notes</p>
                                @if($control->remarks)
                                <p class="mt-2"><span class="font-black">Mekanik:</span> {{ $control->remarks }}</p>
                                @endif
                                @if($control->review_note)
                                <p class="mt-2"><span class="font-black">Review:</span> {{ $control->review_note }}</p>
                                @endif
                                @if($control->supply_note)
                                <p class="mt-2"><span class="font-black">Supply:</span> {{ $control->supply_note }}</p>
                                @endif
                            </div>
                            @endif
                        </div>

                        @if($canManage)
                        <div>
                            <form method="POST" action="{{ route('sparepart-recommendations.status', $control) }}"
                                data-smart-action-form
                                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                @csrf
                                @method('PATCH')

                                @php
                                $currentAction = match($control->recommendation_status) {
                                'RECOMMENDED', 'REVIEWED' => 'REVIEWED',
                                'APPROVED' => 'APPROVED',
                                'NEED_SUPPLY' => 'NEED_SUPPLY',
                                'SUPPLIED' => 'SUPPLIED',
                                'REJECTED' => 'REJECTED',
                                'CLOSED', 'INSTALLED', 'PARTIAL_INSTALLED' => 'CLOSED',
                                'CANCELLED' => 'CANCELLED',
                                default => 'REVIEWED',
                                };
                                @endphp

                                <label
                                    class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Action</label>
                                <select name="action_type" data-action-type-select
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold">
                                    <option value="REVIEWED" {{ $currentAction==='REVIEWED' ? 'selected' : '' }}>Mark
                                        Reviewed</option>
                                    <option value="APPROVED" {{ $currentAction==='APPROVED' ? 'selected' : '' }}>Approve
                                    </option>
                                    <option value="NEED_SUPPLY" {{ $currentAction==='NEED_SUPPLY' ? 'selected' : '' }}>
                                        Need Supply</option>
                                    <option value="SUPPLIED" {{ $currentAction==='SUPPLIED' ? 'selected' : '' }}>Mark
                                        Supplied / Create Stock IN</option>
                                    <option value="REJECTED" {{ $currentAction==='REJECTED' ? 'selected' : '' }}>Reject
                                    </option>
                                    <option value="CLOSED" {{ $currentAction==='CLOSED' ? 'selected' : '' }}>Close
                                    </option>
                                    <option value="CANCELLED" {{ $currentAction==='CANCELLED' ? 'selected' : '' }}>
                                        Cancel</option>
                                </select>

                                <p data-action-help
                                    class="mt-2 rounded-2xl bg-slate-50 px-3 py-2 text-xs font-semibold leading-5 text-slate-500">
                                    Pilih action. Field supply hanya muncul saat Mark Supplied / Create Stock IN.
                                </p>

                                <div data-supply-fields class="mt-3 hidden space-y-3">
                                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3">
                                        <p class="text-xs font-black uppercase tracking-wide text-emerald-700">Data
                                            Supply /
                                            Barang Masuk</p>
                                        <p class="mt-1 text-[11px] leading-5 text-emerald-700">
                                            Jika Source Stock dikosongkan, Mark Supplied akan membuat stok IN baru dari
                                            rekomendasi ini.
                                        </p>

                                        <label
                                            class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Tanggal
                                            Supply</label>
                                        <input type="date" name="supply_date" value="{{ now()->toDateString() }}"
                                            class="w-full rounded-2xl border border-emerald-200 bg-white px-3 py-2 text-sm">

                                        <label
                                            class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">No
                                            Job</label>
                                        <input type="text" name="supply_no_job"
                                            placeholder="No job dari sparepart masuk"
                                            class="w-full rounded-2xl border border-emerald-200 bg-white px-3 py-2 text-sm uppercase">

                                        <label
                                            class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Qty
                                            Supplied</label>
                                        <input type="number" name="qty_supplied" min="1"
                                            value="{{ max(1, (int) $control->qty_recommended - (int) $control->qty_supplied) }}"
                                            class="w-full rounded-2xl border border-emerald-200 bg-white px-3 py-2 text-sm">

                                        <label
                                            class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Location
                                            Code</label>
                                        <input type="text" name="location_code" value="RECOMMENDATION-SUPPLY"
                                            class="w-full rounded-2xl border border-emerald-200 bg-white px-3 py-2 text-sm uppercase">

                                        <label
                                            class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Location
                                            Name</label>
                                        <input type="text" name="location_name" value="RECOMMENDATION SUPPLY"
                                            class="w-full rounded-2xl border border-emerald-200 bg-white px-3 py-2 text-sm uppercase">

                                        <div class="mt-3 grid grid-cols-3 gap-2">
                                            <div>
                                                <label
                                                    class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Cabinet</label>
                                                <input type="text" name="cabinet"
                                                    class="w-full rounded-xl border border-emerald-200 bg-white px-2 py-2 text-xs uppercase">
                                            </div>
                                            <div>
                                                <label
                                                    class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Shelf</label>
                                                <input type="text" name="shelf"
                                                    class="w-full rounded-xl border border-emerald-200 bg-white px-2 py-2 text-xs uppercase">
                                            </div>
                                            <div>
                                                <label
                                                    class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Box</label>
                                                <input type="text" name="box"
                                                    class="w-full rounded-xl border border-emerald-200 bg-white px-2 py-2 text-xs uppercase">
                                            </div>
                                        </div>
                                    </div>

                                    <label
                                        class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Source
                                        Stock Existing</label>
                                    <select name="source_stock_id"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                        <option value="">Kosongkan untuk Create Stock IN baru</option>
                                        @foreach($sourceStocks as $stock)
                                        <option value="{{ $stock->id }}">
                                            #{{ $stock->id }} | {{ $stock->item?->part_number }} | Sisa {{
                                            $stock->qty_available }} | SN {{ $stock->allocation_sn_unit ?:
                                            $stock->source_sn_unit ?: '-' }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <label
                                        class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Source
                                        Type</label>
                                    <select name="source_type"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                        <option value="MANUAL">MANUAL</option>
                                        <option value="STOCK">STOCK</option>
                                        <option value="PURCHASE">PURCHASE</option>
                                        <option value="BORROWED">BORROWED</option>
                                    </select>
                                </div>

                                <label
                                    class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Note</label>
                                <textarea name="note" rows="2"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                    placeholder="Catatan koordinator"></textarea>

                                <button type="submit"
                                    onclick="return confirm('Update recommendation control ini? Jika action Mark Supplied dan Source Stock kosong, sistem akan membuat stok IN baru.');"
                                    class="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-black text-white hover:bg-indigo-700">
                                    Save Action
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </details>
        </div>
        @empty
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
            <p class="text-lg font-black text-slate-800">Belum ada recommendation control.</p>
            <p class="mt-2 text-sm text-slate-500">Data akan muncul otomatis setelah mekanik mengisi Recommendation Part
                di Update Job.</p>
        </div>
        @endforelse
    </div>

    <div>
        {{ $controls->links() }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-smart-action-form]').forEach(function (form) {
            const actionSelect = form.querySelector('[data-action-type-select]');
            const supplyFields = form.querySelector('[data-supply-fields]');
            const actionHelp = form.querySelector('[data-action-help]');

            if (!actionSelect || !supplyFields) {
                return;
            }

            const supplyInputs = Array.from(
                supplyFields.querySelectorAll('input, select, textarea')
            );

            function syncActionFields() {
                const selectedAction = String(actionSelect.value || '').trim().toUpperCase();
                const shouldShowSupply = selectedAction === 'SUPPLIED';

                supplyFields.classList.toggle('hidden', !shouldShowSupply);

                supplyInputs.forEach(function (input) {
                    input.disabled = !shouldShowSupply;
                });

                if (!actionHelp) {
                    return;
                }

                if (shouldShowSupply) {
                    actionHelp.textContent = 'Mode Mark Supplied aktif. Isi data supply, atau pilih Source Stock Existing jika memakai stok yang sudah ada.';
                    actionHelp.className = 'mt-2 rounded-2xl bg-emerald-50 px-3 py-2 text-xs font-semibold leading-5 text-emerald-700';
                    return;
                }

                if (selectedAction === 'NEED_SUPPLY') {
                    actionHelp.textContent = 'Mode Need Supply aktif. Isi Note jika perlu menjelaskan kebutuhan supply.';
                    actionHelp.className = 'mt-2 rounded-2xl bg-amber-50 px-3 py-2 text-xs font-semibold leading-5 text-amber-700';
                    return;
                }

                if (selectedAction === 'REJECTED' || selectedAction === 'CANCELLED') {
                    actionHelp.textContent = 'Mode reject/cancel aktif. Isi Note agar alasan keputusan tercatat.';
                    actionHelp.className = 'mt-2 rounded-2xl bg-red-50 px-3 py-2 text-xs font-semibold leading-5 text-red-700';
                    return;
                }

                actionHelp.textContent = 'Field supply disembunyikan karena action ini tidak membutuhkan data barang masuk.';
                actionHelp.className = 'mt-2 rounded-2xl bg-slate-50 px-3 py-2 text-xs font-semibold leading-5 text-slate-500';
            }

            actionSelect.addEventListener('change', syncActionFields);
            syncActionFields();
        });
    });
</script>
@endsection