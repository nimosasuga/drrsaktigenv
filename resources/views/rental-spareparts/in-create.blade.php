@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Barang Masuk</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Input stok masuk sparepart RENTAL. Sistem otomatis membuat master part, lokasi, stok aktif, dan movement IN.
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

    <form method="POST" action="{{ route('rental-spareparts.in.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-black text-slate-900">Data Barang Masuk</h2>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">No Job</label>
                    <input type="text" name="no_job" value="{{ old('no_job') }}" placeholder="Boleh kosong"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Part Number <span class="text-red-500">*</span></label>
                    <input type="text" name="part_number" value="{{ old('part_number') }}" required
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Part Name <span class="text-red-500">*</span></label>
                    <input type="text" name="part_name" value="{{ old('part_name') }}" required
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Qty Masuk <span class="text-red-500">*</span></label>
                    <input type="number" name="qty_masuk" value="{{ old('qty_masuk', 1) }}" min="1" required
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Minimal Stok</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', 0) }}" min="0"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Default Type Unit</label>
                    <input type="text" name="default_type_unit" value="{{ old('default_type_unit') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-black text-slate-900">Lokasi Penyimpanan</h2>
            <p class="mt-1 text-xs text-slate-500">Jika kode lokasi belum ada, sistem otomatis membuat lokasi baru.</p>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Kode Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="location_code" value="{{ old('location_code') }}" list="location_code_options" required placeholder="Contoh: L1-B2"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <datalist id="location_code_options">
                        @foreach($locations as $location)
                        <option value="{{ $location->location_code }}">{{ $location->location_name }}</option>
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Nama Lokasi</label>
                    <input type="text" name="location_name" value="{{ old('location_name') }}" placeholder="Contoh: Lemari 1 / Box 2"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Lemari</label>
                    <input type="text" name="cabinet" value="{{ old('cabinet') }}" placeholder="Contoh: Lemari 1"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Rak / Shelf</label>
                    <input type="text" name="shelf" value="{{ old('shelf') }}" placeholder="Opsional"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Box</label>
                    <input type="text" name="box" value="{{ old('box') }}" placeholder="Contoh: Box 2"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-black text-slate-900">Alokasi Awal</h2>
            <p class="mt-1 text-xs text-slate-500">Boleh kosong. Ini dipakai nanti untuk smart matching saat mekanik install part.</p>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Source Customer</label>
                    <input type="text" name="source_customer" value="{{ old('source_customer') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Source Type Unit</label>
                    <input type="text" name="source_type_unit" value="{{ old('source_type_unit') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Source SN Unit</label>
                    <input type="text" name="source_sn_unit" value="{{ old('source_sn_unit') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Alokasi Customer</label>
                    <input type="text" name="allocation_customer" value="{{ old('allocation_customer') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Alokasi Type Unit</label>
                    <input type="text" name="allocation_type_unit" value="{{ old('allocation_type_unit') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Alokasi SN Unit</label>
                    <input type="text" name="allocation_sn_unit" value="{{ old('allocation_sn_unit') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Remarks</label>
                    <textarea name="remarks" rows="3"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100"
                        placeholder="Catatan barang masuk">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('rental-spareparts.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Batal
            </a>
            <button type="submit"
                class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700">
                Simpan Barang Masuk
            </button>
        </div>
    </form>
</div>
@endsection
