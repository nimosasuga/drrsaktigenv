@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Edit Stok Sparepart</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Perubahan qty akan dicatat sebagai movement ADJUSTMENT agar audit stok tetap terbaca.
                </p>
            </div>

            <a href="{{ route('rental-spareparts.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('rental-spareparts.stocks.update', $stock) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black text-slate-950">Data Part & Qty</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Part Number *</label>
                    <input name="part_number" value="{{ old('part_number', $stock->item?->part_number) }}" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Part Name *</label>
                    <input name="part_name" value="{{ old('part_name', $stock->item?->part_name) }}" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Default Type Unit</label>
                    <input name="default_type_unit" value="{{ old('default_type_unit', $stock->item?->default_type_unit) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Min Stock</label>
                    <input type="number" min="0" name="min_stock" value="{{ old('min_stock', $stock->item?->min_stock ?? 0) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Qty On Hand *</label>
                    <input type="number" min="0" name="qty_on_hand" value="{{ old('qty_on_hand', $stock->qty_on_hand) }}" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <p class="mt-1 text-xs font-semibold text-slate-500">Reserved saat ini: {{ number_format($stock->qty_reserved) }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black text-slate-950">Lokasi Penyimpanan</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Location Code *</label>
                    <input name="location_code" value="{{ old('location_code', $stock->location?->location_code) }}" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Location Name</label>
                    <input name="location_name" value="{{ old('location_name', $stock->location?->location_name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Cabinet</label>
                    <input name="cabinet" value="{{ old('cabinet', $stock->location?->cabinet) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Shelf</label>
                    <input name="shelf" value="{{ old('shelf', $stock->location?->shelf) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Box</label>
                    <input name="box" value="{{ old('box', $stock->location?->box) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black text-slate-950">Source & Allocation</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach([
                    'source_no_job' => 'Source No Job',
                    'source_customer' => 'Source Customer',
                    'source_location' => 'Source Location',
                    'source_type_unit' => 'Source Type Unit',
                    'source_sn_unit' => 'Source SN Unit',
                    'allocation_customer' => 'Allocation Customer',
                    'allocation_location' => 'Allocation Location',
                    'allocation_type_unit' => 'Allocation Type Unit',
                    'allocation_sn_unit' => 'Allocation SN Unit',
                ] as $field => $label)
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">{{ $label }}</label>
                        <input name="{{ $field }}" value="{{ old($field, $stock->{$field}) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>
                @endforeach

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Remarks</label>
                    <textarea name="remarks" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">{{ old('remarks', $stock->remarks) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('rental-spareparts.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Batal</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
