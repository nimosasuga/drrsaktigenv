{{--
|--------------------------------------------------------------------------
| PATH FILE:
| resources/views/deliveries/edit.blade.php
|--------------------------------------------------------------------------
--}}

@extends('layouts.app')

@section('content')
@php
$statusUser = $user->status_user ?? $user->role ?? '-';
$upperStatusUser = strtoupper((string) $statusUser);

if (str_contains($upperStatusUser, 'FIELD SERVICE')) {
$statusMekanik = 'Field Service';
} elseif (str_contains($upperStatusUser, 'FMC')) {
$statusMekanik = 'FMC';
} else {
$statusMekanik = $delivery->status_mekanik ?? $statusUser;
}

$deliveryDate = $delivery->date ? \Carbon\Carbon::parse($delivery->date)->format('Y-m-d') : now()->format('Y-m-d');
$deliveryInTime = $delivery->in_time ? \Carbon\Carbon::parse($delivery->in_time)->format('H:i') : '';
$deliveryOutTime = $delivery->out_time ? \Carbon\Carbon::parse($delivery->out_time)->format('H:i') : '';
@endphp

<div class="mx-auto max-w-6xl pb-28">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">
                Delivery Unit
            </p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                Edit Delivery Unit
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Perbarui data pengiriman unit, battery, charger, trolly, kendaraan, dan status akhir unit.
            </p>
        </div>

        <a href="{{ route('deliveries.show', $delivery->id) }}"
            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
            <svg class="mr-2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Detail
        </a>
    </div>

    {{-- Alert Error --}}
    @if($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
        <p class="font-bold">Ada data yang perlu diperbaiki:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Permission Info --}}
    <div class="mb-6 rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm sm:p-5">
        <div class="flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M12 18a6 6 0 100-12 6 6 0 000 12z" />
                </svg>
            </div>

            <div>
                <p class="text-sm font-black text-blue-900">
                    Permission Info
                </p>
                <p class="mt-1 text-xs font-semibold text-blue-700">
                    PIC Record: {{ $delivery->pic ?? '-' }} &bull; Login: {{ $user->name }} &bull; Branch: {{ $branch }}
                </p>
            </div>
        </div>
    </div>

    <form id="form-delivery" action="{{ route('deliveries.update', $delivery->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Section 1: Informasi Teknisi --}}
        <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                    Informasi Teknisi
                </h2>
            </div>

            <div class="grid gap-5 p-5 sm:p-6 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        PIC / Mekanik
                    </label>
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        {{ $delivery->pic ?? $user->name }}
                    </div>
                </div>

                <div>
                    <label for="partner" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Partner
                    </label>
                    <select name="partner" id="partner"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <option value="">-- Tidak Ada / Sendiri --</option>
                        @foreach($partners as $partner)
                        <option value="{{ $partner->name }}" {{ old('partner', $delivery->partner) === $partner->name ?
                            'selected' : '' }}>
                            {{ $partner->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Branch / Cabang
                    </label>
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        {{ $branch }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Status Mekanik
                    </label>
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold capitalize text-slate-700">
                        {{ str_replace('_', ' ', $statusMekanik) }}
                    </div>
                </div>
            </div>
        </section>

        {{-- Section 2: Kendaraan & Waktu --}}
        <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                    Kendaraan & Waktu
                </h2>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div class="space-y-5">
                    <div>
                        <label for="date" class="mb-1 block text-xs font-bold text-slate-700">
                            Tanggal Delivery <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date" id="date" value="{{ old('date', $deliveryDate) }}" required
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="in_time" class="mb-1 block text-xs font-bold text-slate-700">
                                Jam Mulai
                            </label>
                            <input type="time" name="in_time" id="in_time" value="{{ old('in_time', $deliveryInTime) }}"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        </div>

                        <div>
                            <label for="out_time" class="mb-1 block text-xs font-bold text-slate-700">
                                Jam Selesai
                            </label>
                            <input type="time" name="out_time" id="out_time"
                                value="{{ old('out_time', $deliveryOutTime) }}"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="vehicle" class="mb-1 block text-xs font-bold text-slate-700">
                            Kendaraan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="vehicle" id="vehicle" value="{{ old('vehicle', $delivery->vehicle) }}"
                            required placeholder="Contoh: L300 / Hilux / Service Car"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label for="nopol" class="mb-1 block text-xs font-bold text-slate-700">
                            Nomor Polisi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nopol" id="nopol" value="{{ old('nopol', $delivery->nopol) }}" required
                            placeholder="Contoh: B 1234 XYZ"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>
                </div>
            </div>
        </section>

        {{-- Section 3: Customer & Unit --}}
        <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                    Customer & Unit
                </h2>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div class="space-y-5">
                    <div class="relative">
                        <label for="serial_number" class="mb-1 block text-xs font-bold text-slate-700">
                            Serial Number Unit <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="serial_number" id="serial_number"
                            value="{{ old('serial_number', $delivery->serial_number) }}" required
                            placeholder="Ketik minimal 2 karakter untuk cari asset" autocomplete="off"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">

                        <div id="sn-dropdown"
                            class="absolute left-0 right-0 z-30 mt-2 hidden max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        </div>
                    </div>

                    <div>
                        <label for="customer" class="mb-1 block text-xs font-bold text-slate-700">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="customer" id="customer"
                            value="{{ old('customer', $delivery->customer) }}" required
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label for="location" class="mb-1 block text-xs font-bold text-slate-700">
                            Lokasi / Site
                        </label>
                        <input type="text" name="location" id="location"
                            value="{{ old('location', $delivery->location) }}"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="unit_type" class="mb-1 block text-xs font-bold text-slate-700">
                            Unit Type
                        </label>
                        <input type="text" name="unit_type" id="unit_type"
                            value="{{ old('unit_type', $delivery->unit_type) }}"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="year" class="mb-1 block text-xs font-bold text-slate-700">
                                Tahun Unit
                            </label>
                            <input type="number" name="year" id="year" value="{{ old('year', $delivery->year) }}"
                                min="1900" max="2100"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        </div>

                        <div>
                            <label for="hour_meter" class="mb-1 block text-xs font-bold text-slate-700">
                                Hour Meter
                            </label>
                            <input type="number" name="hour_meter" id="hour_meter"
                                value="{{ old('hour_meter', $delivery->hour_meter) }}" min="0"
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Section 4: Job Type & Status --}}
        <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                    Job Type & Status
                </h2>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Job Type
                    </label>
                    <div
                        class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-black text-blue-700">
                        DELIVERY UNIT
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        Job type dikunci otomatis oleh sistem.
                    </p>
                </div>

                <div>
                    <label for="status_unit" class="mb-1 block text-xs font-bold text-slate-700">
                        Status Akhir Unit <span class="text-red-500">*</span>
                    </label>
                    <select name="status_unit" id="status_unit" required
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <option value="RFU" {{ old('status_unit', $delivery->status_unit) === 'RFU' ? 'selected' : ''
                            }}>
                            RFU
                        </option>
                        <option value="BREAKDOWN" {{ old('status_unit', $delivery->status_unit) === 'BREAKDOWN' ?
                            'selected' : '' }}>
                            BREAKDOWN
                        </option>
                    </select>
                </div>
            </div>
        </section>

        {{-- Section 5: Battery, Charger, Trolly --}}
        <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                    Battery, Charger & Trolly
                </h2>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div class="space-y-5">
                    <div>
                        <label for="battery_type" class="mb-1 block text-xs font-bold text-slate-700">
                            Battery Type
                        </label>
                        <input type="text" name="battery_type" id="battery_type"
                            value="{{ old('battery_type', $delivery->battery_type) }}"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label for="battery_sn" class="mb-1 block text-xs font-bold text-slate-700">
                            Battery Serial Number
                        </label>
                        <input type="text" name="battery_sn" id="battery_sn"
                            value="{{ old('battery_sn', $delivery->battery_sn) }}"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label for="trolly" class="mb-1 block text-xs font-bold text-slate-700">
                            Trolly
                        </label>
                        <input type="text" name="trolly" id="trolly" value="{{ old('trolly', $delivery->trolly) }}"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="charger_type" class="mb-1 block text-xs font-bold text-slate-700">
                            Charger Type
                        </label>
                        <input type="text" name="charger_type" id="charger_type"
                            value="{{ old('charger_type', $delivery->charger_type) }}"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label for="charger_sn" class="mb-1 block text-xs font-bold text-slate-700">
                            Charger Serial Number
                        </label>
                        <input type="text" name="charger_sn" id="charger_sn"
                            value="{{ old('charger_sn', $delivery->charger_sn) }}"
                            class="uppercase-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label for="note" class="mb-1 block text-xs font-bold text-slate-700">
                            Catatan
                        </label>
                        <textarea name="note" id="note" rows="4" placeholder="Catatan tambahan delivery..."
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">{{ old('note', $delivery->note) }}</textarea>
                    </div>
                </div>
            </div>
        </section>

        {{-- Submit --}}
        <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
            <a href="{{ route('deliveries.show', $delivery->id) }}"
                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
                Batal
            </a>

            <button type="submit" id="btn-submit"
                class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-8 py-3 text-sm font-black text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const formDelivery = document.getElementById('form-delivery');
    const btnSubmit = document.getElementById('btn-submit');
    const upperInputs = document.querySelectorAll('.uppercase-input');

    const snInput = document.getElementById('serial_number');
    const snDropdown = document.getElementById('sn-dropdown');
    const unitTypeInput = document.getElementById('unit_type');
    const customerInput = document.getElementById('customer');
    const locationInput = document.getElementById('location');
    const yearInput = document.getElementById('year');

    let hasUnsavedChanges = false;
    let isSubmitting = false;
    let searchTimeout = null;

    function markChanged() {
        hasUnsavedChanges = true;
    }

    function beforeUnloadHandler(event) {
        if (!hasUnsavedChanges || isSubmitting) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    }

    window.addEventListener('beforeunload', beforeUnloadHandler);

    formDelivery.querySelectorAll('input, select, textarea').forEach(function (element) {
        element.addEventListener('change', markChanged);
        element.addEventListener('input', markChanged);
    });

    upperInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(start, end);
        });
    });

    snInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);

        const query = this.value.trim();

        if (query.length < 2) {
            snDropdown.classList.add('hidden');
            snDropdown.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(function () {
            fetch(`{{ route('deliveries.search-assets') }}?q=${encodeURIComponent(query)}`)
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    snDropdown.innerHTML = '';

                    if (!Array.isArray(data) || data.length === 0) {
                        snDropdown.innerHTML = `
                            <div class="px-4 py-4 text-center text-sm font-semibold text-slate-500">
                                Unit tidak ditemukan.
                            </div>
                        `;
                        snDropdown.classList.remove('hidden');
                        return;
                    }

                    data.forEach(function (item) {
                        const row = document.createElement('button');
                        row.type = 'button';
                        row.className = 'block w-full px-4 py-3 text-left text-sm transition hover:bg-blue-50';
                        row.innerHTML = `
                            <span class="block font-black text-slate-900">${item.serial_number || '-'}</span>
                            <span class="mt-1 block text-xs font-semibold text-slate-500">
                                Tipe: ${item.unit_type || '-'} &bull; Customer: ${item.customer || '-'} &bull; Lokasi: ${item.location || '-'}
                            </span>
                        `;

                        row.addEventListener('click', function () {
                            snInput.value = item.serial_number || '';
                            unitTypeInput.value = item.unit_type || '';
                            customerInput.value = item.customer || '';
                            locationInput.value = item.location || '';

                            if (yearInput && item.year) {
                                yearInput.value = item.year;
                            }

                            snDropdown.classList.add('hidden');
                            snDropdown.innerHTML = '';
                            markChanged();
                        });

                        snDropdown.appendChild(row);
                    });

                    snDropdown.classList.remove('hidden');
                })
                .catch(function () {
                    snDropdown.innerHTML = `
                        <div class="px-4 py-4 text-center text-sm font-semibold text-red-600">
                            Gagal mengambil data asset.
                        </div>
                    `;
                    snDropdown.classList.remove('hidden');
                });
        }, 300);
    });

    document.addEventListener('click', function (event) {
        if (!snInput.contains(event.target) && !snDropdown.contains(event.target)) {
            snDropdown.classList.add('hidden');
        }
    });

    formDelivery.addEventListener('submit', function (event) {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        isSubmitting = true;
        hasUnsavedChanges = false;
        window.removeEventListener('beforeunload', beforeUnloadHandler);

        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
        btnSubmit.innerText = 'Menyimpan...';
    });
});
</script>
@endsection