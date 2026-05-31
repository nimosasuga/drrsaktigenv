@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Histori Movement Sparepart</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Audit trail pergerakan sparepart RENTAL. Menampilkan IN, OUT, dan tanda cross allocation saat sparepart dipakai untuk unit berbeda dari alokasi awal.
                </p>
            </div>

            <a href="{{ route('rental-spareparts.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Kembali ke Stok
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Movement</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-500">IN</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($summary['in']) }}</p>
        </div>
        <div class="rounded-3xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-red-500">OUT</p>
            <p class="mt-2 text-2xl font-black text-red-700">{{ number_format($summary['out']) }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-500">Cross Allocation</p>
            <p class="mt-2 text-2xl font-black text-amber-700">{{ number_format($summary['cross']) }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('rental-spareparts.movements.index') }}" class="grid gap-3 lg:grid-cols-7">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Movement</label>
                <select name="movement_type" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">
                    <option value="">Semua</option>
                    <option value="IN" @selected($filters['movement_type'] === 'IN')>IN</option>
                    <option value="OUT" @selected($filters['movement_type'] === 'OUT')>OUT</option>
                    <option value="TRANSFER" @selected($filters['movement_type'] === 'TRANSFER')>TRANSFER</option>
                    <option value="ADJUSTMENT" @selected($filters['movement_type'] === 'ADJUSTMENT')>ADJUSTMENT</option>
                    <option value="REALLOCATION" @selected($filters['movement_type'] === 'REALLOCATION')>REALLOCATION</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Part Number</label>
                <input type="text" name="part_number" value="{{ $filters['part_number'] }}" placeholder="Contoh: ABC-001"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">SN Unit</label>
                <input type="text" name="sn_unit" value="{{ $filters['sn_unit'] }}" placeholder="Serial unit"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">No Job</label>
                <input type="text" name="no_job" value="{{ $filters['no_job'] }}" placeholder="No job"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Sampai</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Cross</label>
                <select name="cross_allocation" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">
                    <option value="">Semua</option>
                    <option value="yes" @selected($filters['cross_allocation'] === 'yes')>Cross Only</option>
                    <option value="no" @selected($filters['cross_allocation'] === 'no')>Normal Only</option>
                </select>
            </div>

            <div class="flex gap-2 lg:col-span-7 lg:justify-end">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800 sm:flex-none">
                    Filter
                </button>
                <a href="{{ route('rental-spareparts.movements.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($movements as $movement)
            @php
                $typeClass = 'bg-slate-50 text-slate-700 border-slate-200';
                if ($movement->movement_type === 'IN') {
                    $typeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                }
                if ($movement->movement_type === 'OUT') {
                    $typeClass = 'bg-red-50 text-red-700 border-red-200';
                }
                if ($movement->is_cross_allocation) {
                    $crossClass = 'bg-amber-50 text-amber-700 border-amber-200';
                } else {
                    $crossClass = 'bg-slate-50 text-slate-500 border-slate-200';
                }
            @endphp

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $typeClass }}">{{ $movement->movement_type }}</span>
                            <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $crossClass }}">
                                {{ $movement->is_cross_allocation ? 'CROSS ALLOCATION' : 'NORMAL' }}
                            </span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                {{ optional($movement->movement_date)->format('d M Y') ?: '-' }}
                            </span>
                        </div>

                        <h2 class="mt-3 text-lg font-black text-slate-950">{{ $movement->part_number_snapshot ?: '-' }}</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $movement->part_name_snapshot ?: '-' }}</p>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">No Job / PIC</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $movement->no_job ?: '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $movement->pic_name ?: '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Alokasi Awal</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $movement->allocation_customer ?: $movement->source_customer ?: '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $movement->allocation_location ?: $movement->source_location ?: '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $movement->allocation_type_unit ?: $movement->source_type_unit ?: '-' }} / {{ $movement->allocation_sn_unit ?: $movement->source_sn_unit ?: '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Aktual Pemakaian</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $movement->actual_customer ?: '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $movement->actual_location ?: '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $movement->actual_type_unit ?: '-' }} / {{ $movement->actual_sn_unit ?: '-' }}</p>
                            </div>
                        </div>

                        @if($movement->remarks)
                            <p class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">{{ $movement->remarks }}</p>
                        @endif
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center lg:w-40">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Qty</p>
                        <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($movement->qty) }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <p class="text-lg font-black text-slate-800">Belum ada histori movement.</p>
                <p class="mt-2 text-sm text-slate-500">Data akan muncul setelah Barang Masuk atau Barang Keluar disimpan.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $movements->links() }}
    </div>
</div>
@endsection
