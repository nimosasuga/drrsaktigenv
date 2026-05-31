@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Barang Masuk</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Input stok masuk sparepart RENTAL. Serial Number terhubung ke Data Unit Asset RENTAL untuk mengisi customer, lokasi customer, dan type unit otomatis.
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

    <form method="POST" action="{{ route('rental-spareparts.in.store') }}" class="space-y-6" x-data="rentalSparepartInboundForm()">
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
                    <input type="text" name="default_type_unit" value="{{ old('default_type_unit') }}" x-model="defaultTypeUnit"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-black text-slate-900">Lokasi Penyimpanan</h2>
            <p class="mt-1 text-xs text-slate-500">Ini lokasi fisik sparepart disimpan, seperti lemari, rak, atau box. Berbeda dengan lokasi customer/unit.</p>

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
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Nama Lokasi Penyimpanan</label>
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
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-black text-slate-900">Informasi Unit Asset Rental</h2>
                    <p class="mt-1 text-xs text-slate-500">Isi Serial Number, lalu klik Cek Asset. Customer, lokasi customer, dan type unit akan terisi dari Data Unit Assets RENTAL.</p>
                </div>
                <span x-show="assetStatus" x-text="assetStatus"
                    class="rounded-full border px-3 py-1 text-xs font-black"
                    :class="assetFound ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'"></span>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Serial Number Unit</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="text" x-model="serialNumber" @keydown.enter.prevent="searchAsset" placeholder="Masukkan S/N unit asset rental"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <button type="button" @click="searchAsset"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-800">
                            Cek Asset
                        </button>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Customer</label>
                    <input type="text" name="allocation_customer" value="{{ old('allocation_customer') }}" x-model="allocationCustomer"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Lokasi Customer</label>
                    <input type="text" name="allocation_location" value="{{ old('allocation_location') }}" x-model="allocationLocation"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Type Unit</label>
                    <input type="text" name="allocation_type_unit" value="{{ old('allocation_type_unit') }}" x-model="allocationTypeUnit"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Alokasi SN Unit</label>
                    <input type="text" name="allocation_sn_unit" value="{{ old('allocation_sn_unit') }}" x-model="allocationSnUnit"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-black text-slate-900">Source / Asal Stok</h2>
            <p class="mt-1 text-xs text-slate-500">Opsional. Jika barang masuk memang berasal dari dokumen/no job tertentu, isi data source. Jika sama dengan alokasi unit, gunakan tombol salin.</p>

            <div class="mt-4 flex justify-end">
                <button type="button" @click="copyAllocationToSource"
                    class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black text-blue-700 hover:bg-blue-100">
                    Salin dari Informasi Unit
                </button>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Source Customer</label>
                    <input type="text" name="source_customer" value="{{ old('source_customer') }}" x-model="sourceCustomer"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Source Lokasi Customer</label>
                    <input type="text" name="source_location" value="{{ old('source_location') }}" x-model="sourceLocation"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Source Type Unit</label>
                    <input type="text" name="source_type_unit" value="{{ old('source_type_unit') }}" x-model="sourceTypeUnit"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Source SN Unit</label>
                    <input type="text" name="source_sn_unit" value="{{ old('source_sn_unit') }}" x-model="sourceSnUnit"
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

<script>
    function rentalSparepartInboundForm() {
        return {
            serialNumber: @json(old('allocation_sn_unit', '')),
            defaultTypeUnit: @json(old('default_type_unit', '')),
            allocationCustomer: @json(old('allocation_customer', '')),
            allocationLocation: @json(old('allocation_location', '')),
            allocationTypeUnit: @json(old('allocation_type_unit', '')),
            allocationSnUnit: @json(old('allocation_sn_unit', '')),
            sourceCustomer: @json(old('source_customer', '')),
            sourceLocation: @json(old('source_location', '')),
            sourceTypeUnit: @json(old('source_type_unit', '')),
            sourceSnUnit: @json(old('source_sn_unit', '')),
            assetStatus: '',
            assetFound: false,
            async searchAsset() {
                const sn = (this.serialNumber || '').trim().toUpperCase();

                if (!sn) {
                    this.assetStatus = 'ISI SERIAL NUMBER';
                    this.assetFound = false;
                    return;
                }

                this.assetStatus = 'MENCARI...';
                this.assetFound = false;

                try {
                    const url = new URL(@json(route('rental-spareparts.assets.search')), window.location.origin);
                    url.searchParams.set('serial_number', sn);

                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    if (!data.found) {
                        this.assetStatus = 'ASSET RENTAL TIDAK DITEMUKAN';
                        this.assetFound = false;
                        this.allocationSnUnit = sn;
                        return;
                    }

                    this.assetStatus = 'ASSET RENTAL DITEMUKAN';
                    this.assetFound = true;
                    this.allocationSnUnit = data.serial_number || sn;
                    this.allocationTypeUnit = data.unit_type || '';
                    this.allocationCustomer = data.customer || '';
                    this.allocationLocation = data.customer_location || '';

                    if (!this.defaultTypeUnit) {
                        this.defaultTypeUnit = data.unit_type || '';
                    }
                } catch (error) {
                    this.assetStatus = 'GAGAL CEK ASSET';
                    this.assetFound = false;
                }
            },
            copyAllocationToSource() {
                this.sourceCustomer = this.allocationCustomer;
                this.sourceLocation = this.allocationLocation;
                this.sourceTypeUnit = this.allocationTypeUnit;
                this.sourceSnUnit = this.allocationSnUnit;
            }
        }
    }
</script>
@endsection
