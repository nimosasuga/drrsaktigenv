<!-- PATH FILE: resources/views/assets/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Aset Baru</h1>
        <p class="mt-1 text-sm text-slate-500">Masukkan data unit asset sesuai struktur kolom database.</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="{{ route('assets.index') }}"
            class="inline-flex items-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden ring-1 ring-slate-900/5">
    <form action="{{ route('assets.store') }}" method="POST">
        @csrf
        <div class="p-6 sm:p-8 space-y-8">

            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md mb-6">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terdapat {{ $errors->count() }} kesalahan pada isian Anda:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <div>
                <h3 class="text-lg font-semibold leading-6 text-slate-900 mb-4 border-b border-slate-200 pb-2">Kolom Sistem</h3>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">ID</label>
                        <input type="text" value="Otomatis" readonly
                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-slate-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">QR Token</label>
                        <input type="text" value="Otomatis" readonly
                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-slate-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Created At</label>
                        <input type="text" value="Otomatis" readonly
                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-slate-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Updated At</label>
                        <input type="text" value="Otomatis" readonly
                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-slate-500 sm:text-sm">
                    </div>
                </div>
                <p class="mt-3 text-xs font-bold text-slate-500">Kolom sistem ditampilkan agar struktur form lengkap, tetapi nilainya dibuat otomatis oleh aplikasi.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold leading-6 text-slate-900 mb-4 border-b border-slate-200 pb-2">Data Unit Asset</h3>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">

                    <div>
                        <label for="supported_by" class="block text-sm font-medium text-slate-700">Supported By</label>
                        <input type="text" name="supported_by" id="supported_by" value="{{ old('supported_by') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div>
                        <label for="customer" class="block text-sm font-medium text-slate-700">Customer</label>
                        <input type="text" name="customer" id="customer" value="{{ old('customer') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-slate-700">Location</label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div>
                        <label for="branch" class="block text-sm font-medium text-slate-700">Branch</label>
                        <input type="text" name="branch" id="branch" value="{{ old('branch') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-slate-700">Serial Number <span class="text-red-500">*</span></label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div>
                        <label for="unit_type" class="block text-sm font-medium text-slate-700">Unit Type</label>
                        <input type="text" name="unit_type" id="unit_type" value="{{ old('unit_type') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-medium text-slate-700">Year</label>
                        <input type="text" name="year" id="year" value="{{ old('year') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors bg-white">
                            <option value="">Pilih Status</option>
                            <option value="ACTIVE" {{ old('status') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                            <option value="INACTIVE" {{ old('status') === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                            <option value="RENTAL" {{ old('status') === 'RENTAL' ? 'selected' : '' }}>RENTAL</option>
                            <option value="BACKUP" {{ old('status') === 'BACKUP' ? 'selected' : '' }}>BACKUP</option>
                            <option value="DITARIK" {{ old('status') === 'DITARIK' ? 'selected' : '' }}>DITARIK</option>
                        </select>
                    </div>

                    <div>
                        <label for="delivery" class="block text-sm font-medium text-slate-700">Delivery</label>
                        <input type="text" name="delivery" id="delivery" value="{{ old('delivery') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div>
                        <label for="jenis_unit" class="block text-sm font-medium text-slate-700">Jenis Unit</label>
                        <input type="text" name="jenis_unit" id="jenis_unit" value="{{ old('jenis_unit') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="note" class="block text-sm font-medium text-slate-700">Note</label>
                        <textarea name="note" id="note" rows="3"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">{{ old('note') }}</textarea>
                    </div>

                </div>
            </div>

        </div>

        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-200">
            <button type="submit"
                class="w-full inline-flex justify-center rounded-xl border border-transparent bg-blue-600 px-6 py-2.5 text-base font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                Simpan Aset Baru
            </button>
            <a href="{{ route('assets.index') }}"
                class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 bg-white px-6 py-2.5 text-base font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
