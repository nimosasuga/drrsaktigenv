<!-- resources/views/update-jobs/show.blade.php -->
@extends('layouts.app')

@section('content')

@php
// Cek Hak Akses di View
$user = Auth::user();
$role = $user->role ?? $user->status_user;
$privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];
$canEdit = ($user->id === $job->user_id) || in_array($role, $privilegedRoles);
@endphp

<div class="max-w-6xl mx-auto">

    <!-- Area Notifikasi Alert (Error/Success) -->
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
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Pekerjaan</h1>
                @if($job->status_unit === 'RFU')
                <span
                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider">RFU
                    (Ready)</span>
                @elseif($job->status_unit === 'B/D')
                <span
                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-700 uppercase tracking-wider">Breakdown</span>
                @else
                <span
                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 uppercase tracking-wider">{{
                    $job->status_unit ?? 'On Progress' }}</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">S/N: <span class="font-semibold text-slate-700">{{
                    $job->serial_number }}</span> &bull; ID Pekerjaan: #{{ str_pad($job->id, 5, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('update-jobs.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            <!-- Logic Tombol Hanya Muncul Jika Ada Akses -->
            @if($canEdit)
            <!-- Tombol Hapus -->
            <form action="{{ route('update-jobs.destroy', $job->id) }}" method="POST" class="inline-block"
                onsubmit="return confirm('PERINGATAN: Yakin ingin menghapus permanen Job ini beserta data part di dalamnya? Aksi ini tidak dapat dibatalkan.');">
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

            <!-- Tombol Edit -->
            <a href="{{ route('update-jobs.edit', $job->id) }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                    </path>
                </svg>
                Edit Job
            </a>
            @endif
        </div>
    </div>

    <!-- Main Grid Layout (Sama seperti sebelumnya) -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-2 space-y-6">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                        Informasi Unit & Pelanggan
                    </h2>
                    <span
                        class="text-xs font-medium text-slate-500 bg-white border border-slate-200 px-2.5 py-1 rounded-md">{{
                        $job->job_type ?? 'General' }}</span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer
                            </p>
                            <p class="text-sm font-bold text-slate-900">{{ $job->customer }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Lokasi /
                                Site</p>
                            <p class="text-sm font-bold text-slate-900">{{ $job->location }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe Unit
                            </p>
                            <p class="text-sm font-medium text-slate-800">{{ $job->unit_type }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Hour Meter
                                (HM)</p>
                            <p class="text-sm font-medium text-slate-800">{{ number_format($job->hour_meter, 0, ',',
                                '.') }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal
                                Pekerjaan</p>
                            <p class="text-sm font-medium text-slate-800">{{
                                \Carbon\Carbon::parse($job->work_date)->translatedFormat('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Waktu
                                Mulai - Selesai</p>
                            <p class="text-sm font-medium text-slate-800">
                                {{ $job->in_time ? \Carbon\Carbon::parse($job->in_time)->format('H:i') : '-' }} s/d
                                {{ $job->out_time ? \Carbon\Carbon::parse($job->out_time)->format('H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tgl
                                Problem / B/D</p>
                            <p class="text-sm font-medium text-slate-800">{{ $job->problem_date ?
                                \Carbon\Carbon::parse($job->problem_date)->translatedFormat('d M Y') : '-' }}</p>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider mb-1">Tgl RFU
                            </p>
                            <p class="text-sm font-bold text-emerald-800">{{ $job->rfu_date ?
                                \Carbon\Carbon::parse($job->rfu_date)->translatedFormat('d M Y') : '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 mb-2">Problem / Masalah:</p>
                        <div class="bg-red-50/50 p-4 rounded-2xl border border-red-100">
                            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $job->problem }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 mb-2">Action / Tindakan Perbaikan:</p>
                        <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $job->action }}</p>
                        </div>
                    </div>
                </div>
            </div>

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
                        Parts Terpasang ({{ $job->installParts->count() }})
                    </h2>
                </div>
                <div class="p-6">
                    @if($job->installParts->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($job->installParts as $part)
                        <div
                            class="p-4 border border-slate-200 rounded-2xl hover:border-indigo-300 hover:shadow-sm transition-all bg-white relative">
                            <span
                                class="absolute -top-2 -right-2 bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded-full border border-indigo-200">Qty:
                                {{ $part->qty }}</span>
                            <p class="text-xs text-slate-500 font-mono mb-1">{{ $part->part_number ?? 'No P/N' }}</p>
                            <p class="text-sm font-bold text-slate-900 mb-2">{{ $part->part_name }}</p>
                            <div
                                class="flex items-center gap-2 text-[11px] text-slate-500 mt-3 pt-3 border-t border-slate-100">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">Job: {{ $part->no_job ?? '-' }}</span>
                                <span class="bg-slate-100 px-2 py-0.5 rounded">PR: {{ $part->no_pr ?? '-' }}</span>
                            </div>
                            @if($part->remarks)
                            <p class="text-xs text-slate-600 mt-2 bg-slate-50 p-2 rounded-lg italic">"{{ $part->remarks
                                }}"</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-sm text-slate-400">Tidak ada part yang dipasang pada job ini.</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-amber-100 bg-amber-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-amber-900 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        Rekomendasi Part ({{ $job->recommendations->count() }})
                    </h2>
                </div>
                <div class="p-6">
                    @if($job->recommendations->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($job->recommendations as $rec)
                        <div
                            class="p-4 border border-amber-200 rounded-2xl hover:bg-amber-50 transition-all bg-white relative">
                            <span
                                class="absolute -top-2 -right-2 bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-full border border-amber-300">Qty:
                                {{ $rec->qty }}</span>
                            <p class="text-xs text-amber-600/70 font-mono mb-1">{{ $rec->part_number ?? 'No P/N' }}</p>
                            <p class="text-sm font-bold text-amber-900 mb-2">{{ $rec->part_name }}</p>
                            @if($rec->remarks)
                            <p class="text-xs text-amber-800 mt-2 bg-amber-100/50 p-2 rounded-lg italic">"{{
                                $rec->remarks }}"</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-sm text-slate-400">Tidak ada rekomendasi part untuk next job.</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>

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
                            class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg shrink-0">
                            {{ substr($job->pic, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $job->pic }}</p>
                            <p class="text-xs text-slate-500 capitalize">{{ str_replace('_', ' ', $job->status_mekanik)
                                }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Partner Kerja
                        </p>
                        @if($job->partner)
                        <div
                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-sm font-medium text-slate-700">
                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            {{ $job->partner }}
                        </div>
                        @else
                        <p class="text-sm text-slate-500 italic">- Tidak ada partner -</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Branch /
                            Cabang</p>
                        <p class="text-sm font-medium text-slate-800">{{ $job->branch }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 bg-slate-50 -mx-6 px-6 py-4 mt-2">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">Informasi
                            Sarana</p>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-500 mb-0.5">Nama Mobil</p>
                                <p class="text-sm font-bold text-slate-800">{{ $job->vehicle_type ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500 mb-0.5">Nopol</p>
                                <span
                                    class="inline-block bg-white border border-slate-300 text-slate-800 text-xs font-mono font-bold px-2 py-1 rounded shadow-sm">{{
                                    $job->nopol ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection