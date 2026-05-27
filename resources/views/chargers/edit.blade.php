<!-- resources/views/chargers/edit.blade.php -->
@extends('layouts.app')

@section('content')
@php
$dbJobTypes = collect(explode(',', str_replace(', ', ',', $charger->job_type ?? '')))
->map(fn ($item) => trim($item))
->filter()
->values()
->toArray();

$oldJobTypes = old('job_types', $dbJobTypes);
$jobTypes = ['Troubleshooting', 'Install Part', 'Repair'];
@endphp

<div class="max-w-6xl mx-auto pb-28">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Edit Data Charger
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Perbarui data pekerjaan charger, parts terpasang, dan rekomendasi parts.
            </p>
        </div>

        <a href="{{ route('chargers.show', $charger->id) }}"
            class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Kembali ke Detail
        </a>
    </div>

    @if($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <p class="font-bold mb-2">Ada data yang perlu diperbaiki:</p>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="form-charger" action="{{ route('chargers.update', $charger->id) }}" method="POST" class="space-y-6 pb-12">
        @csrf
        @method('PUT')

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
                        {{ $charger->pic ?? $user->name }}
                    </div>
                </div>

                <div>
                    <label for="partner" class="block text-xs font-medium text-slate-500 mb-2">Partner</label>
                    <select name="partner" id="partner"
                        class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                        <option value="">-- Tidak Ada / Sendiri --</option>
                        @foreach($partners as $p)
                        <option value="{{ $p->name }}" {{ old('partner', $charger->partner) === $p->name ? 'selected' :
                            '' }}>
                            {{ $p->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">Branch / Cabang</label>
                    <div
                        class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium">
                        {{ $branch }}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">Status Mekanik</label>
                    <div
                        class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium capitalize">
                        {{ str_replace('_', ' ', $charger->status_mekanik ?? $user->status_user) }}
                    </div>
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
                        <label for="date" class="block text-xs font-medium text-slate-700 mb-1">
                            Tanggal Pekerjaan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date" id="date"
                            value="{{ old('date', $charger->date ? $charger->date->format('Y-m-d') : '') }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="in_time" class="block text-xs font-medium text-slate-700 mb-1">
                                Jam Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="in_time" id="in_time"
                                value="{{ old('in_time', $charger->in_time ? \Carbon\Carbon::parse($charger->in_time)->format('H:i') : '') }}"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                        </div>

                        <div>
                            <label for="out_time" class="block text-xs font-medium text-slate-700 mb-1">
                                Jam Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="out_time" id="out_time"
                                value="{{ old('out_time', $charger->out_time ? \Carbon\Carbon::parse($charger->out_time)->format('H:i') : '') }}"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="vehicle" class="block text-xs font-medium text-slate-700 mb-1">
                            Kendaraan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="vehicle" id="vehicle" value="{{ old('vehicle', $charger->vehicle) }}"
                            required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>

                    <div>
                        <label for="nopol" class="block text-xs font-medium text-slate-700 mb-1">
                            Nomor Polisi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nopol" id="nopol" value="{{ old('nopol', $charger->nopol) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Unit & Charger -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Unit & Charger</h2>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-5">
                    <div>
                        <label for="serial_number" class="block text-xs font-medium text-slate-700 mb-1">
                            Serial Number Unit <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="serial_number" id="serial_number"
                            value="{{ old('serial_number', $charger->serial_number) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>

                    <div>
                        <label for="customer" class="block text-xs font-medium text-slate-700 mb-1">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="customer" id="customer"
                            value="{{ old('customer', $charger->customer) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>

                    <div>
                        <label for="location" class="block text-xs font-medium text-slate-700 mb-1">
                            Lokasi / Site <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="location" id="location"
                            value="{{ old('location', $charger->location) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>

                    <div>
                        <label for="unit_type" class="block text-xs font-medium text-slate-700 mb-1">
                            Tipe Unit <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="unit_type" id="unit_type"
                            value="{{ old('unit_type', $charger->unit_type) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="sn_charger" class="block text-xs font-medium text-slate-700 mb-1">
                            Charger Serial Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="sn_charger" id="sn_charger"
                            value="{{ old('sn_charger', $charger->sn_charger) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>

                    <div>
                        <label for="charger_type" class="block text-xs font-medium text-slate-700 mb-1">
                            Charger Type / Model <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="charger_type" id="charger_type"
                            value="{{ old('charger_type', $charger->charger_type) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>

                    <div>
                        <label for="charger_year" class="block text-xs font-medium text-slate-700 mb-1">
                            Tahun Charger
                        </label>
                        <input type="number" name="charger_year" id="charger_year"
                            value="{{ old('charger_year', $charger->charger_year) }}"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
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
                        <label for="category_job" class="block text-xs font-medium text-slate-700 mb-1">
                            Category Job <span class="text-red-500">*</span>
                        </label>
                        <select name="category_job" id="category_job" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                            <option value="CEK CHARGER" {{ old('category_job', $charger->category_job) == 'CEK CHARGER'
                                ? 'selected' : '' }}>
                                CEK CHARGER
                            </option>
                            <option value="TARIK CHARGER" {{ old('category_job', $charger->category_job) == 'TARIK
                                CHARGER' ? 'selected' : '' }}>
                                TARIK CHARGER
                            </option>
                            <option value="KIRIM CHARGER" {{ old('category_job', $charger->category_job) == 'KIRIM
                                CHARGER' ? 'selected' : '' }}>
                                KIRIM CHARGER
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="status_unit" class="block text-xs font-medium text-slate-700 mb-1">
                            Status Akhir Unit <span class="text-red-500">*</span>
                        </label>
                        <select name="status_unit" id="status_unit" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                            <option value="RFU" {{ old('status_unit', $charger->status_unit) == 'RFU' ? 'selected' : ''
                                }}>
                                RFU (Ready)
                            </option>
                            <option value="BREAKDOWN" {{ old('status_unit', $charger->status_unit) == 'BREAKDOWN' ?
                                'selected' : '' }}>
                                BREAKDOWN
                            </option>
                            <option value="MONITORING" {{ old('status_unit', $charger->status_unit) == 'MONITORING' ?
                                'selected' : '' }}>
                                MONITORING
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Chips Multi-Select untuk Job Type -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-3">
                        Tipe Pekerjaan (Bisa pilih > 1) <span class="text-red-500">*</span>
                    </label>

                    <div class="flex flex-wrap gap-2" id="job-types-container">
                        @foreach($jobTypes as $type)
                        <div class="relative group">
                            <input type="checkbox" name="job_types[]" value="{{ $type }}" id="job_{{ $loop->index }}"
                                class="peer absolute opacity-0 w-full h-full cursor-pointer z-20 job-type-checkbox"
                                data-type="{{ $type }}" {{ in_array($type, $oldJobTypes) ? 'checked' : '' }}>
                            <label for="job_{{ $loop->index }}"
                                class="block px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 peer-checked:bg-amber-50 peer-checked:border-amber-500 peer-checked:text-amber-700 transition-all hover:bg-slate-50 cursor-pointer select-none shadow-sm peer-focus:ring-2 peer-focus:ring-amber-300 z-10">
                                {{ $type }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <p id="job-type-error" class="hidden text-xs text-red-500 mt-2 font-semibold">
                        Mohon pilih minimal 1 Tipe Pekerjaan!
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 5: Problem & Action -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Problem & Action</h2>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="problem_date" class="block text-xs font-medium text-slate-700 mb-1">Tanggal
                            Problem</label>
                        <input type="date" name="problem_date" id="problem_date"
                            value="{{ old('problem_date', $charger->problem_date ? $charger->problem_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>

                    <div>
                        <label for="rfu_date" class="block text-xs font-medium text-slate-700 mb-1">Tanggal RFU</label>
                        <input type="date" name="rfu_date" id="rfu_date"
                            value="{{ old('rfu_date', $charger->rfu_date ? $charger->rfu_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="problem" class="block text-xs font-medium text-slate-700 mb-1">Problem /
                            Temuan</label>
                        <textarea name="problem" id="problem" rows="4"
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">{{ old('problem', $charger->problem) }}</textarea>
                    </div>

                    <div>
                        <label for="action" class="block text-xs font-medium text-slate-700 mb-1">Action /
                            Tindakan</label>
                        <textarea name="action" id="action" rows="4"
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">{{ old('action', $charger->action) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 6: Install Parts -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Install Parts</h2>
                <button type="button" id="btn-add-inst"
                    class="inline-flex items-center px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg transition-colors border border-amber-200">
                    Tambah Part
                </button>
            </div>

            <div class="p-6">
                <div id="inst-container" class="space-y-4">
                    @forelse($charger->installParts as $part)
                    <div
                        class="part-row grid grid-cols-1 md:grid-cols-6 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <input type="text" name="inst_part_number[]" value="{{ $part->part_number }}"
                            placeholder="Part Number"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-1">
                        <input type="text" name="inst_part_name[]" value="{{ $part->part_name }}"
                            placeholder="Part Name"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                        <input type="number" name="inst_qty[]" value="{{ $part->qty ?? 1 }}" min="1" placeholder="Qty"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <input type="text" name="inst_no_job[]" value="{{ $part->no_job }}" placeholder="No Job"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <input type="text" name="inst_no_pr[]" value="{{ $part->no_pr }}" placeholder="No PR"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <textarea name="inst_remarks[]" rows="2" placeholder="Remarks"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-5">{{ $part->remarks }}</textarea>
                        <button type="button"
                            class="btn-remove-row rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                    </div>
                    @empty
                    <p id="inst-empty-text" class="text-sm text-slate-400 text-center py-4">Tidak ada part yang
                        dipasang.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Section 7: Rekomendasi Parts -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Rekomendasi Parts</h2>
                <button type="button" id="btn-add-rec"
                    class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors border border-slate-300">
                    Tambah Rekomendasi
                </button>
            </div>

            <div class="p-6">
                <div id="rec-container" class="space-y-4">
                    @forelse($charger->recommendations as $rec)
                    <div
                        class="part-row grid grid-cols-1 md:grid-cols-5 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <input type="text" name="rec_part_number[]" value="{{ $rec->part_number }}"
                            placeholder="Part Number" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <input type="text" name="rec_part_name[]" value="{{ $rec->part_name }}" placeholder="Part Name"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                        <input type="number" name="rec_qty[]" value="{{ $rec->qty ?? 1 }}" min="1" placeholder="Qty"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <button type="button"
                            class="btn-remove-row rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                        <textarea name="rec_remarks[]" rows="2" placeholder="Remarks"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-5">{{ $rec->remarks }}</textarea>
                    </div>
                    @empty
                    <p id="rec-empty-text" class="text-sm text-slate-400 text-center py-4">Tidak ada rekomendasi part.
                    </p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-4">
            <button type="submit" id="btn-submit"
                class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-amber-200 transition-all focus:ring-4 focus:ring-amber-100">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const formCharger = document.getElementById('form-charger');
    const btnSubmit = document.getElementById('btn-submit');
    const instContainer = document.getElementById('inst-container');
    const recContainer = document.getElementById('rec-container');
    const btnAddInst = document.getElementById('btn-add-inst');
    const btnAddRec = document.getElementById('btn-add-rec');

    let isSubmitting = false;

    function removeEmptyText(id) {
        const el = document.getElementById(id);
        if (el) {
            el.remove();
        }
    }

    function bindRemoveButtons() {
        document.querySelectorAll('.btn-remove-row').forEach(function (button) {
            button.onclick = function () {
                const row = button.closest('.part-row');
                if (row) {
                    row.remove();
                }
            };
        });
    }

    btnAddInst.addEventListener('click', function () {
        removeEmptyText('inst-empty-text');

        const html = `
            <div class="part-row grid grid-cols-1 md:grid-cols-6 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <input type="text" name="inst_part_number[]" placeholder="Part Number" class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-1">
                <input type="text" name="inst_part_name[]" placeholder="Part Name" class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                <input type="number" name="inst_qty[]" value="1" min="1" placeholder="Qty" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="inst_no_job[]" placeholder="No Job" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="inst_no_pr[]" placeholder="No PR" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <textarea name="inst_remarks[]" rows="2" placeholder="Remarks" class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-5"></textarea>
                <button type="button" class="btn-remove-row rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-100">Hapus</button>
            </div>
        `;

        instContainer.insertAdjacentHTML('beforeend', html);
        bindRemoveButtons();
    });

    btnAddRec.addEventListener('click', function () {
        removeEmptyText('rec-empty-text');

        const html = `
            <div class="part-row grid grid-cols-1 md:grid-cols-5 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <input type="text" name="rec_part_number[]" placeholder="Part Number" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="rec_part_name[]" placeholder="Part Name" class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                <input type="number" name="rec_qty[]" value="1" min="1" placeholder="Qty" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <button type="button" class="btn-remove-row rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-100">Hapus</button>
                <textarea name="rec_remarks[]" rows="2" placeholder="Remarks" class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-5"></textarea>
            </div>
        `;

        recContainer.insertAdjacentHTML('beforeend', html);
        bindRemoveButtons();
    });

    formCharger.addEventListener('submit', function (e) {
        const checkedJobs = document.querySelectorAll('.job-type-checkbox:checked');
        const jobTypeError = document.getElementById('job-type-error');

        if (checkedJobs.length === 0) {
            e.preventDefault();
            jobTypeError.classList.remove('hidden');
            document.getElementById('category_job').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        jobTypeError.classList.add('hidden');

        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        isSubmitting = true;
        btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
        btnSubmit.innerText = 'Menyimpan...';
    });

    bindRemoveButtons();
});
</script>
@endsection