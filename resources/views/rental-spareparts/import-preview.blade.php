@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-cyan-200 bg-cyan-50 p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-600">Import Preview</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-cyan-950 sm:text-3xl">Preview Import Stok Sparepart</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-cyan-800">
                    Data belum masuk database. Periksa ringkasan di bawah ini. Jika sudah benar, klik Confirm Import.
                </p>
            </div>

            <a href="{{ route('rental-spareparts.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-cyan-300 bg-white px-5 py-3 text-sm font-black text-cyan-800 hover:bg-cyan-100">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Baris</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total_rows']) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-500">Total Qty</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($summary['total_qty']) }}</p>
        </div>
        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-500">Part Unik</p>
            <p class="mt-2 text-2xl font-black text-blue-700">{{ number_format($summary['unique_parts']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Part Lama</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['existing_parts']) }}</p>
        </div>
        <div class="rounded-3xl border border-cyan-200 bg-cyan-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-cyan-500">Part Baru</p>
            <p class="mt-2 text-2xl font-black text-cyan-700">{{ number_format($summary['new_parts']) }}</p>
        </div>
        <div class="rounded-3xl border border-purple-200 bg-purple-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-purple-500">Lokasi Baru</p>
            <p class="mt-2 text-2xl font-black text-purple-700">{{ number_format($summary['new_locations']) }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-500">Merge Stok</p>
            <p class="mt-2 text-2xl font-black text-amber-700">{{ number_format($summary['merge_stock_rows']) }}</p>
        </div>
        <div class="rounded-3xl border border-orange-200 bg-orange-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-orange-500">Stok Baru</p>
            <p class="mt-2 text-2xl font-black text-orange-700">{{ number_format($summary['new_stock_rows']) }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-950">Sample Data Import</h2>
                <p class="mt-1 text-sm text-slate-500">Menampilkan maksimal 20 baris pertama. Semua baris tetap akan diproses saat confirm.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <form method="POST" action="{{ route('rental-spareparts.import.confirm') }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Confirm import sekarang? Data akan masuk sebagai Barang Masuk massal dan movement IN otomatis dibuat.')" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
                        Confirm Import
                    </button>
                </form>

                <form method="POST" action="{{ route('rental-spareparts.import.cancel') }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Batalkan preview import ini? Tidak ada data yang disimpan.')" class="inline-flex w-full items-center justify-center rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white hover:bg-red-700">
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
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Part Number</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Part Name</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">SN Unit</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($previewRows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">{{ $row['tanggal'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-black text-slate-900">{{ $row['part_number'] ?? '-' }}</td>
                            <td class="min-w-[220px] px-4 py-3 text-slate-700">{{ $row['part_name'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-black text-emerald-700">{{ number_format((int) ($row['qty_masuk'] ?? 0)) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $row['location_code'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $row['allocation_customer'] ?? $row['source_customer'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $row['allocation_sn_unit'] ?? $row['source_sn_unit'] ?? '-' }}</td>
                            <td class="min-w-[180px] px-4 py-3 text-slate-500">{{ $row['remarks'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
