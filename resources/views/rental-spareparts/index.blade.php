@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Dashboard Stok Sparepart Rental</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Monitoring stok aktif sparepart RENTAL berdasarkan part, lokasi penyimpanan, no job, customer, dan alokasi unit.
                    Barang Masuk, Barang Keluar, Histori Movement, Review Usage, dan Audit Stok Bermasalah sudah aktif.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row lg:flex-col">
                <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-blue-500">Department</p>
                    <p class="mt-1 text-xl font-black text-blue-700">RENTAL</p>
                </div>

                <a href="{{ route('rental-spareparts.reviews.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-purple-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-purple-700">
                    Review Usage
                </a>

                <a href="{{ route('rental-spareparts.movements.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-slate-800">
                    Histori Movement
                </a>

                @if($canManageSparepart)
                    <a href="{{ route('rental-spareparts.in.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700">
                        + Barang Masuk
                    </a>
                    <a href="{{ route('rental-spareparts.out.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-red-700">
                        - Barang Keluar
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Part Unik</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total_part']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Baris Stok</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total_stock_row']) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-500">On Hand</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($summary['qty_on_hand']) }}</p>
        </div>
        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-500">Available</p>
            <p class="mt-2 text-2xl font-black text-blue-700">{{ number_format($summary['qty_available']) }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-500">Reserved</p>
            <p class="mt-2 text-2xl font-black text-amber-700">{{ number_format($summary['qty_reserved']) }}</p>
        </div>
        <div class="rounded-3xl border border-orange-200 bg-orange-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-orange-500">Menipis</p>
            <p class="mt-2 text-2xl font-black text-orange-700">{{ number_format($summary['qty_low']) }}</p>
        </div>
        <div class="rounded-3xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-red-500">Habis</p>
            <p class="mt-2 text-2xl font-black text-red-700">{{ number_format($summary['qty_empty']) }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-600">Audit Stok Bermasalah</p>
                <h2 class="mt-2 text-xl font-black text-amber-950">{{ number_format($summary['problem_total']) }} indikator perlu dicek</h2>
                <p class="mt-1 text-sm leading-6 text-amber-800">
                    Ini bukan selalu error. Ini alarm kerja: stok kosong, menipis, reserved, atau data identitas stok belum lengkap.
                </p>
            </div>
            <a href="{{ route('rental-spareparts.movements.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-amber-300 bg-white px-5 py-3 text-sm font-black text-amber-800 hover:bg-amber-100">
                Cek Histori
            </a>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
            <a href="{{ route('rental-spareparts.index', ['stock_status' => 'HABIS']) }}" class="rounded-2xl border border-red-200 bg-white p-4 transition hover:bg-red-50">
                <p class="text-[11px] font-black uppercase tracking-wide text-red-500">Stok Habis</p>
                <p class="mt-2 text-2xl font-black text-red-700">{{ number_format($summary['problem_empty']) }}</p>
            </a>
            <a href="{{ route('rental-spareparts.index', ['stock_status' => 'MENIPIS']) }}" class="rounded-2xl border border-orange-200 bg-white p-4 transition hover:bg-orange-50">
                <p class="text-[11px] font-black uppercase tracking-wide text-orange-500">Stok Menipis</p>
                <p class="mt-2 text-2xl font-black text-orange-700">{{ number_format($summary['problem_low']) }}</p>
            </a>
            <a href="{{ route('rental-spareparts.index', ['stock_status' => 'RESERVED']) }}" class="rounded-2xl border border-blue-200 bg-white p-4 transition hover:bg-blue-50">
                <p class="text-[11px] font-black uppercase tracking-wide text-blue-500">Reserved Row</p>
                <p class="mt-2 text-2xl font-black text-blue-700">{{ number_format($summary['problem_reserved_rows']) }}</p>
            </a>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-black uppercase tracking-wide text-slate-500">Tanpa SN</p>
                <p class="mt-2 text-2xl font-black text-slate-800">{{ number_format($summary['problem_missing_sn']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-black uppercase tracking-wide text-slate-500">Tanpa Customer</p>
                <p class="mt-2 text-2xl font-black text-slate-800">{{ number_format($summary['problem_missing_customer']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-black uppercase tracking-wide text-slate-500">Tanpa Lokasi</p>
                <p class="mt-2 text-2xl font-black text-slate-800">{{ number_format($summary['problem_missing_location']) }}</p>
            </div>
            <div class="rounded-2xl border border-red-300 bg-white p-4">
                <p class="text-[11px] font-black uppercase tracking-wide text-red-600">Minus Available</p>
                <p class="mt-2 text-2xl font-black text-red-800">{{ number_format($summary['problem_negative_available']) }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('rental-spareparts.index') }}" class="grid gap-3 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Cari Sparepart</label>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Part number, part name, no job, customer, SN, lokasi..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Lokasi</label>
                <select name="location_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua Lokasi</option>
                    @foreach($filterOptions['locations'] as $location)
                        <option value="{{ $location->id }}" {{ (string) $filters['location_id'] === (string) $location->id ? 'selected' : '' }}>{{ $location->location_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Customer</label>
                <select name="customer" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua Customer</option>
                    @foreach($filterOptions['customers'] as $customer)
                        <option value="{{ $customer }}" {{ $filters['customer'] === $customer ? 'selected' : '' }}>{{ $customer }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">SN Unit</label>
                <select name="sn_unit" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua SN</option>
                    @foreach($filterOptions['snUnits'] as $snUnit)
                        <option value="{{ $snUnit }}" {{ $filters['sn_unit'] === $snUnit ? 'selected' : '' }}>{{ $snUnit }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Status</label>
                <select name="stock_status" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua Status</option>
                    <option value="AMAN" {{ $filters['stock_status'] === 'AMAN' ? 'selected' : '' }}>Aman</option>
                    <option value="MENIPIS" {{ $filters['stock_status'] === 'MENIPIS' ? 'selected' : '' }}>Menipis</option>
                    <option value="RESERVED" {{ $filters['stock_status'] === 'RESERVED' ? 'selected' : '' }}>Reserved</option>
                    <option value="HABIS" {{ $filters['stock_status'] === 'HABIS' ? 'selected' : '' }}>Habis</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">No Job</label>
                <select name="no_job" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua No Job</option>
                    @foreach($filterOptions['noJobs'] as $noJob)
                        <option value="{{ $noJob }}" {{ $filters['no_job'] === $noJob ? 'selected' : '' }}>{{ $noJob }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 lg:col-span-4 lg:justify-end">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700 sm:flex-none">Filter</button>
                <a href="{{ route('rental-spareparts.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($stocks as $stock)
            @php
                $available = $stock->qty_available;
                $minStock = (int) ($stock->item->min_stock ?? 0);
                $statusLabel = 'AMAN';
                $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                if ($available <= 0) {
                    $statusLabel = 'HABIS';
                    $statusClass = 'bg-red-50 text-red-700 border-red-200';
                } elseif ($stock->qty_reserved > 0) {
                    $statusLabel = 'RESERVED';
                    $statusClass = 'bg-blue-50 text-blue-700 border-blue-200';
                } elseif ($minStock > 0 && $available <= $minStock) {
                    $statusLabel = 'MENIPIS';
                    $statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                }
            @endphp

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $statusClass }}">{{ $statusLabel }}</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $stock->department }}</span>
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">{{ $stock->location->location_name ?? 'Tanpa Lokasi' }}</span>
                        </div>

                        <h2 class="mt-3 text-lg font-black text-slate-950">{{ $stock->item->part_number ?? '-' }}</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $stock->item->part_name ?? '-' }}</p>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">No Job</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $stock->source_no_job ?: '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Customer / Type</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $stock->allocation_customer ?: $stock->source_customer ?: '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $stock->allocation_type_unit ?: $stock->source_type_unit ?: '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">SN Unit</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $stock->allocation_sn_unit ?: $stock->source_sn_unit ?: '-' }}</p>
                            </div>
                        </div>

                        @if($stock->remarks)
                            <p class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">{{ $stock->remarks }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-2 lg:w-72">
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-center">
                            <p class="text-[11px] font-bold uppercase text-emerald-500">On Hand</p>
                            <p class="mt-1 text-xl font-black text-emerald-700">{{ number_format($stock->qty_on_hand) }}</p>
                        </div>
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-center">
                            <p class="text-[11px] font-bold uppercase text-amber-500">Reserved</p>
                            <p class="mt-1 text-xl font-black text-amber-700">{{ number_format($stock->qty_reserved) }}</p>
                        </div>
                        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-3 text-center">
                            <p class="text-[11px] font-bold uppercase text-blue-500">Sisa</p>
                            <p class="mt-1 text-xl font-black text-blue-700">{{ number_format($available) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <p class="text-lg font-black text-slate-800">Belum ada stok sparepart rental.</p>
                <p class="mt-2 text-sm text-slate-500">Gunakan menu Barang Masuk untuk mulai mengisi stok.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $stocks->links() }}
    </div>
</div>
@endsection
