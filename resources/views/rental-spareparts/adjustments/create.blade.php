@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Import Correction / Adjustment Stock</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Gunakan fitur ini untuk koreksi stok massal berdasarkan stock_id. Data akan masuk preview dulu sebelum dieksekusi.
                </p>
            </div>

            <a href="{{ route('rental-spareparts.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Kembali ke Stok
            </a>
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

    <div class="rounded-3xl border border-orange-200 bg-orange-50 p-5 shadow-sm">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">Format CSV</p>
        <h2 class="mt-2 text-xl font-black text-orange-950">Adjustment berbasis stock_id</h2>
        <p class="mt-2 text-sm leading-6 text-orange-800">
            Ambil stock_id dari Export Stok CSV. Kolom expected_qty_on_hand bersifat opsional, tetapi sangat disarankan agar sistem menolak file lama saat stok sudah berubah.
        </p>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">SET</p>
                <p class="mt-1 text-sm text-slate-600">Mengubah qty_on_hand menjadi angka final.</p>
            </div>
            <div class="rounded-2xl bg-white p-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">ADD</p>
                <p class="mt-1 text-sm text-slate-600">Menambah qty_on_hand dari stok saat ini.</p>
            </div>
            <div class="rounded-2xl bg-white p-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">SUBTRACT</p>
                <p class="mt-1 text-sm text-slate-600">Mengurangi qty_on_hand, tidak boleh lebih kecil dari reserved.</p>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-950">Upload CSV Correction</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">
                    Header wajib: tanggal, stock_id, adjustment_type, qty. Header tambahan: expected_qty_on_hand, remarks.
                </p>
            </div>

            <a href="{{ route('rental-spareparts.adjustments.template') }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white hover:bg-orange-700">
                Download Template
            </a>
        </div>

        <form method="POST" action="{{ route('rental-spareparts.adjustments.preview') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-3">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">File CSV</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 file:mr-4 file:rounded-xl file:border-0 file:bg-orange-600 file:px-4 file:py-2 file:text-sm file:font-black file:text-white focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100">
            </div>
            <div class="flex items-end">
                <button type="submit" onclick="return confirm('Upload CSV correction ke halaman preview? Data belum disimpan sebelum Confirm Adjustment.')" class="inline-flex w-full items-center justify-center rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white hover:bg-orange-700">
                    Preview Adjustment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
