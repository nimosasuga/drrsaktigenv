@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="grid gap-5 xl:grid-cols-12 xl:items-start">
            <div class="xl:col-span-8">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-indigo-600">Recommendation Control</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    Rekomendasi Berdasarkan Serial Number Unit
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Breakdown rekomendasi sparepart berdasarkan serial number unit. Halaman ini membantu melihat unit
                    mana
                    yang paling banyak membutuhkan supply atau tindakan lanjutan.
                </p>
            </div>

            <div class="xl:col-span-4">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('sparepart-recommendations.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">
                        ← Kembali
                    </a>

                    <a href="{{ route('sparepart-recommendations.parts') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                        List Sparepart
                    </a>

                    <a href="{{ route('rental-spareparts.index') }}"
                        class="col-span-2 inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">
                        Management Sparepart
                    </a>
                </div>
            </div>
        </div>
    </div>

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
    $exportFilteredQuery = request()->except('page');
    $exportAllQuery = [
    'department' => $filters['department'],
    'export_scope' => 'all',
    ];
    @endphp

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-black text-slate-900">Filter & Export Unit</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Export memakai format CSV delimiter titik koma agar rapi saat dibuka di Excel Indonesia.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('sparepart-recommendations.units.export', $exportFilteredQuery) }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
                    Export Filter Terpilih
                </a>

                <a href="{{ route('sparepart-recommendations.units.export', $exportAllQuery) }}"
                    onclick="return confirm('Export semua rekomendasi unit untuk department {{ $filters['department'] }}?');"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">
                    Export Semua
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('sparepart-recommendations.units') }}" class="grid gap-3 lg:grid-cols-6">
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
                <a href="{{ route('sparepart-recommendations.units') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @forelse($units as $unit)
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        @if((int) $unit->need_supply_count > 0)
                        <span
                            class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-black text-amber-700">
                            NEED SUPPLY
                        </span>
                        @elseif((int) $unit->closed_count >= (int) $unit->total_items)
                        <span
                            class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-black text-slate-600">
                            CLOSED
                        </span>
                        @elseif((int) $unit->supplied_count > 0)
                        <span
                            class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700">
                            SUPPLIED
                        </span>
                        @else
                        <span
                            class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-700">
                            ACTIVE
                        </span>
                        @endif

                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                            {{ $filters['department'] }}
                        </span>
                    </div>

                    <h2 class="mt-3 text-xl font-black tracking-tight text-slate-950">
                        SN {{ $unit->serial_number ?: '-' }}
                    </h2>

                    <p class="mt-1 text-sm font-semibold text-slate-600">
                        {{ $unit->customer ?: '-' }} / {{ $unit->location ?: '-' }} • {{ $unit->unit_type ?: '-' }}
                    </p>

                    <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">
                        Last Work Date: {{ $unit->latest_work_date ?: '-' }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:w-130">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase text-slate-400">Items</p>
                        <p class="text-lg font-black text-slate-900">{{ number_format($unit->total_items) }}</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase text-amber-500">Need</p>
                        <p class="text-lg font-black text-amber-700">{{ number_format($unit->need_supply_count) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase text-emerald-500">Supplied</p>
                        <p class="text-lg font-black text-emerald-700">{{ number_format($unit->supplied_count) }}</p>
                    </div>
                    <div class="rounded-2xl bg-purple-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase text-purple-500">Installed</p>
                        <p class="text-lg font-black text-purple-700">{{ number_format($unit->installed_count) }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid gap-2 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <p class="text-[10px] font-black uppercase text-slate-400">Qty Recommended</p>
                    <p class="mt-1 text-lg font-black text-slate-900">{{ number_format($unit->qty_recommended) }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                    <p class="text-[10px] font-black uppercase text-emerald-500">Qty Supplied</p>
                    <p class="mt-1 text-lg font-black text-emerald-700">{{ number_format($unit->qty_supplied) }}</p>
                </div>
                <div class="rounded-2xl border border-purple-100 bg-purple-50 px-4 py-3">
                    <p class="text-[10px] font-black uppercase text-purple-500">Qty Installed</p>
                    <p class="mt-1 text-lg font-black text-purple-700">{{ number_format($unit->qty_installed) }}</p>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="{{ route('sparepart-recommendations.units.show', ['serialNumber' => $unit->serial_number, 'department' => $filters['department']]) }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white hover:bg-indigo-700">
                    Lihat Detail Unit
                </a>

                <a href="{{ route('sparepart-recommendations.parts', ['serial_number' => $unit->serial_number, 'department' => $filters['department']]) }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Buka di List Sparepart
                </a>
            </div>
        </div>
        @empty
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
            <p class="text-lg font-black text-slate-800">Belum ada serial number dengan recommendation.</p>
            <p class="mt-2 text-sm text-slate-500">
                Data akan muncul setelah mekanik mengisi Recommendation Part di Update Job.
            </p>
        </div>
        @endforelse
    </div>

    <div>
        {{ $units->links() }}
    </div>
</div>
@endsection