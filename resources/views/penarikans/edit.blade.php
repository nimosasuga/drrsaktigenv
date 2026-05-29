<!-- PATH FILE: resources/views/penarikans/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl pb-28">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-rose-600">Penarikan Unit</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Edit Penarikan Unit</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $penarikan->penarikan_code ?? '-' }} · {{ $penarikan->serial_number ?? '-' }}</p>
        </div>
        <a href="{{ route('penarikans.show', $penarikan->id) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm">Kembali</a>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
            <p class="font-bold">Ada data yang perlu diperbaiki:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('penarikans.update', $penarikan->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Informasi Teknisi</h2>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                <div><label class="mb-1 block text-xs font-bold text-slate-600">PIC</label><div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">{{ $penarikan->pic ?? $user->name }}</div></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Partner</label><select name="partner" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"><option value="">Sendiri</option>@foreach($partners as $partner)<option value="{{ $partner->name }}" {{ old('partner', $penarikan->partner)===$partner->name ? 'selected' : '' }}>{{ $partner->name }}</option>@endforeach</select></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Branch</label><div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">{{ $branch }}</div></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Status</label><div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">{{ $statusMekanik }}</div></div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Kendaraan & Waktu</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Tanggal *</label><input type="date" name="date" value="{{ old('date', $penarikan->date) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div>
                <div class="grid grid-cols-2 gap-3"><div><label class="mb-1 block text-xs font-bold text-slate-600">IN</label><input type="time" name="in_time" value="{{ old('in_time', $penarikan->in_time) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div><div><label class="mb-1 block text-xs font-bold text-slate-600">OUT</label><input type="time" name="out_time" value="{{ old('out_time', $penarikan->out_time) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Vehicle *</label><input type="text" name="vehicle" value="{{ old('vehicle', $penarikan->vehicle) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Nopol *</label><input type="text" name="nopol" value="{{ old('nopol', $penarikan->nopol) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Customer & Unit</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Serial Number *</label><input type="text" name="serial_number" value="{{ old('serial_number', $penarikan->serial_number) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Customer *</label><input type="text" name="customer" value="{{ old('customer', $penarikan->customer) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Location</label><input type="text" name="location" value="{{ old('location', $penarikan->location) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Unit Type</label><input type="text" name="unit_type" value="{{ old('unit_type', $penarikan->unit_type) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Year</label><input type="number" name="year" value="{{ old('year', $penarikan->year) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Hour Meter</label><input type="text" name="hour_meter" value="{{ old('hour_meter', $penarikan->hour_meter) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Job, Status, Battery, Charger</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Job Type</label><div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-black text-rose-700">TARIK UNIT</div></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Status Unit *</label><select name="status_unit" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"><option value="RFU" {{ old('status_unit', $penarikan->status_unit)==='RFU' ? 'selected' : '' }}>RFU</option><option value="BREAKDOWN" {{ old('status_unit', $penarikan->status_unit)==='BREAKDOWN' ? 'selected' : '' }}>BREAKDOWN</option></select></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Battery Type</label><input type="text" name="battery_type" value="{{ old('battery_type', $penarikan->battery_type) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Battery SN</label><input type="text" name="battery_sn" value="{{ old('battery_sn', $penarikan->battery_sn) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Charger Type</label><input type="text" name="charger_type" value="{{ old('charger_type', $penarikan->charger_type) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Charger SN</label><input type="text" name="charger_sn" value="{{ old('charger_sn', $penarikan->charger_sn) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Trolly</label><input type="text" name="trolly" value="{{ old('trolly', $penarikan->trolly) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div class="md:col-span-2"><label class="mb-1 block text-xs font-bold text-slate-600">Note</label><textarea name="note" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase">{{ old('note', $penarikan->note) }}</textarea></div>
            </div>
        </section>

        <div class="flex justify-end gap-3"><a href="{{ route('penarikans.show', $penarikan->id) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700">Batal</a><button type="submit" class="rounded-2xl bg-rose-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-rose-900/20">Update</button></div>
    </form>
</div>
@endsection
