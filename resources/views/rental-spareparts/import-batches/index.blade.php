@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Import Batch History</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Riwayat upload CSV sparepart rental. Setiap batch terhubung ke movement IN yang dibuat saat confirm import.
                </p>
            </div>

            <a href="{{ route('rental-spareparts.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Kembali ke Stok
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Batch</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total_batch']) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-500">Imported</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($summary['imported']) }}</p>
        </div>
        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-500">Total Rows</p>
            <p class="mt-2 text-2xl font-black text-blue-700">{{ number_format($summary['total_rows']) }}</p>
        </div>
        <div class="rounded-3xl border border-purple-200 bg-purple-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-purple-500">Total Qty</p>
            <p class="mt-2 text-2xl font-black text-purple-700">{{ number_format($summary['total_qty']) }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('rental-spareparts.import-batches.index') }}" class="grid gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Batch code / importer" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-4 focus:ring-cyan-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                    <option value="">Semua</option>
                    <option value="IMPORTED" {{ $filters['status'] === 'IMPORTED' ? 'selected' : '' }}>IMPORTED</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white hover:bg-cyan-700">Filter</button>
                <a href="{{ route('rental-spareparts.import-batches.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($batches as $batch)
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700">{{ $batch->status }}</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ optional($batch->created_at)->format('d M Y H:i') }}</span>
                        </div>

                        <h2 class="mt-3 text-lg font-black text-slate-950">{{ $batch->batch_code }}</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-600">Importer: {{ $batch->imported_by_name ?: '-' }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm md:grid-cols-4 lg:grid-cols-8">
                            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs font-bold uppercase text-slate-400">Rows</p><p class="mt-1 text-xl font-black text-slate-800">{{ number_format($batch->total_rows) }}</p></div>
                            <div class="rounded-2xl bg-emerald-50 p-3"><p class="text-xs font-bold uppercase text-emerald-400">Qty</p><p class="mt-1 text-xl font-black text-emerald-700">{{ number_format($batch->total_qty) }}</p></div>
                            <div class="rounded-2xl bg-blue-50 p-3"><p class="text-xs font-bold uppercase text-blue-400">Parts</p><p class="mt-1 text-xl font-black text-blue-700">{{ number_format($batch->unique_parts) }}</p></div>
                            <div class="rounded-2xl bg-cyan-50 p-3"><p class="text-xs font-bold uppercase text-cyan-400">New Part</p><p class="mt-1 text-xl font-black text-cyan-700">{{ number_format($batch->new_parts) }}</p></div>
                            <div class="rounded-2xl bg-purple-50 p-3"><p class="text-xs font-bold uppercase text-purple-400">New Loc</p><p class="mt-1 text-xl font-black text-purple-700">{{ number_format($batch->new_locations) }}</p></div>
                            <div class="rounded-2xl bg-amber-50 p-3"><p class="text-xs font-bold uppercase text-amber-400">Merge</p><p class="mt-1 text-xl font-black text-amber-700">{{ number_format($batch->merge_stock_rows) }}</p></div>
                            <div class="rounded-2xl bg-orange-50 p-3"><p class="text-xs font-bold uppercase text-orange-400">New Stock</p><p class="mt-1 text-xl font-black text-orange-700">{{ number_format($batch->new_stock_rows) }}</p></div>
                            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs font-bold uppercase text-slate-400">Movement</p><p class="mt-1 text-xl font-black text-slate-800">{{ number_format($batch->movements_count) }}</p></div>
                        </div>
                    </div>

                    <a href="{{ route('rental-spareparts.movements.index', ['movement_type' => 'IN', 'no_job' => '']) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                        Cek Movement
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <p class="text-lg font-black text-slate-800">Belum ada batch import.</p>
                <p class="mt-2 text-sm text-slate-500">Riwayat batch akan muncul setelah confirm import CSV.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $batches->links() }}
    </div>
</div>
@endsection
