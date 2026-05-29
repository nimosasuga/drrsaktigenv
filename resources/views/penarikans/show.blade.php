<!-- PATH FILE: resources/views/penarikans/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl pb-28">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-rose-600">Penarikan Unit</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Detail Penarikan Unit</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $penarikan->penarikan_code ?? '-' }} · {{ $penarikan->serial_number ?? '-' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('penarikans.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm">Kembali</a>
            <a href="{{ route('penarikans.edit', $penarikan->id) }}" class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-rose-900/20">Edit</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700 shadow-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700 shadow-sm">{{ $errors->first() }}</div>
    @endif

    @php
        $status = strtoupper((string) ($penarikan->status_unit ?? ''));
        $statusClass = $status === 'RFU' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200';
    @endphp

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-rose-900">Informasi Unit</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div><p class="text-xs font-bold uppercase text-slate-400">Kode</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->penarikan_code ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Status</p><span class="mt-1 inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">{{ $penarikan->status_unit ?? '-' }}</span></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Serial Number</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->serial_number ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Unit Type</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->unit_type ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Customer</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->customer ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Lokasi</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->location ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Tahun</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->year ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Hour Meter</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->hour_meter ?? '-' }}</p></div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-900">PIC & Waktu</h2>
            <div class="space-y-4">
                <div><p class="text-xs font-bold uppercase text-slate-400">Tanggal</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->date ? \Carbon\Carbon::parse($penarikan->date)->format('d/m/Y') : '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">PIC</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->pic ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Partner</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->partner ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Branch</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->branch ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase text-slate-400">Jam</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->in_time ?? '-' }} - {{ $penarikan->out_time ?? '-' }}</p></div>
            </div>
        </section>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-900">Kendaraan</h2>
            <div class="grid gap-4 md:grid-cols-2"><div><p class="text-xs font-bold uppercase text-slate-400">Vehicle</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->vehicle ?? '-' }}</p></div><div><p class="text-xs font-bold uppercase text-slate-400">Nopol</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->nopol ?? '-' }}</p></div></div>
        </section>
        <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-900">Battery / Charger / Trolly</h2>
            <div class="grid gap-4 md:grid-cols-2"><div><p class="text-xs font-bold uppercase text-slate-400">Battery</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->battery_type ?? '-' }} / {{ $penarikan->battery_sn ?? '-' }}</p></div><div><p class="text-xs font-bold uppercase text-slate-400">Charger</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->charger_type ?? '-' }} / {{ $penarikan->charger_sn ?? '-' }}</p></div><div><p class="text-xs font-bold uppercase text-slate-400">Trolly</p><p class="mt-1 text-sm font-black text-slate-900">{{ $penarikan->trolly ?? '-' }}</p></div></div>
        </section>
    </div>

    <section class="mt-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-900">Note</h2>
        <p class="whitespace-pre-line text-sm font-semibold leading-relaxed text-slate-700">{{ $penarikan->note ?: '-' }}</p>
    </section>
</div>
@endsection
