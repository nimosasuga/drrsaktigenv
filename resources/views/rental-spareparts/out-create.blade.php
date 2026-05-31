@extends('layouts.app')

@section('content')
@php
    $stockOptions = $stocks->map(function ($stock) {
        $available = max(0, (int) $stock->qty_on_hand - (int) $stock->qty_reserved);
        $partNumber = $stock->item->part_number ?? '-';
        $partName = $stock->item->part_name ?? '-';
        $locationName = $stock->location->location_name ?? 'Tanpa Lokasi';
        $snUnit = $stock->allocation_sn_unit ?: $stock->source_sn_unit ?: '-';
        $customer = $stock->allocation_customer ?: $stock->source_customer ?: '-';
        $typeUnit = $stock->allocation_type_unit ?: $stock->source_type_unit ?: '-';
        $noJob = $stock->source_no_job ?: '';

        return [
            'id' => $stock->id,
            'label' => $partNumber . ' | ' . $partName . ' | Sisa: ' . $available . ' | ' . $locationName . ' | SN: ' . $snUnit,
            'part_number' => $partNumber,
            'part_name' => $partName,
            'available' => $available,
            'location_name' => $locationName,
            'customer' => $customer,
            'customer_location' => $stock->allocation_location ?: $stock->source_location ?: '',
            'type_unit' => $typeUnit,
            'sn_unit' => $snUnit,
            'no_job' => $noJob,
            'search_text' => strtoupper(trim($partNumber . ' ' . $partName . ' ' . $locationName . ' ' . $snUnit . ' ' . $customer . ' ' . $typeUnit . ' ' . $noJob)),
        ];
    })->values();

    $initialStockId = (string) old('stock_id', $selectedStock?->id);
    $initialStock = $stockOptions->firstWhere('id', (int) $initialStockId);
@endphp

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

    <form method="POST" action="{{ route('rental-spareparts.out.store') }}" class="space-y-6"
        x-data="rentalSparepartOutForm()">
        @csrf

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-black text-slate-900">Pilih Stok</h2>
            <p class="mt-1 text-xs text-slate-500">Ketik part number, part name, customer, lokasi, no job, atau serial number unit.</p>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="relative md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Stok Sparepart <span class="text-red-500">*</span>
                    </label>

                    <input type="hidden" name="stock_id" x-model="stockId" required>

                    <input type="text" x-model="stockSearch" @input="openSearch = true" @focus="openSearch = true"
                        placeholder="Cari stok: part number, nama part, customer, lokasi, SN, no job..."
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">

                    <div x-show="openSearch" @click.outside="openSearch = false"
                        class="absolute z-30 mt-2 max-h-80 w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                        <template x-for="stock in filteredStocks" :key="stock.id">
                            <button type="button" @click="selectStock(stock)"
                                class="w-full rounded-xl px-3 py-3 text-left transition hover:bg-red-50">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-slate-900" x-text="stock.part_number + ' — ' + stock.part_name"></p>
                                        <p class="mt-1 text-xs text-slate-500" x-text="stock.customer + ' / ' + stock.type_unit + ' / SN: ' + stock.sn_unit"></p>
                                        <p class="mt-1 text-xs text-slate-400" x-text="stock.location_name + (stock.no_job ? ' / No Job: ' + stock.no_job : '')"></p>
                                    </div>
                                    <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-black text-red-700" x-text="'Sisa ' + stock.available"></span>
                                </div>
                            </button>
                        </template>

                        <div x-show="filteredStocks.length === 0" class="px-3 py-6 text-center text-sm font-semibold text-slate-500">
                            Stok tidak ditemukan.
                        </div>
                    </div>

                    <p class="mt-2 text-xs font-semibold text-slate-500">
                        Stok terpilih: <span class="font-black text-slate-800" x-text="selectedLabel || 'Belum dipilih'"></span>
                    </p>
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
                    <input type="text" name="no_job" x-model="noJob" placeholder="Boleh kosong"
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
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Actual Customer</label>
                    <input type="text" name="actual_customer" x-model="actualCustomer"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Actual Lokasi Customer</label>
                    <input type="text" name="actual_location" x-model="actualLocation"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Actual Type Unit</label>
                    <input type="text" name="actual_type_unit" x-model="actualTypeUnit"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Actual SN Unit</label>
                    <input type="text" name="actual_sn_unit" x-model="actualSnUnit"
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

<script>
    function rentalSparepartOutForm() {
        const stocks = @json($stockOptions);
        const initialStockId = @json($initialStockId);
        const initialStock = @json($initialStock);

        return {
            stocks,
            stockId: initialStockId || '',
            stockSearch: initialStock ? initialStock.label : '',
            selectedLabel: initialStock ? initialStock.label : '',
            openSearch: false,
            noJob: @json(old('no_job', $initialStock['no_job'] ?? '')),
            actualCustomer: @json(old('actual_customer', $initialStock['customer'] ?? '')),
            actualLocation: @json(old('actual_location', $initialStock['customer_location'] ?? '')),
            actualTypeUnit: @json(old('actual_type_unit', $initialStock['type_unit'] ?? '')),
            actualSnUnit: @json(old('actual_sn_unit', $initialStock['sn_unit'] ?? '')),
            get filteredStocks() {
                const keyword = (this.stockSearch || '').trim().toUpperCase();

                if (!keyword) {
                    return this.stocks.slice(0, 20);
                }

                return this.stocks
                    .filter((stock) => stock.search_text.includes(keyword))
                    .slice(0, 30);
            },
            selectStock(stock) {
                this.stockId = stock.id;
                this.stockSearch = stock.label;
                this.selectedLabel = stock.label;
                this.noJob = stock.no_job || '';
                this.actualCustomer = stock.customer === '-' ? '' : stock.customer;
                this.actualLocation = stock.customer_location || '';
                this.actualTypeUnit = stock.type_unit === '-' ? '' : stock.type_unit;
                this.actualSnUnit = stock.sn_unit === '-' ? '' : stock.sn_unit;
                this.openSearch = false;
            }
        };
    }
</script>
@endsection
