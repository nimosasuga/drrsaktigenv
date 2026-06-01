@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-orange-200 bg-orange-50 p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">Adjustment Preview</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-orange-950 sm:text-3xl">Preview Correction / Adjustment Stock</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-orange-800">
                    Data belum masuk database. Periksa ringkasan perubahan stok sebelum confirm.
                </p>
            </div>

            <a href="{{ route('rental-spareparts.adjustments.create') }}" class="inline-flex items-center justify-center rounded-2xl border border-orange-300 bg-white px-5 py-3 text-sm font-black text-orange-800 hover:bg-orange-100">
                Kembali Upload
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Baris</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total_rows']) }}</p>
        </div>
        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-500">SET</p>
            <p class="mt-2 text-2xl font-black text-blue-700">{{ number_format($summary['set']) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-500">ADD</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($summary['add']) }}</p>
        </div>
        <div class="rounded-3xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-red-500">SUBTRACT</p>
            <p class="mt-2 text-2xl font-black text-red-700">{{ number_format($summary['subtract']) }}</p>
        </div>
        <div class="rounded-3xl border border-green-200 bg-green-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-green-500">Qty Naik</p>
            <p class="mt-2 text-2xl font-black text-green-700">{{ number_format($summary['total_increase']) }}</p>
        </div>
        <div class="rounded-3xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-rose-500">Qty Turun</p>
            <p class="mt-2 text-2xl font-black text-rose-700">{{ number_format($summary['total_decrease']) }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-950">Sample Adjustment</h2>
                <p class="mt-1 text-sm text-slate-500">Menampilkan maksimal 30 baris pertama. Semua baris tetap diproses saat confirm.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <form method="POST" action="{{ route('rental-spareparts.adjustments.confirm') }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Confirm correction/adjustment sekarang? Stok akan berubah dan movement ADJUSTMENT dibuat otomatis.')" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
                        Confirm Adjustment
                    </button>
                </form>

                <form method="POST" action="{{ route('rental-spareparts.adjustments.cancel') }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Batalkan preview adjustment ini? Tidak ada data yang disimpan.')" class="inline-flex w-full items-center justify-center rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white hover:bg-red-700">
                        Cancel
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Stock ID</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Expected</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($previewRows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">{{ $row['tanggal'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-black text-slate-900">#{{ $row['stock_id'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-black text-orange-700">{{ $row['adjustment_type'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-black text-slate-800">{{ number_format((int) ($row['qty'] ?? 0)) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $row['expected_qty_on_hand'] !== '' ? number_format((int) $row['expected_qty_on_hand']) : '-' }}</td>
                            <td class="min-w-[240px] px-4 py-3 text-slate-500">{{ $row['remarks'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
