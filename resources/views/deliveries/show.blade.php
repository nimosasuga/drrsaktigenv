{{--
|--------------------------------------------------------------------------
| PATH FILE:
| resources/views/deliveries/show.blade.php
|--------------------------------------------------------------------------
--}}

@extends('layouts.app')

@section('content')
@php
$user = Auth::user();
$role = $user->role ?? $user->status_user;
$privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];

$canEdit = ($delivery->user_id === $user->id)
|| ($delivery->pic === $user->name)
|| in_array($role, $privilegedRoles);
@endphp

<div class="max-w-6xl mx-auto pb-28">

    {{-- Alert Success --}}
    @if(session('success'))
    <div
        class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Alert Error --}}
    @if($errors->any())
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl shadow-sm">
        <div class="flex items-start">
            <svg class="w-5 h-5 mr-3 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
            <div>
                <p class="text-sm font-bold">Terjadi kesalahan:</p>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Header Section --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Detail Delivery Unit
                </h1>

                <span
                    class="inline-flex items-center rounded-lg border px-3 py-1 text-xs font-black uppercase tracking-wide {{ $delivery->status_badge_class }}">
                    {{ $delivery->status_unit ?? '-' }}
                </span>
            </div>

            <p class="text-sm text-slate-500 mt-1">
                ID Record: #{{ str_pad($delivery->id, 5, '0', STR_PAD_LEFT) }}
                &bull;
                Kode:
                <span class="font-bold text-slate-700">
                    {{ $delivery->delivery_code ?? '-' }}
                </span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('deliveries.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            @if($canEdit)
            <form action="{{ route('deliveries.destroy', $delivery->id) }}" method="POST" class="inline-block"
                onsubmit="return confirm('Yakin ingin menghapus data Delivery Unit ini? Aksi ini tidak dapat dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors shadow-sm focus:ring-2 focus:ring-red-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    Hapus
                </button>
            </form>

            <a href="{{ route('deliveries.edit', $delivery->id) }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm focus:ring-2 focus:ring-blue-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                    </path>
                </svg>
                Edit Data
            </a>
            @endif
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Left Column --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Card 1: Identitas Delivery --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative">
                <div class="absolute top-0 right-0 left-0 h-1 bg-blue-600"></div>

                <div
                    class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2 mt-1">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10h2m8 0h2m4 0h2V9a1 1 0 00-.293-.707l-3-3A1 1 0 0017 5h-4v11z">
                            </path>
                        </svg>
                        Identitas Delivery
                    </h2>

                    <span
                        class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-lg shadow-sm">
                        {{ $delivery->display_job_type }}
                    </span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer
                            </p>
                            <p class="text-sm font-bold text-slate-900">{{ $delivery->customer ?? '-' }}</p>
                        </div>

                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Lokasi /
                                Site</p>
                            <p class="text-sm font-bold text-slate-900">{{ $delivery->location ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe Unit
                            </p>
                            <p class="text-sm font-medium text-slate-800">{{ $delivery->unit_type ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">S/N Unit
                            </p>
                            <p class="text-sm font-medium text-slate-800">{{ $delivery->serial_number ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tahun</p>
                            <p class="text-sm font-medium text-slate-800">{{ $delivery->year ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Hour Meter
                            </p>
                            <p class="text-sm font-medium text-slate-800">{{ $delivery->hour_meter ?? '-' }}</p>
                        </div>

                        <div class="col-span-4 mt-2 pt-4 border-t border-slate-100">
                            <div
                                class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        Battery Type</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $delivery->battery_type ?? '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        Battery S/N</p>
                                    <p class="text-base font-bold text-amber-700 font-mono">{{ $delivery->battery_sn ??
                                        '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        Trolly</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $delivery->trolly ?? '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        Charger Type</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $delivery->charger_type ?? '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        Charger S/N</p>
                                    <p class="text-base font-bold text-blue-700 font-mono">{{ $delivery->charger_sn ??
                                        '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        Status Unit</p>
                                    <span
                                        class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-black uppercase tracking-wide {{ $delivery->status_badge_class }}">
                                        {{ $delivery->status_unit ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Catatan --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Catatan Delivery
                    </h2>
                </div>

                <div class="p-6">
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-5">
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                            {{ $delivery->note ?: 'Tidak ada catatan tambahan.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden xl:sticky xl:top-6">
                <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                        Eksekutor
                    </h2>
                </div>

                <div class="p-6 flex flex-col gap-5">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg shrink-0 border border-blue-200">
                            {{ substr($delivery->pic ?? 'U', 0, 1) }}
                        </div>

                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $delivery->pic ?? '-' }}</p>
                            <p class="text-xs text-slate-500 capitalize">{{ str_replace('_', ' ',
                                $delivery->status_mekanik ?? '-') }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Partner Kerja
                        </p>

                        @if($delivery->partner)
                        <div
                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-sm font-medium text-slate-700">
                            {{ $delivery->partner }}
                        </div>
                        @else
                        <p class="text-sm text-slate-500 italic">- Tidak ada partner -</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Branch /
                            Cabang</p>
                        <p class="text-sm font-medium text-slate-800">{{ $delivery->branch ?? '-' }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 bg-slate-50 -mx-6 px-6 py-4 mt-2">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">Waktu &
                            Kendaraan</p>

                        <div
                            class="flex items-center justify-between mb-3 bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm">
                            <div class="text-center w-full">
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Tanggal</p>
                                <p class="text-sm font-bold text-slate-800">{{ $delivery->formatted_date }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <div class="text-center flex-1">
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Jam Masuk</p>
                                <p class="text-sm font-bold text-emerald-600">{{ $delivery->formatted_in_time }}</p>
                            </div>

                            <div class="w-px h-8 bg-slate-200"></div>

                            <div class="text-center flex-1">
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Jam Keluar</p>
                                <p class="text-sm font-bold text-red-600">{{ $delivery->formatted_out_time }}</p>
                            </div>
                        </div>

                        <div class="flex items-start justify-between border-t border-slate-200 pt-3 gap-3">
                            <div>
                                <p class="text-xs text-slate-500 mb-0.5">Kendaraan</p>
                                <p class="text-sm font-bold text-slate-800">{{ $delivery->vehicle ?? '-' }}</p>
                            </div>

                            <div class="text-right">
                                <p class="text-xs text-slate-500 mb-0.5">Nopol</p>
                                <span
                                    class="inline-block bg-white border border-slate-300 text-slate-800 text-xs font-mono font-bold px-2 py-1 rounded shadow-sm">
                                    {{ $delivery->nopol ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection