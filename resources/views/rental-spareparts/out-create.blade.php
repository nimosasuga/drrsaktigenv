@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Barang Keluar</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Input pemakaian sparepart RENTAL. Sistem memvalidasi stok tersedia, mengurangi qty on hand, mencatat
                    movement OUT, dan mendeteksi cross allocation.
                </p>
            </div>

            <a href="{{ route('rental-spareparts.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-sm font-bold text-red-700">Data belum bisa disimpan:</p>
        <ul class="mt-2 list-inside list-disc text-sm text-red-600">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('rental-spareparts.out.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-black text-slate-900">Pilih Stok</h2>
            <p class="mt-1 text-xs text-slate-500">Hanya stok dengan qty available lebih dari 0 yang bisa dipilih.</p>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Stok Sparepart <span class="text-red-500">*</span>
                    </label>

                    <select name="stock_id" required
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                        <option value="">Pilih stok</option>
                        @foreach($stocks as $stock)
                        @php
                        $available = max(0, (int) $stock->qty_on_hand - (int) $stock->qty_reserved);
                        $label = ($stock->item->part_number ?? '-')
                        . ' | ' . ($stock->item->part_name ?? '-')
                        . ' | Sisa: ' . $available
                        . ' | ' . ($stock->location->location_name ?? 'Tanpa Lokasi')
                        . ' | SN: ' . ($stock->allocation_sn_unit ?: $stock->source_sn_unit ?: '-');
                        @endphp
                        <option value="{{ $stock->id }}" {{ (string) old('stock_id', $selectedStock?->id) === (string)
                            $stock->id ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Qty Keluar <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="qty_keluar" value="{{ old('qty_keluar', 1) }}" min="1" required
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">No Job</label>
                    <input type="text" name="no_job" value="{{ old('no_job', $selectedStock?->source_no_job) }}"
                        placeholder="Boleh kosong"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-black text-slate-900">Pemakaian Aktual</h2>
            <p class="mt-1 text-xs text-slate-500">
                Isi unit yang benar-benar memakai sparepart. Jika SN aktual berbeda dari alokasi stok, sistem menandai
                cross allocation.
            </p>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Actual
                        Customer</label>
                    <input type="text" name="actual_customer"
                        value="{{ old('actual_customer', $selectedStock?->allocation_customer ?: $selectedStock?->source_customer) }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Actual Lokasi
                        Customer</label>
                    <input type="text" name="actual_location"
                        value="{{ old('actual_location', $selectedStock?->allocation_location ?: $selectedStock?->source_location) }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Actual Type
                        Unit</label>
                    <input type="text" name="actual_type_unit"
                        value="{{ old('actual_type_unit', $selectedStock?->allocation_type_unit ?: $selectedStock?->source_type_unit) }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Actual SN
                        Unit</label>
                    <input type="text" name="actual_sn_unit"
                        value="{{ old('actual_sn_unit', $selectedStock?->allocation_sn_unit ?: $selectedStock?->source_sn_unit) }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Remarks</label>
                    <textarea name="remarks" rows="3"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100"
                        placeholder="Catatan barang keluar / alasan cross allocation jika ada">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('rental-spareparts.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Batal
            </a>

            <button type="submit"
                class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-red-700">
                Simpan Barang Keluar
            </button>
        </div>
    </form>
</div>
@endsection
