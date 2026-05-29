<!-- PATH FILE: resources/views/penarikans/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl pb-28">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-rose-600">Penarikan Unit</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Tambah Penarikan Unit</h1>
            <p class="mt-1 text-sm text-slate-500">Input data tarik unit sesuai alur form Dart.</p>
        </div>
        <a href="{{ route('penarikans.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm">Kembali</a>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
            <p class="font-bold">Ada data yang perlu diperbaiki:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @php
        $inTimeValue = old('in_time') !== null ? substr((string) old('in_time'), 0, 5) : '';
        $outTimeValue = old('out_time') !== null ? substr((string) old('out_time'), 0, 5) : '';
    @endphp

    <form action="{{ route('penarikans.store') }}" method="POST" class="space-y-6">
        @csrf

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Informasi Teknisi</h2>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                <div><label class="mb-1 block text-xs font-bold text-slate-600">PIC</label><div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">{{ $user->name }}</div></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Partner</label><select name="partner" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"><option value="">Sendiri</option>@foreach($partners as $partner)<option value="{{ $partner->name }}">{{ $partner->name }}</option>@endforeach</select></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Branch</label><div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">{{ $branch }}</div></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Status</label><div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">{{ $statusMekanik }}</div></div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Kendaraan & Waktu</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Tanggal *</label><input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div>
                <div class="grid grid-cols-2 gap-3"><div><label class="mb-1 block text-xs font-bold text-slate-600">IN</label><input type="time" name="in_time" value="{{ $inTimeValue }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div><div><label class="mb-1 block text-xs font-bold text-slate-600">OUT</label><input type="time" name="out_time" value="{{ $outTimeValue }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Vehicle *</label><input type="text" name="vehicle" value="{{ old('vehicle') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Nopol *</label><input type="text" name="nopol" value="{{ old('nopol') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Customer & Unit</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Serial Number *</label><input type="text" name="serial_number" value="{{ old('serial_number') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Customer *</label><input type="text" name="customer" value="{{ old('customer') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Location</label><input type="text" name="location" value="{{ old('location') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Unit Type</label><input type="text" name="unit_type" value="{{ old('unit_type') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Year</label><input type="number" name="year" value="{{ old('year') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Hour Meter</label><input type="text" name="hour_meter" value="{{ old('hour_meter') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Job, Status, Battery, Charger</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Job Type</label><div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-black text-rose-700">TARIK UNIT</div></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Status Unit *</label><select name="status_unit" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"><option value="RFU">RFU</option><option value="BREAKDOWN">BREAKDOWN</option></select></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Battery Type</label><input type="text" name="battery_type" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Battery SN</label><input type="text" name="battery_sn" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Charger Type</label><input type="text" name="charger_type" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Charger SN</label><input type="text" name="charger_sn" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-600">Trolly</label><input type="text" name="trolly" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase"></div>
                <div class="md:col-span-2"><label class="mb-1 block text-xs font-bold text-slate-600">Note</label><textarea name="note" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase">{{ old('note') }}</textarea></div>
            </div>
        </section>

        <div class="flex justify-end gap-3"><a href="{{ route('penarikans.index') }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700">Batal</a><button type="submit" class="rounded-2xl bg-rose-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-rose-900/20">Simpan</button></div>
    </form>
</div>
@endsection
