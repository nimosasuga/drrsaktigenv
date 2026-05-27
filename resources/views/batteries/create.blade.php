<!-- resources/views/batteries/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">

    @if($errors->has('error'))
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-3 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="text-sm font-medium">{{ $errors->first('error') }}</span>
    </div>
    @endif

    <!-- Header Section -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Data Battery</h1>
                <div id="network-status" class="transition-all duration-300">
                    <span
                        class="flex items-center text-slate-500 bg-slate-100 px-2 py-1 rounded-md text-xs font-medium">Memeriksa...</span>
                </div>
            </div>
            <p class="text-sm text-slate-500 mt-1">Isi detail perawatan atau penarikan battery unit.</p>
        </div>
        <a href="{{ route('batteries.index') }}"
            class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Form Container -->
    <form id="form-battery" action="{{ route('batteries.store') }}" method="POST" class="space-y-6 pb-12">
        @csrf

        <!-- Section 1: Informasi Teknisi -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Informasi Teknisi</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">PIC / Mekanik</label>
                    <div
                        class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium">
                        {{ $user->name }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">Partner</label>
                    <select name="partner" id="partner"
                        class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                        <option value="">-- Tidak Ada / Sendiri --</option>
                        @foreach($partners as $p)
                        <option value="{{ $p->name }}" {{ old('partner')==$p->name ? 'selected' : '' }}>{{ $p->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">Branch / Cabang</label>
                    <div
                        class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium">
                        {{ $branch }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">Status Mekanik</label>
                    <div
                        class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium capitalize">
                        {{ str_replace('_', ' ', $user->role ?? $user->status_user) }}</div>
                </div>
            </div>
        </div>

        <!-- Section 2: Kendaraan & Waktu -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Kendaraan & Waktu</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-5">
                    <div>
                        <label for="date" class="block text-xs font-medium text-slate-700 mb-1">Tanggal Pekerjaan <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="in_time" class="block text-xs font-medium text-slate-700 mb-1">Jam Mulai (IN)
                                <span class="text-red-500">*</span></label>
                            <input type="time" name="in_time" id="in_time" value="{{ old('in_time') }}" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                        </div>
                        <div>
                            <label for="out_time" class="block text-xs font-medium text-slate-700 mb-1">Jam Selesai
                                (OUT) <span class="text-red-500">*</span></label>
                            <input type="time" name="out_time" id="out_time" value="{{ old('out_time') }}" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                        </div>
                    </div>
                </div>
                <div class="space-y-5">
                    <div>
                        <label for="vehicle" class="block text-xs font-medium text-slate-700 mb-1">Kendaraan (Vehicle)
                            <span class="text-red-500">*</span></label>
                        <input type="text" name="vehicle" id="vehicle" value="{{ old('vehicle') }}"
                            placeholder="Contoh: Hilux" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                    </div>
                    <div>
                        <label for="nopol" class="block text-xs font-medium text-slate-700 mb-1">Nomor Polisi (Nopol)
                            <span class="text-red-500">*</span></label>
                        <input type="text" name="nopol" id="nopol" value="{{ old('nopol') }}"
                            placeholder="Contoh: B 1234 ABC" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Info Unit Pelanggan & Battery -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Unit & Battery Info</h2>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-6">
                <!-- KOLOM KIRI: Identitas Unit (Live Search S/N) -->
                <div class="space-y-5 border-r-0 md:border-r border-slate-100 md:pr-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Unit Alat Berat
                    </h3>
                    <div class="relative">
                        <label for="serial_number" class="block text-xs font-medium text-slate-700 mb-1">Serial Number
                            Unit (S/N) <span class="text-red-500">*</span></label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}"
                            autocomplete="off" placeholder="Ketik S/N Alat Berat..." required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                        <div id="sn-dropdown"
                            class="absolute z-10 w-full bg-white border border-slate-200 shadow-xl rounded-xl mt-1 hidden max-h-60 overflow-y-auto top-full left-0 divide-y divide-slate-100">
                        </div>
                    </div>
                    <div>
                        <label for="customer" class="block text-xs font-medium text-slate-700 mb-1">Customer <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="customer" id="customer" value="{{ old('customer') }}" required readonly
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed outline-none select-none">
                    </div>
                    <div>
                        <label for="location" class="block text-xs font-medium text-slate-700 mb-1">Lokasi / Site <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}" required readonly
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed outline-none select-none">
                    </div>
                    <div>
                        <label for="unit_type" class="block text-xs font-medium text-slate-700 mb-1">Tipe Unit <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="unit_type" id="unit_type" value="{{ old('unit_type') }}" required
                            readonly
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed outline-none select-none">
                    </div>
                </div>

                <!-- KOLOM KANAN: Data Spesifik Battery -->
                <div class="space-y-5">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Baterai</h3>
                    <div>
                        <label for="sn_battery" class="block text-xs font-medium text-slate-700 mb-1">Battery Serial
                            Number (S/N) <span class="text-red-500">*</span></label>
                        <input type="text" name="sn_battery" id="sn_battery" value="{{ old('sn_battery') }}"
                            placeholder="S/N Baterai Fisik..." required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                    </div>
                    <div>
                        <label for="battery_type" class="block text-xs font-medium text-slate-700 mb-1">Battery Type /
                            Model <span class="text-red-500">*</span></label>
                        <input type="text" name="battery_type" id="battery_type" value="{{ old('battery_type') }}"
                            placeholder="Contoh: N200 / 12V 200Ah" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                    </div>
                    <div>
                        <label for="battery_year" class="block text-xs font-medium text-slate-700 mb-1">Tahun Pembuatan
                            Battery</label>
                        <input type="number" name="battery_year" id="battery_year" value="{{ old('battery_year') }}"
                            placeholder="Contoh: 2024"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Kategori & Job Type (Multi Select Chips) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Job Type & Status</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-6">
                <div class="space-y-5">
                    <div>
                        <label for="category_job" class="block text-xs font-medium text-slate-700 mb-1">Category Job
                            <span class="text-red-500">*</span></label>
                        <select name="category_job" id="category_job" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                            <option value="TARIK BATTERY" {{ old('category_job')=='TARIK BATTERY' ? 'selected' : '' }}>
                                TARIK BATTERY</option>
                            <option value="CEK BATTERY" {{ old('category_job')=='CEK BATTERY' ? 'selected' : '' }}>CEK
                                BATTERY</option>
                            <option value="KIRIM BATTERY" {{ old('category_job')=='KIRIM BATTERY' ? 'selected' : '' }}>
                                KIRIM BATTERY</option>
                        </select>
                    </div>
                    <div>
                        <label for="status_unit" class="block text-xs font-medium text-slate-700 mb-1">Status Akhir Unit
                            <span class="text-red-500">*</span></label>
                        <select name="status_unit" id="status_unit" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                            <option value="RFU" {{ old('status_unit')=='RFU' ? 'selected' : '' }}>RFU (Ready)</option>
                            <option value="BREAKDOWN" {{ old('status_unit')=='BREAKDOWN' ? 'selected' : '' }}>BREAKDOWN
                            </option>
                            <option value="MONITORING" {{ old('status_unit')=='MONITORING' ? 'selected' : '' }}>
                                MONITORING</option>
                        </select>
                    </div>
                </div>

                <!-- Chips Multi-Select untuk Job Type -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-3">Tipe Pekerjaan (Bisa pilih > 1) <span
                            class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-2" id="job-types-container">
                        @php
                        $jobTypes = ['Troubleshooting', 'Install part', 'Repair', 'Peremajaan'];
                        $oldJobTypes = old('job_types', []);
                        @endphp

                        @foreach($jobTypes as $type)
                        <label class="relative cursor-pointer">
                            <input type="checkbox" name="job_types[]" value="{{ $type }}"
                                class="peer sr-only job-type-checkbox" data-type="{{ $type }}" {{ in_array($type,
                                $oldJobTypes) ? 'checked' : '' }}>
                            <div
                                class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 transition-all hover:bg-slate-50 flex items-center justify-center select-none shadow-sm">
                                {{ $type }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <!-- Pesan Error Custom jika Job Type Kosong -->
                    <p id="job-type-error" class="hidden text-xs text-red-500 mt-2 font-semibold">Mohon pilih minimal 1
                        Tipe Pekerjaan!</p>

                    <p class="text-xs text-amber-600 mt-3 font-medium flex items-start">
                        <svg class="w-4 h-4 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Info: Memilih "Peremajaan" otomatis memunculkan template pengecekan di kolom Action.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 5: Temuan & Tindakan -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Problem & Action</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="problem_date" class="block text-xs font-medium text-slate-700 mb-1">Tanggal
                            Problem</label>
                        <input type="date" name="problem_date" id="problem_date" value="{{ old('problem_date') }}"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                    </div>
                    <div>
                        <label for="rfu_date" class="block text-xs font-medium text-slate-700 mb-1">Tanggal RFU</label>
                        <input type="date" name="rfu_date" id="rfu_date" value="{{ old('rfu_date') }}"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="problem" class="block text-xs font-medium text-slate-700 mb-1">Problem /
                            Temuan</label>
                        <textarea name="problem" id="problem" rows="8" placeholder="Jelaskan kendala battery..."
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('problem') }}</textarea>
                    </div>
                    <div>
                        <label for="action" class="block text-xs font-medium text-slate-700 mb-1">Action /
                            Tindakan</label>
                        <textarea name="action" id="action" rows="8" placeholder="Tindakan yang telah dilakukan..."
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('action') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 6: Install Parts -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Install Parts</h2>
                <button type="button" id="btn-add-inst"
                    class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors border border-emerald-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Part
                </button>
            </div>
            <div class="p-6">
                <div id="inst-container" class="space-y-4">
                    <p id="inst-empty-text" class="text-sm text-slate-400 text-center py-4">Tidak ada part yang
                        dipasang.</p>
                </div>
            </div>
        </div>

        <!-- Section 7: Rekomendasi Parts -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-amber-100 bg-amber-50/50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-amber-900 uppercase tracking-wider">Rekomendasi Parts</h2>
                <button type="button" id="btn-add-rec"
                    class="inline-flex items-center px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 text-xs font-semibold rounded-lg transition-colors border border-amber-300">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Rekomendasi
                </button>
            </div>
            <div class="p-6">
                <div id="rec-container" class="space-y-4">
                    <p id="rec-empty-text" class="text-sm text-slate-400 text-center py-4">Tidak ada rekomendasi part.
                    </p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-4">
            <button type="submit" id="btn-submit"
                class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-200 transition-all focus:ring-4 focus:ring-emerald-100">
                <span id="btn-icon">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </span>
                <span id="btn-text">Simpan Data Battery</span>
            </button>
        </div>
    </form>
</div>

<!-- ========================================== -->
<!-- TEMPLATE ROWS (Inst & Rec) -->
<!-- ========================================== -->
<template id="tmpl-inst">
    <div
        class="inst-item relative bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 pt-8 sm:pt-5 transition-all">
        <button type="button"
            class="btn-remove-inst absolute top-2 right-2 sm:top-4 sm:right-4 p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                </path>
            </svg>
        </button>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Part
                    Number</label>
                <input type="text" name="inst_part_number[]"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nama Part
                    <span class="text-red-500">*</span></label>
                <input type="text" name="inst_part_name[]" required
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="lg:col-span-1">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Qty <span
                        class="text-red-500">*</span></label>
                <input type="number" name="inst_qty[]" required value="1" min="1"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">No.
                    Job</label>
                <input type="text" name="inst_no_job[]"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">No.
                    PR</label>
                <input type="text" name="inst_no_pr[]"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="lg:col-span-12">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Keterangan /
                    Remarks</label>
                <input type="text" name="inst_remarks[]"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>
    </div>
</template>

<template id="tmpl-rec">
    <div
        class="rec-item relative bg-amber-50/30 border border-amber-200 rounded-2xl p-4 sm:p-5 pt-8 sm:pt-5 transition-all">
        <button type="button"
            class="btn-remove-rec absolute top-2 right-2 sm:top-4 sm:right-4 p-1.5 text-amber-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                </path>
            </svg>
        </button>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
            <div class="lg:col-span-3">
                <label class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Part
                    Number</label>
                <input type="text" name="rec_part_number[]"
                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Nama Part
                    <span class="text-red-500">*</span></label>
                <input type="text" name="rec_part_name[]" required
                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div class="lg:col-span-1">
                <label class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Qty <span
                        class="text-red-500">*</span></label>
                <input type="number" name="rec_qty[]" required value="1" min="1"
                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div class="lg:col-span-4">
                <label
                    class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Keterangan</label>
                <input type="text" name="rec_remarks[]"
                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
        </div>
    </div>
</template>

<!-- ========================================== -->
<!-- SCRIPT JS GABUNGAN -->
<!-- ========================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- 1. NETWORK INDICATOR ---
        const networkStatus = document.getElementById('network-status');
        function updateNetworkStatus() {
            if (navigator.onLine) {
                const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                if (conn && (conn.effectiveType === '2g' || conn.effectiveType === 'slow-2g' || conn.effectiveType === '3g')) {
                    networkStatus.innerHTML = '<span class="flex items-center text-amber-600 bg-amber-50 px-2 py-1 rounded-md text-xs font-bold border border-amber-100"><span class="w-2 h-2 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>Sinyal Lemah</span>';
                } else {
                    networkStatus.innerHTML = '<span class="flex items-center text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md text-xs font-bold border border-emerald-100"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>Online</span>';
                }
            } else {
                networkStatus.innerHTML = '<span class="flex items-center text-red-600 bg-red-50 px-2 py-1 rounded-md text-xs font-bold border border-red-100"><span class="w-2 h-2 rounded-full bg-red-500 mr-1.5"></span>Offline</span>';
            }
        }
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        updateNetworkStatus();

        // --- 2. PERINGATAN KELUAR HALAMAN ---
        let formChanged = false;
        const formBattery = document.getElementById('form-battery');

        formBattery.addEventListener('input', () => { formChanged = true; });

        const beforeUnloadHandler = function (e) {
            if (formChanged && !isSubmitting) {
                const msg = 'Anda memiliki data yang belum disimpan. Yakin ingin keluar?';
                e.preventDefault(); e.returnValue = msg; return msg;
            }
        };
        window.addEventListener('beforeunload', beforeUnloadHandler);

        // --- 3. MENCEGAH DOUBLE SUBMIT & CUSTOM VALIDATION ---
        let isSubmitting = false;
        const btnSubmit = document.getElementById('btn-submit');
        const btnIcon = document.getElementById('btn-icon');
        const btnText = document.getElementById('btn-text');

        formBattery.addEventListener('submit', function(e) {

            // VALIDASI KHUSUS UNTUK TIPE PEKERJAAN (Minimal 1 dicentang)
            const checkedJobs = document.querySelectorAll('.job-type-checkbox:checked');
            const jobTypeError = document.getElementById('job-type-error');

            if(checkedJobs.length === 0) {
                e.preventDefault();
                jobTypeError.classList.remove('hidden');
                document.getElementById('category_job').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return; // Batalkan submit
            } else {
                jobTypeError.classList.add('hidden');
            }

            // Mencegah klik ganda
            if (isSubmitting) { e.preventDefault(); return; }

            isSubmitting = true;
            btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
            btnIcon.innerHTML = `<svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
            btnText.innerText = 'Menyimpan...';

            window.removeEventListener('beforeunload', beforeUnloadHandler);
        });

        // --- 4. AUTOCOMPLETE S/N (FIELD LAIN DI-LOCK) ---
        const snInput = document.getElementById('serial_number');
        const snDropdown = document.getElementById('sn-dropdown');
        const unitTypeInput = document.getElementById('unit_type');
        const customerInput = document.getElementById('customer');
        const locationInput = document.getElementById('location');
        let timeoutId;

        snInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();
            if (query.length < 2) { snDropdown.classList.add('hidden'); return; }

            timeoutId = setTimeout(() => {
                fetch(`/batteries/search-assets?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        snDropdown.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'px-4 py-3 hover:bg-emerald-50 cursor-pointer text-sm text-slate-700 transition-colors flex flex-col gap-1';
                                div.innerHTML = `
                                    <span class="font-bold text-slate-900">${item.serial_number}</span>
                                    <span class="text-xs text-slate-500">Tipe: ${item.unit_type} &bull; Customer: ${item.customer} &bull; Loc: ${item.location}</span>
                                `;
                                div.addEventListener('click', () => {
                                    snInput.value = item.serial_number;
                                    unitTypeInput.value = item.unit_type;
                                    customerInput.value = item.customer;
                                    locationInput.value = item.location;
                                    snDropdown.classList.add('hidden');
                                });
                                snDropdown.appendChild(div);
                            });
                        } else {
                            snDropdown.innerHTML = '<div class="px-4 py-4 text-sm text-slate-500 italic text-center">Unit tidak ditemukan. Pastikan S/N terdaftar.</div>';
                        }
                        snDropdown.classList.remove('hidden');
                    });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!snInput.contains(e.target) && !snDropdown.contains(e.target)) {
                snDropdown.classList.add('hidden');
            }
        });

        // --- 5. LOGIKA PEREMAJAAN (TEMPLATE AUTO-FILL) ---
        const checkboxes = document.querySelectorAll('.job-type-checkbox');
        const actionTextarea = document.getElementById('action');

        const templatePeremajaan = `VOLTAGE TOTAL : \nPLUS TO BODY : \nMINUS TO BODY : \nCELL NOMINAL : \nCELL DAMAGED : \nPLUG / SOCKET : \nBATTERYCELL : \nCABLE CONDITION : \nINTER CELL CONNECTOR : \nCELL BOLT : \nBATTERY TRAY : `;

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // Sembunyikan error saat user mulai mencentang sesuatu
                document.getElementById('job-type-error').classList.add('hidden');

                if (this.dataset.type === 'Peremajaan') {
                    let currentVal = actionTextarea.value;
                    if (this.checked) {
                        if (!currentVal.includes('VOLTAGE TOTAL :')) {
                            actionTextarea.value = currentVal + (currentVal ? '\n\n' : '') + templatePeremajaan;
                        }
                    } else {
                        if (currentVal.includes('VOLTAGE TOTAL :')) {
                            actionTextarea.value = currentVal.replace(templatePeremajaan, '').trim();
                        }
                    }
                }
            });
        });

        // --- 6. LOGIC DYNAMIC ROWS (INSTALL & REKOMENDASI) ---
        const instContainer = document.getElementById('inst-container');
        const btnAddInst = document.getElementById('btn-add-inst');
        const tmplInst = document.getElementById('tmpl-inst');
        const instEmptyText = document.getElementById('inst-empty-text');

        btnAddInst.addEventListener('click', function() {
            const clone = tmplInst.content.cloneNode(true);
            instContainer.appendChild(clone);
            if(instEmptyText) instEmptyText.style.display = 'none';
        });

        instContainer.addEventListener('click', function(e) {
            const btnRemove = e.target.closest('.btn-remove-inst');
            if (btnRemove) {
                btnRemove.closest('.inst-item').remove();
                if (instContainer.querySelectorAll('.inst-item').length === 0 && instEmptyText) {
                    instEmptyText.style.display = 'block';
                }
            }
        });

        const recContainer = document.getElementById('rec-container');
        const btnAddRec = document.getElementById('btn-add-rec');
        const tmplRec = document.getElementById('tmpl-rec');
        const recEmptyText = document.getElementById('rec-empty-text');

        btnAddRec.addEventListener('click', function() {
            const clone = tmplRec.content.cloneNode(true);
            recContainer.appendChild(clone);
            if(recEmptyText) recEmptyText.style.display = 'none';
        });

        recContainer.addEventListener('click', function(e) {
            const btnRemove = e.target.closest('.btn-remove-rec');
            if (btnRemove) {
                btnRemove.closest('.rec-item').remove();
                if (recContainer.querySelectorAll('.rec-item').length === 0 && recEmptyText) {
                    recEmptyText.style.display = 'block';
                }
            }
        });
    });
</script>
@endsection
