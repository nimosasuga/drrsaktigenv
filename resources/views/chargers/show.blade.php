<!-- resources/views/chargers/show.blade.php -->
@extends('layouts.app')

@section('content')

@php
$user = Auth::user();
$role = $user->role ?? $user->status_user;
$privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];
$canEdit = ($user->id === $charger->user_id) || in_array($role, $privilegedRoles);
@endphp

<div class="max-w-6xl mx-auto pb-28">

    @if(session('success'))
    <div
        class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->has('error'))
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-3 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
            </path>
        </svg>
        <span class="text-sm font-medium">{{ $errors->first('error') }}</span>
    </div>
    @endif

    <!-- Header Section -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Detail Pekerjaan Charger
                </h1>

                @if($charger->status_unit === 'RFU')
                <span
                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider">
                    RFU
                </span>
                @elseif($charger->status_unit === 'BREAKDOWN')
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-700 uppercase tracking-wider">
                    B/D
                </span>
                @else
                <span
                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 uppercase tracking-wider">
                    {{ $charger->status_unit ?? 'MONITORING' }}
                </span>
                @endif
            </div>

            <p class="text-sm text-slate-500 mt-1">
                ID Record: #{{ str_pad($charger->id, 5, '0', STR_PAD_LEFT) }}
                &bull;
                Unit S/N:
                <span class="font-bold text-slate-700">
                    {{ $charger->serial_number ?? '-' }}
                </span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('chargers.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            @if($canEdit)
            <form action="{{ route('chargers.destroy', $charger->id) }}" method="POST" class="inline-block"
                onsubmit="return confirm('PERINGATAN: Yakin ingin menghapus permanen data Charger ini beserta parts di dalamnya? Aksi ini tidak dapat dibatalkan.');">
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

            <a href="{{ route('chargers.edit', $charger->id) }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition-colors shadow-sm focus:ring-2 focus:ring-amber-100">
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

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- KOLOM KIRI -->
        <div class="xl:col-span-2 space-y-6">

            <!-- Card 1: Informasi Unit & Charger -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative">
                <div class="absolute top-0 right-0 left-0 h-1 bg-amber-500"></div>

                <div
                    class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2 mt-1">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Identitas Unit & Charger
                    </h2>

                    <span
                        class="text-xs font-bold text-slate-700 bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm">
                        {{ $charger->category_job ?? 'CHARGER JOB' }}
                    </span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                Customer
                            </p>
                            <p class="text-sm font-bold text-slate-900">
                                {{ $charger->customer ?? '-' }}
                            </p>
                        </div>

                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                Lokasi / Site
                            </p>
                            <p class="text-sm font-bold text-slate-900">
                                {{ $charger->location ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                Tipe Unit
                            </p>
                            <p class="text-sm font-medium text-slate-800">
                                {{ $charger->unit_type ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                S/N Unit
                            </p>
                            <p class="text-sm font-medium text-slate-800">
                                {{ $charger->serial_number ?? '-' }}
                            </p>
                        </div>

                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                Tipe Pekerjaan
                            </p>

                            <div class="flex flex-wrap gap-1.5 mt-0.5">
                                @forelse(explode(',', $charger->job_type ?? '') as $jtype)
                                @if(trim($jtype) != '')
                                <span
                                    class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded text-xs font-bold">
                                    {{ trim($jtype) }}
                                </span>
                                @endif
                                @empty
                                <span
                                    class="bg-slate-50 text-slate-500 border border-slate-200 px-2 py-0.5 rounded text-xs font-bold">
                                    -
                                </span>
                                @endforelse
                            </div>
                        </div>

                        <!-- Data Charger Spesifik -->
                        <div class="col-span-4 mt-2 pt-4 border-t border-slate-100">
                            <div
                                class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        S/N Charger Fisik
                                    </p>
                                    <p class="text-base font-bold text-amber-700 font-mono">
                                        {{ $charger->sn_charger ?? '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        Charger Type / Model
                                    </p>
                                    <p class="text-sm font-bold text-slate-800">
                                        {{ $charger->charger_type ?? '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                        Tahun Charger
                                    </p>
                                    <p class="text-sm font-bold text-slate-800">
                                        {{ $charger->charger_year ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Card 2: Temuan & Tindakan -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        Temuan & Tindakan
                    </h2>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                                Tgl Problem / Tarik
                            </p>
                            <p class="text-sm font-medium text-slate-800">
                                {{ $charger->problem_date ?
                                \Carbon\Carbon::parse($charger->problem_date)->translatedFormat('d M Y') : '-' }}
                            </p>
                        </div>

                        <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider mb-1">
                                Tgl RFU / Selesai
                            </p>
                            <p class="text-sm font-bold text-emerald-800">
                                {{ $charger->rfu_date ? \Carbon\Carbon::parse($charger->rfu_date)->translatedFormat('d M
                                Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-slate-800 mb-2">
                            Problem / Temuan:
                        </p>
                        <div class="bg-red-50/50 p-4 rounded-2xl border border-red-100">
                            <p class="text-sm text-slate-700 whitespace-pre-line">
                                {{ $charger->problem ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-slate-800 mb-2">
                            Action / Tindakan Perbaikan:
                        </p>
                        <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                            <p class="text-sm text-slate-700 font-mono whitespace-pre-line leading-relaxed">
                                {{ $charger->action ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Card 3: Parts Terpasang -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Parts Terpasang ({{ $charger->installParts->count() }})
                    </h2>
                </div>

                <div class="p-6">
                    @if($charger->installParts->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($charger->installParts as $part)
                        <div
                            class="p-4 border border-slate-200 rounded-2xl hover:border-indigo-300 hover:shadow-sm transition-all bg-white relative">
                            <span
                                class="absolute -top-2 -right-2 bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded-full border border-indigo-200">
                                Qty: {{ $part->qty }}
                            </span>

                            <p class="text-xs text-slate-500 font-mono mb-1">
                                {{ $part->part_number ?? 'No P/N' }}
                            </p>

                            <p class="text-sm font-bold text-slate-900 mb-2">
                                {{ $part->part_name }}
                            </p>

                            <div
                                class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500 mt-3 pt-3 border-t border-slate-100">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">
                                    Job: {{ $part->no_job ?? '-' }}
                                </span>
                                <span class="bg-slate-100 px-2 py-0.5 rounded">
                                    PR: {{ $part->no_pr ?? '-' }}
                                </span>
                            </div>

                            @if($part->remarks)
                            <p class="text-xs text-slate-600 mt-2 bg-slate-50 p-2 rounded-lg italic">
                                "{{ $part->remarks }}"
                            </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-sm text-slate-400">
                            Tidak ada part yang dipasang pada pekerjaan ini.
                        </p>
                    </div>
                    @endif
                </div>
            </div>


            <!-- Card 4: Rekomendasi Parts -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-amber-100 bg-amber-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-amber-900 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        Rekomendasi Part ({{ $charger->recommendations->count() }})
                    </h2>
                </div>

                <div class="p-6">
                    @if($charger->recommendations->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($charger->recommendations as $rec)
                        <div
                            class="p-4 border border-amber-200 rounded-2xl hover:bg-amber-50 transition-all bg-white relative">
                            <span
                                class="absolute -top-2 -right-2 bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-full border border-amber-300">
                                Qty: {{ $rec->qty }}
                            </span>

                            <p class="text-xs text-amber-600/70 font-mono mb-1">
                                {{ $rec->part_number ?? 'No P/N' }}
                            </p>

                            <p class="text-sm font-bold text-amber-900 mb-2">
                                {{ $rec->part_name }}
                            </p>

                            @if($rec->remarks)
                            <p class="text-xs text-amber-800 mt-2 bg-amber-100/50 p-2 rounded-lg italic">
                                "{{ $rec->remarks }}"
                            </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-sm text-slate-400">
                            Tidak ada rekomendasi part untuk next job.
                        </p>
                    </div>
                    @endif
                </div>
            </div>

        </div>


        <!-- KOLOM KANAN -->
        <div class="space-y-6">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden sticky top-6">
                <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        Eksekutor (Mekanik)
                    </h2>
                </div>

                <div class="p-6 flex flex-col gap-5">

                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-lg shrink-0 border border-amber-200">
                            {{ substr($charger->pic ?? 'U', 0, 1) }}
                        </div>

                        <div>
                            <p class="text-sm font-bold text-slate-900">
                                {{ $charger->pic ?? '-' }}
                            </p>
                            <p class="text-xs text-slate-500 capitalize">
                                {{ str_replace('_', ' ', $charger->status_mekanik ?? '-') }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                            Partner Kerja
                        </p>

                        @if($charger->partner)
                        <div
                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-sm font-medium text-slate-700">
                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            {{ $charger->partner }}
                        </div>
                        @else
                        <p class="text-sm text-slate-500 italic">
                            - Tidak ada partner -
                        </p>
                        @endif
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
                            Branch / Cabang
                        </p>
                        <p class="text-sm font-medium text-slate-800">
                            {{ $charger->branch ?? '-' }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 bg-slate-50 -mx-6 px-6 py-4 mt-2">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">
                            Waktu & Kendaraan
                        </p>

                        <div
                            class="flex items-center justify-between mb-3 bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm">
                            <div class="text-center w-full">
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">
                                    Tanggal
                                </p>
                                <p class="text-sm font-bold text-slate-800">
                                    {{ $charger->date ? \Carbon\Carbon::parse($charger->date)->translatedFormat('d M Y')
                                    : '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <div class="text-center flex-1">
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">
                                    Jam Masuk
                                </p>
                                <p class="text-sm font-bold text-emerald-600">
                                    {{ $charger->in_time ? \Carbon\Carbon::parse($charger->in_time)->format('H:i') : '-'
                                    }}
                                </p>
                            </div>

                            <div class="w-px h-8 bg-slate-200"></div>

                            <div class="text-center flex-1">
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">
                                    Jam Keluar
                                </p>
                                <p class="text-sm font-bold text-red-600">
                                    {{ $charger->out_time ? \Carbon\Carbon::parse($charger->out_time)->format('H:i') :
                                    '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start justify-between border-t border-slate-200 pt-3 gap-3">
                            <div>
                                <p class="text-xs text-slate-500 mb-0.5">
                                    Nama Mobil
                                </p>
                                <p class="text-sm font-bold text-slate-800">
                                    {{ $charger->vehicle ?? '-' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-xs text-slate-500 mb-0.5">
                                    Nopol
                                </p>
                                <span
                                    class="inline-block bg-white border border-slate-300 text-slate-800 text-xs font-mono font-bold px-2 py-1 rounded shadow-sm">
                                    {{ $charger->nopol ?? '-' }}
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