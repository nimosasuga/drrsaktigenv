<!-- resources/views/update-jobs/edit.blade.php -->
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
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Update Job</h1>
                <div id="network-status" class="transition-all duration-300">
                    <span
                        class="flex items-center text-slate-500 bg-slate-100 px-2 py-1 rounded-md text-xs font-medium">Memeriksa...</span>
                </div>
            </div>
            <p class="text-sm text-slate-500 mt-1">S/N Unit: <span class="font-bold text-slate-700">{{
                    $job->serial_number }}</span></p>
        </div>
        <a href="{{ route('update-jobs.show', $job->id) }}"
            class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Batal
        </a>
    </div>

    <!-- Form Container -->
    <!-- PERHATIKAN: Route diarahkan ke update dan menggunakan @method('PUT') -->
    <form id="form-job" action="{{ route('update-jobs.update', $job->id) }}" method="POST" class="space-y-6 pb-12">
        @csrf
        @method('PUT')

        <!-- Section 1: Informasi Mekanik & Partner -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Informasi Mekanik & Partner
                </h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">PIC / Mekanik (Pembuat Job)</label>
                    <div
                        class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium">
                        {{ $job->pic }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">Partner</label>
                    <select name="partner" id="partner"
                        class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                        <option value="">-- Tidak Ada / Sendiri --</option>
                        @foreach($partners as $p)
                        <option value="{{ $p->name }}" {{ old('partner', $job->partner) == $p->name ? 'selected' : ''
                            }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">Branch / Cabang</label>
                    <div
                        class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium">
                        {{ $job->branch }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-2">Status / Role</label>
                    <div
                        class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium capitalize">
                        {{ str_replace('_', ' ', $job->status_mekanik) }}</div>
                </div>
            </div>
        </div>

        <!-- Section 1.5: Informasi Kendaraan -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Informasi Kendaraan Mekanik
                </h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="vehicle_type" class="block text-xs font-medium text-slate-700 mb-1">Nama Mobil /
                        Sarana</label>
                    <input type="text" name="vehicle_type" id="vehicle_type"
                        value="{{ old('vehicle_type', $job->vehicle_type) }}" placeholder="Contoh: Hilux / Triton"
                        class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                </div>
                <div>
                    <label for="nopol" class="block text-xs font-medium text-slate-700 mb-1">Nomor Polisi
                        (Nopol)</label>
                    <input type="text" name="nopol" id="nopol" value="{{ old('nopol', $job->nopol) }}"
                        placeholder="Contoh: B 1234 ABC"
                        class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                </div>
            </div>
        </div>

        <!-- Section 2: Data Unit & Customer -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Data Unit & Customer</h2>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-6">

                <!-- KOLOM KIRI: Identitas Unit -->
                <div class="space-y-5">
                    <div class="relative">
                        <label for="serial_number" class="block text-xs font-medium text-slate-700 mb-1">Serial Number
                            (S/N) <span class="text-red-500">*</span></label>
                        <input type="text" name="serial_number" id="serial_number"
                            value="{{ old('serial_number', $job->serial_number) }}" autocomplete="off"
                            placeholder="Ketik S/N untuk mencari..." required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                        <div id="sn-dropdown"
                            class="absolute z-10 w-full bg-white border border-slate-200 shadow-xl rounded-xl mt-1 hidden max-h-60 overflow-y-auto top-full left-0 divide-y divide-slate-100">
                        </div>
                    </div>

                    <div>
                        <label for="customer" class="block text-xs font-medium text-slate-700 mb-1">Customer <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="customer" id="customer" value="{{ old('customer', $job->customer) }}"
                            required readonly
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed outline-none select-none">
                    </div>

                    <div>
                        <label for="location" class="block text-xs font-medium text-slate-700 mb-1">Lokasi / Site <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="location" id="location" value="{{ old('location', $job->location) }}"
                            required readonly
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed outline-none select-none">
                    </div>

                    <div>
                        <label for="unit_type" class="block text-xs font-medium text-slate-700 mb-1">Tipe Unit <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="unit_type" id="unit_type"
                            value="{{ old('unit_type', $job->unit_type) }}" required readonly
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed outline-none select-none">
                    </div>

                    <!-- Nomor Lambung -->
                    <div>
                        <label for="nomor_lambung" class="block text-xs font-medium text-slate-700 mb-1">
                            Nomor Lambung
                        </label>
                        <input type="text" name="nomor_lambung" id="nomor_lambung"
                            value="{{ old('nomor_lambung', $job->nomor_lambung) }}" placeholder="Isi nomor lambung..."
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                    </div>

                    <!-- Year -->
                    <div>
                        <label for="year" class="block text-xs font-medium text-slate-700 mb-1">
                            Year
                        </label>
                        <input type="text" name="year" id="year" value="{{ old('year', $job->year) }}"
                            placeholder="Contoh: 2021"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                    </div>
                </div>

                <!-- KOLOM KANAN: Data Pekerjaan -->
                <div class="space-y-5">
                    <div>
                        <label for="work_date" class="block text-xs font-medium text-slate-700 mb-1">Tanggal Pekerjaan
                            <span class="text-red-500">*</span></label>
                        <input type="date" name="work_date" id="work_date"
                            value="{{ old('work_date', \Carbon\Carbon::parse($job->work_date)->format('Y-m-d')) }}"
                            required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="in_time" class="block text-xs font-medium text-slate-700 mb-1">Jam Mulai
                                Job <span class="text-red-500">*</span></label>
                            <input type="time" name="in_time" id="in_time"
                                value="{{ old('in_time', $job->in_time ? \Carbon\Carbon::parse($job->in_time)->format('H:i') : '') }}"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                        </div>
                        <div>
                            <label for="out_time" class="block text-xs font-medium text-slate-700 mb-1">Jam Selesai
                                Job <span class="text-red-500">*</span></label>
                            <input type="time" name="out_time" id="out_time"
                                value="{{ old('out_time', $job->out_time ? \Carbon\Carbon::parse($job->out_time)->format('H:i') : '') }}"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                        </div>
                    </div>

                    <div>
                        <label for="hour_meter" class="block text-xs font-medium text-slate-700 mb-1">Hour Meter (HM)
                            Unit <span class="text-red-500">*</span></label>
                        <input type="number" name="hour_meter" id="hour_meter"
                            value="{{ old('hour_meter', $job->hour_meter) }}" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="battery_type" class="block text-xs font-medium text-slate-700 mb-1">
                                Battery Type <span class="text-red-500">*</span>
                            </label>
                            <select name="battery_type" id="battery_type" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                                <option value="">Pilih Battery Type</option>
                                @foreach(($batteryTypeOptions ?? ['LEAD ACID', 'LITHIUM']) as $option)
                                <option value="{{ $option }}" {{ old('battery_type', $job->battery_type) === $option ?
                                    'selected' : '' }}>
                                    {{ $option }}
                                </option>
                                @endforeach
                            </select>
                            @error('battery_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="battery_brand" class="block text-xs font-medium text-slate-700 mb-1">
                                Battery Brand <span class="text-red-500">*</span>
                            </label>
                            <select name="battery_brand" id="battery_brand" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                                <option value="">Pilih Battery Brand</option>
                                @foreach(($batteryBrandOptions ?? ['EIKTO', 'ENEROC', 'JUNGHEINRICH', 'YUASA', 'GS',
                                'MIDAC']) as $option)
                                <option value="{{ $option }}" {{ old('battery_brand', $job->battery_brand) === $option ?
                                    'selected' : '' }}>
                                    {{ $option }}
                                </option>
                                @endforeach
                            </select>
                            @error('battery_brand')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            @php
                            $jobTypeChoices = $jobTypeOptions ?? [
                            'Preventive Maintenance',
                            'Install Part',
                            'Troubleshooting',
                            'Inspection',
                            'Repair',
                            ];

                            $selectedJobTypes = collect((array) old('job_type', $job->job_type ? explode(',', (string)
                            $job->job_type) : []))
                            ->flatMap(fn ($value) => is_array($value) ? $value : explode(',', (string) $value))
                            ->map(function ($value) {
                            $value = trim((string) $value);

                            return match (strtoupper($value)) {
                            'PM' => 'Preventive Maintenance',
                            'BM' => 'Troubleshooting',
                            'PDI' => 'Inspection',
                            default => $value,
                            };
                            })
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                            $selectedJobTypeLabel = count($selectedJobTypes) > 0
                            ? implode(', ', $selectedJobTypes)
                            : 'Pilih Tipe Pekerjaan';
                            @endphp

                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                Tipe Pekerjaan <span class="text-red-500">*</span>
                            </label>

                            <div class="relative" data-multi-job-type>
                                <input type="hidden" name="job_type" id="job_type"
                                    value="{{ implode(', ', $selectedJobTypes) }}" data-multi-job-type-value>

                                <button type="button" data-multi-job-type-button
                                    class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow flex items-center justify-between gap-3 text-left">
                                    <span data-multi-job-type-label class="truncate">{{ $selectedJobTypeLabel }}</span>
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div data-multi-job-type-menu
                                    class="hidden absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                                    <div class="max-h-64 overflow-y-auto p-2 space-y-1">
                                        @foreach($jobTypeChoices as $option)
                                        <label
                                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 cursor-pointer text-sm text-slate-700">
                                            <input type="checkbox" value="{{ $option }}" data-multi-job-type-option {{
                                                in_array($option, $selectedJobTypes, true) ? 'checked' : '' }}
                                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <span>{{ $option }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <p class="mt-1 text-[11px] leading-4 text-slate-500">
                                Bisa pilih lebih dari satu.
                            </p>

                            @error('job_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="status_unit" class="block text-xs font-medium text-slate-700 mb-1">Status Akhir
                                Unit <span class="text-red-500">*</span></label>
                            <select name="status_unit" id="status_unit" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                                <option value="">Pilih Status</option>
                                <option value="RFU" {{ old('status_unit', $job->status_unit) == 'RFU' ? 'selected' : ''
                                    }}>RFU (Ready)</option>
                                <option value="B/D" {{ old('status_unit', $job->status_unit) == 'B/D' ? 'selected' : ''
                                    }}>Breakdown</option>
                                <option value="Standby" {{ old('status_unit', $job->status_unit) == 'Standby' ?
                                    'selected' : '' }}>Standby</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Temuan & Tindakan -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Temuan & Tindakan</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="problem_date" class="block text-xs font-medium text-slate-700 mb-1">Tanggal Problem
                            / Breakdown</label>
                        <input type="date" name="problem_date" id="problem_date"
                            value="{{ old('problem_date', $job->problem_date ? \Carbon\Carbon::parse($job->problem_date)->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                    </div>
                    <div>
                        <label for="rfu_date" class="block text-xs font-medium text-slate-700 mb-1">Tanggal RFU (Ready
                            For Use)</label>
                        <input type="date" name="rfu_date" id="rfu_date"
                            value="{{ old('rfu_date', $job->rfu_date ? \Carbon\Carbon::parse($job->rfu_date)->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="problem" class="block text-xs font-medium text-slate-700 mb-1">Problem / Temuan
                            <span class="text-red-500">*</span></label>
                        <textarea name="problem" id="problem" rows="3" required
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('problem', $job->problem) }}</textarea>
                    </div>
                    <div>
                        <label for="action" class="block text-xs font-medium text-slate-700 mb-1">Action / Tindakan
                            <span class="text-red-500">*</span></label>
                        <textarea name="action" id="action" rows="3" required
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('action', $job->action) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Install Parts -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Parts Terpasang (Install Part)
                </h2>
                <button type="button" id="btn-add-inst"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Part
                </button>
            </div>
            <div class="p-6">
                <div id="inst-container" class="space-y-4">
                    <!-- Render Data Lama Jika Ada -->
                    @forelse($job->installParts as $part)
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
                                <label
                                    class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Part
                                    Number</label>
                                <input type="text" name="inst_part_number[]" value="{{ $part->part_number }}"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="lg:col-span-4">
                                <label
                                    class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nama
                                    Part <span class="text-red-500">*</span></label>
                                <input type="text" name="inst_part_name[]" value="{{ $part->part_name }}" required
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="lg:col-span-1">
                                <label
                                    class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Qty
                                    <span class="text-red-500">*</span></label>
                                <input type="number" name="inst_qty[]" value="{{ $part->qty }}" required min="1"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="lg:col-span-2">
                                <label
                                    class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">No.
                                    Job/PR</label>
                                <input type="text" name="inst_no_job[]" value="{{ $part->no_job }}"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="lg:col-span-2">
                                <label
                                    class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Keterangan</label>
                                <input type="text" name="inst_remarks[]" value="{{ $part->remarks }}"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    @empty
                    <p id="inst-empty-text" class="text-sm text-slate-400 text-center py-4">Tidak ada part yang
                        dipasang. Klik 'Tambah Part' jika ada.</p>
                    @endforelse

                    <!-- Fallback if there are items but we need the empty text to exist in DOM for JS logic -->
                    @if($job->installParts->count() > 0)
                    <p id="inst-empty-text" class="text-sm text-slate-400 text-center py-4" style="display: none;">Tidak
                        ada part yang dipasang. Klik 'Tambah Part' jika ada.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section 5: Rekomendasi Parts -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-amber-100 bg-amber-50/50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-amber-900 uppercase tracking-wider">Rekomendasi Part (Next Job)
                </h2>
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
                    <!-- Render Data Lama Jika Ada -->
                    @forelse($job->recommendations as $rec)
                    <div
                        class="rec-item relative bg-amber-50/30 border border-amber-200 rounded-2xl p-4 sm:p-5 pt-8 sm:pt-5 transition-all">
                        <input type="hidden" name="rec_id[]" value="{{ $rec->id }}">
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
                                <label
                                    class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Part
                                    Number</label>
                                <input type="text" name="rec_part_number[]" value="{{ $rec->part_number }}"
                                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            <div class="lg:col-span-4">
                                <label
                                    class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Nama
                                    Part <span class="text-red-500">*</span></label>
                                <input type="text" name="rec_part_name[]" value="{{ $rec->part_name }}" required
                                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            <div class="lg:col-span-1">
                                <label
                                    class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Qty
                                    <span class="text-red-500">*</span></label>
                                <input type="number" name="rec_qty[]" value="{{ $rec->qty }}" required min="1"
                                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            <div class="lg:col-span-4">
                                <label
                                    class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Keterangan
                                    / Alasan</label>
                                <input type="text" name="rec_remarks[]" value="{{ $rec->remarks }}"
                                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                        </div>
                    </div>
                    @empty
                    <p id="rec-empty-text" class="text-sm text-slate-400 text-center py-4">Tidak ada rekomendasi part.
                        Klik 'Tambah Rekomendasi' jika ada.</p>
                    @endforelse

                    @if($job->recommendations->count() > 0)
                    <p id="rec-empty-text" class="text-sm text-slate-400 text-center py-4" style="display: none;">Tidak
                        ada rekomendasi part. Klik 'Tambah Rekomendasi' jika ada.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" id="btn-submit"
                class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all focus:ring-4 focus:ring-blue-100">
                <span id="btn-icon">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </span>
                <span id="btn-text">Simpan Perubahan</span>
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
                <input type="text" name="inst_part_number[]" placeholder="Opsional"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nama Part
                    <span class="text-red-500">*</span></label>
                <input type="text" name="inst_part_name[]" required placeholder="Nama Part"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="lg:col-span-1">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Qty <span
                        class="text-red-500">*</span></label>
                <input type="number" name="inst_qty[]" required value="1" min="1"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">No.
                    Job/PR</label>
                <input type="text" name="inst_no_job[]" placeholder="Opsional"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="lg:col-span-2">
                <label
                    class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Keterangan</label>
                <input type="text" name="inst_remarks[]" placeholder="Opsional"
                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>
</template>

<template id="tmpl-rec">
    <div
        class="rec-item relative bg-amber-50/30 border border-amber-200 rounded-2xl p-4 sm:p-5 pt-8 sm:pt-5 transition-all">
        <input type="hidden" name="rec_id[]" value="">
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
                <input type="text" name="rec_part_number[]" placeholder="Opsional"
                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Nama Part
                    <span class="text-red-500">*</span></label>
                <input type="text" name="rec_part_name[]" required placeholder="Nama Part Rekomendasi"
                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div class="lg:col-span-1">
                <label class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Qty <span
                        class="text-red-500">*</span></label>
                <input type="number" name="rec_qty[]" required value="1" min="1"
                    class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Keterangan /
                    Alasan</label>
                <input type="text" name="rec_remarks[]" placeholder="Alasan direkomendasikan"
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
        const formJob = document.getElementById('form-job');

        formJob.addEventListener('input', () => { formChanged = true; });

        const beforeUnloadHandler = function (e) {
            if (formChanged && !isSubmitting) {
                const msg = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin keluar?';
                e.preventDefault(); e.returnValue = msg; return msg;
            }
        };
        window.addEventListener('beforeunload', beforeUnloadHandler);

        // --- 3. MENCEGAH DOUBLE SUBMIT ---
        let isSubmitting = false;
        const btnSubmit = document.getElementById('btn-submit');
        const btnIcon = document.getElementById('btn-icon');
        const btnText = document.getElementById('btn-text');

        formJob.addEventListener('submit', function(e) {
            if (isSubmitting) { e.preventDefault(); return; }

            isSubmitting = true;
            btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
            btnIcon.innerHTML = `<svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
            btnText.innerText = 'Menyimpan...';

            window.removeEventListener('beforeunload', beforeUnloadHandler);
        });

        // --- 4. AUTOCOMPLETE S/N ---
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
                fetch(`/update-jobs/search-assets?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        snDropdown.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-sm text-slate-700 transition-colors flex flex-col gap-1';
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
                                    snInput.dispatchEvent(new Event('change', { bubbles: true }));

                                    if (window.drrsaktiRunPmCheck) {
                                        setTimeout(function () {
                                            window.drrsaktiRunPmCheck(true);
                                        }, 100);
                                    }
                                });
                                snDropdown.appendChild(div);
                            });
                        } else {
                            snDropdown.innerHTML = '<div class="px-4 py-4 text-sm text-slate-500 italic text-center">Unit tidak ditemukan. Silakan pastikan S/N terdaftar di Manajemen Aset.</div>';
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

        // --- 5. LOGIC DYNAMIC ROWS ---
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
