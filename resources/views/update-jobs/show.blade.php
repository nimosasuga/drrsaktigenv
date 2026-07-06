<!-- resources/views/update-jobs/show.blade.php -->
@extends('layouts.app')

@section('content')

@php
// Cek Hak Akses di View
$user = Auth::user();
$role = $user->role ?? $user->status_user;
$privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];
$canEdit = ($user->id === $job->user_id) || in_array($role, $privilegedRoles);
$sparepartReviews = $sparepartReviews ?? collect();
$sparepartReviewsByInstallPart = $sparepartReviewsByInstallPart ?? collect();
$recommendationHistories = $recommendationHistories ?? collect();
$installPartHistories = $installPartHistories ?? collect();
$leadTimeRfuDays = $job->lead_time_rfu;

if ($leadTimeRfuDays === null && $job->problem_date && $job->rfu_date) {
    $leadTimeRfuDays = max(0, (int) \Carbon\Carbon::parse($job->problem_date)->startOfDay()->diffInDays(\Carbon\Carbon::parse($job->rfu_date)->startOfDay()));
}
@endphp

<div class="max-w-6xl mx-auto">

    <!-- Area Notifikasi Alert (Error/Success) -->
    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->has('error'))
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center shadow-sm">
        <svg class="w-5 h-5 mr-3 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span class="text-sm font-medium">{{ $errors->first('error') }}</span>
    </div>
    @endif

    <!-- Header Section -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Pekerjaan</h1>
                <x-status-badge :status="$job->status_unit" />
            </div>
            <p class="text-sm text-slate-500 mt-1">S/N: <span class="font-semibold text-slate-700">{{ $job->serial_number }}</span> &bull; ID Pekerjaan: #{{ str_pad($job->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('update-jobs.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            @if($sparepartReviews->count() > 0)
            <a href="{{ route('rental-spareparts.reviews.index', ['sn_unit' => $job->serial_number]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-purple-50 text-purple-700 border border-purple-100 rounded-xl text-sm font-semibold hover:bg-purple-100 transition-colors shadow-sm focus:ring-2 focus:ring-purple-200">
                Review Sparepart
            </a>
            @endif

            <!-- Logic Tombol Hanya Muncul Jika Ada Akses -->
            @if($canEdit)
            <!-- Tombol Hapus -->
            <form action="{{ route('update-jobs.destroy', $job->id) }}" method="POST" class="inline-block" onsubmit="return confirm('PERINGATAN: Yakin ingin menghapus permanen Job ini beserta data part di dalamnya? Aksi ini tidak dapat dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors shadow-sm focus:ring-2 focus:ring-red-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus
                </button>
            </form>

            <!-- Tombol Edit -->
            <a href="{{ route('update-jobs.edit', $job->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors shadow-sm focus:ring-2 focus:ring-slate-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                Edit Job
            </a>
            @endif
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-2 space-y-6">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Informasi Unit & Pelanggan
                    </h2>
                    <span class="text-xs font-medium text-slate-500 bg-white border border-slate-200 px-2.5 py-1 rounded-md">{{ $job->job_type ?? 'General' }}</span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer</p>
                            <p class="text-sm font-bold text-slate-900">{{ $job->customer }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Lokasi / Site</p>
                            <p class="text-sm font-bold text-slate-900">{{ $job->location }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe Unit</p>
                            <p class="text-sm font-medium text-slate-800">{{ $job->unit_type }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Hour Meter (HM)</p>
                            <p class="text-sm font-medium text-slate-800">{{ number_format($job->hour_meter, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Battery Type</p>
                            <p class="text-sm font-medium text-slate-800">{{ $job->battery_type ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Battery Brand</p>
                            <p class="text-sm font-medium text-slate-800">{{ $job->battery_brand ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Pekerjaan</p>
                            <p class="text-sm font-medium text-slate-800">{{ \Carbon\Carbon::parse($job->work_date)->translatedFormat('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Waktu Mulai - Selesai</p>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Temuan & Tindakan
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tgl Problem / B/D</p>
                            <p class="text-sm font-medium text-slate-800">{{ $job->problem_date ? \Carbon\Carbon::parse($job->problem_date)->translatedFormat('d M Y') : '-' }}</p>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider mb-1">Tgl RFU</p>
                            <p class="text-sm font-bold text-emerald-800">{{ $job->rfu_date ? \Carbon\Carbon::parse($job->rfu_date)->translatedFormat('d M Y') : '-' }}</p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-xl border border-blue-100">
                            <p class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider mb-1">Lead Time RFU</p>
                            <p class="text-sm font-bold text-blue-800">{{ $leadTimeRfuDays !== null ? $leadTimeRfuDays . ' hari' : '-' }}</p>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Parts Terpasang ({{ $job->installParts->count() }})
                    </h2>
                    @if($sparepartReviews->count() > 0)
                    <a href="{{ route('rental-spareparts.reviews.index', ['sn_unit' => $job->serial_number]) }}" class="text-xs font-black text-purple-700 hover:text-purple-900">
                        Lihat Review
                    </a>
                    @endif
                </div>
                <div class="p-6">
                    @if($job->installParts->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($job->installParts as $part)
                        @php
                            $partReviews = $sparepartReviewsByInstallPart->get($part->id, collect());
                        @endphp
                        <div class="p-4 border border-slate-200 rounded-2xl hover:border-indigo-300 hover:shadow-sm transition-all bg-white relative">
                            <span class="absolute -top-2 -right-2 bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded-full border border-indigo-200">Qty: {{ $part->qty }}</span>
                            <p class="text-xs text-slate-500 font-mono mb-1">{{ $part->part_number ?? 'No P/N' }}</p>
                            <p class="text-sm font-bold text-slate-900 mb-2">{{ $part->part_name }}</p>
                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500 mt-3 pt-3 border-t border-slate-100">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">Job: {{ $part->no_job ?? '-' }}</span>
                                <span class="bg-slate-100 px-2 py-0.5 rounded">PR: {{ $part->no_pr ?? '-' }}</span>
                            </div>

                            @if($partReviews->count() > 0)
                            <div class="mt-3 space-y-2">
                                @foreach($partReviews as $review)
                                @php
                                    $statusClass = 'bg-slate-50 text-slate-700 border-slate-200';
                                    if ($review->review_status === 'PENDING_REVIEW') { $statusClass = 'bg-blue-50 text-blue-700 border-blue-200'; }
                                    if ($review->review_status === 'NEED_SOURCE_SELECTION') { $statusClass = 'bg-amber-50 text-amber-700 border-amber-200'; }
                                    if ($review->review_status === 'APPROVED') { $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200'; }
                                    if ($review->review_status === 'REJECTED') { $statusClass = 'bg-red-50 text-red-700 border-red-200'; }
                                @endphp
                                <div class="rounded-xl border border-purple-100 bg-purple-50/60 p-2">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-black {{ $statusClass }}">{{ $review->review_status }}</span>
                                        <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $review->match_type }}</span>
                                        @if($review->is_borrowed)
                                        <span class="rounded-full border border-orange-200 bg-orange-50 px-2 py-0.5 text-[10px] font-black text-orange-700">PINJAM</span>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-[11px]">
                                        <a href="{{ route('rental-spareparts.reviews.index', ['part_number' => $review->part_number, 'sn_unit' => $review->actual_sn_unit ?: $review->job_serial_number]) }}" class="font-bold text-purple-700 hover:text-purple-900">Review Usage</a>
                                        @if($review->movement_id)
                                        <a href="{{ route('rental-spareparts.movements.index', ['movement_type' => 'OUT', 'part_number' => $review->part_number, 'sn_unit' => $review->actual_sn_unit ?: $review->job_serial_number]) }}" class="font-bold text-slate-700 hover:text-slate-900">Movement OUT</a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            @if($part->remarks)
                            <p class="text-xs text-slate-600 mt-2 bg-slate-50 p-2 rounded-lg italic">"{{ $part->remarks }}"</p>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Rekomendasi Part ({{ $job->recommendations->count() }})
                    </h2>
                </div>
                <div class="p-6">
                    @if($job->recommendations->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($job->recommendations as $rec)
                        <div class="p-4 border border-amber-200 rounded-2xl hover:bg-amber-50 transition-all bg-white relative">
                            <span class="absolute -top-2 -right-2 bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-full border border-amber-300">Qty: {{ $rec->qty }}</span>
                            <p class="text-xs text-amber-600/70 font-mono mb-1">{{ $rec->part_number ?? 'No P/N' }}</p>
                            <p class="text-sm font-bold text-amber-900 mb-2">{{ $rec->part_name }}</p>
                            @if($rec->remarks)
                            <p class="text-xs text-amber-800 mt-2 bg-amber-100/50 p-2 rounded-lg italic">"{{ $rec->remarks }}"</p>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Eksekutor (Mekanik)
                    </h2>
                </div>
                <div class="p-6 flex flex-col gap-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg shrink-0">
                            {{ substr($job->pic, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $job->pic }}</p>
                            <p class="text-xs text-slate-500 capitalize">{{ str_replace('_', ' ', $job->status_mekanik) }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Partner Kerja</p>
                        @if($job->partner)
                        <div class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-sm font-medium text-slate-700">
                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            {{ $job->partner }}
                        </div>
                        @else
                        <p class="text-sm text-slate-500 italic">- Tidak ada partner -</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Branch / Cabang</p>
                        <p class="text-sm font-medium text-slate-800">{{ $job->branch }}</p>
                    </div>

                    @if($sparepartReviews->count() > 0)
                    <div class="pt-4 border-t border-purple-100 bg-purple-50 -mx-6 px-6 py-4 mt-2">
                        <p class="text-[11px] font-semibold text-purple-500 uppercase tracking-wider mb-3">Audit Sparepart Rental</p>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div class="rounded-xl bg-white border border-purple-100 p-3">
                                <p class="text-[10px] font-bold uppercase text-purple-400">Review</p>
                                <p class="mt-1 text-xl font-black text-purple-700">{{ $sparepartReviews->count() }}</p>
                            </div>
                            <div class="rounded-xl bg-white border border-emerald-100 p-3">
                                <p class="text-[10px] font-bold uppercase text-emerald-400">Approved</p>
                                <p class="mt-1 text-xl font-black text-emerald-700">{{ $sparepartReviews->where('review_status', 'APPROVED')->count() }}</p>
                            </div>
                        </div>
                        <a href="{{ route('rental-spareparts.reviews.index', ['sn_unit' => $job->serial_number]) }}" class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-purple-600 px-4 py-2 text-xs font-black text-white hover:bg-purple-700">
                            Buka Review Usage
                        </a>
                    </div>
                    @endif

                    <div class="pt-4 border-t border-slate-100 bg-slate-50 -mx-6 px-6 py-4 mt-2">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">Informasi Sarana</p>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-500 mb-0.5">Nama Mobil</p>
                                <p class="text-sm font-bold text-slate-800">{{ $job->vehicle_type ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500 mb-0.5">Nopol</p>
                                <span class="inline-block bg-white border border-slate-300 text-slate-800 text-xs font-mono font-bold px-2 py-1 rounded shadow-sm">{{ $job->nopol ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="mt-6 space-y-6">
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-end">
                <div>
                    <p class="text-sm font-black uppercase tracking-wider text-slate-900">Ringkasan Histori Sparepart</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Filter dan perbandingan qty rekomendasi terhadap qty pemasangan untuk S/N {{ $job->serial_number ?? '-' }}.
                    </p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                            <p class="text-[11px] font-black uppercase tracking-wide text-amber-700">Total Qty Rekomendasi</p>
                            <p class="mt-2 text-3xl font-black text-amber-900">{{ number_format($totalRecommendedQty, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
                            <p class="text-[11px] font-black uppercase tracking-wide text-indigo-700">Total Qty Terpasang</p>
                            <p class="mt-2 text-3xl font-black text-indigo-900">{{ number_format($totalInstalledQty, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('update-jobs.show', $job->id) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <label for="part_number" class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Filter Part Number</label>
                    <select name="part_number" id="part_number"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <option value="">Semua Part Number</option>
                        @foreach($partNumberOptions as $partNumberOption)
                        <option value="{{ $partNumberOption }}" {{ $partNumberFilter === $partNumberOption ? 'selected' : '' }}>
                            {{ $partNumberOption }}
                        </option>
                        @endforeach
                    </select>

                    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <button type="submit" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-blue-700">
                            Terapkan Filter
                        </button>
                        <a href="{{ route('update-jobs.show', $job->id) }}"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-xs font-black text-slate-700 transition hover:bg-slate-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-amber-100 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-amber-100 bg-amber-50/60 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-amber-900">
                        Histori Rekomendasi Sparepart
                    </h2>
                    <p class="mt-1 text-xs font-semibold text-amber-700/80">
                        Semua rekomendasi sparepart untuk S/N {{ $job->serial_number ?? '-' }}.
                    </p>
                </div>

                <span class="inline-flex w-max rounded-full border border-amber-200 bg-white px-3 py-1 text-xs font-black text-amber-700">
                    {{ $recommendationHistories->count() }} Rekomendasi
                </span>
            </div>

            <div class="hidden xl:block">
                <table class="min-w-full table-fixed divide-y divide-amber-100">
                    <thead class="bg-white">
                        <tr>
                            <th class="w-[10%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Tanggal</th>
                            <th class="w-[12%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Job</th>
                            <th class="w-[13%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Part Number</th>
                            <th class="w-[21%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Part Name</th>
                            <th class="w-[7%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Qty</th>
                            <th class="w-[14%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Status</th>
                            <th class="w-[15%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Remarks</th>
                            <th class="w-[8%] px-4 py-3 text-right text-[11px] font-black uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($recommendationHistories as $history)
                        @php
                            $historyJob = $history['job'];
                            $part = $history['part'];
                        @endphp
                        <tr class="align-top transition hover:bg-amber-50/50">
                            <td class="px-4 py-4 text-sm font-bold text-slate-800">
                                {{ $historyJob->work_date ? \Carbon\Carbon::parse($historyJob->work_date)->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm font-black text-slate-900">#{{ str_pad($historyJob->id, 5, '0', STR_PAD_LEFT) }}</p>
                                <p class="mt-1 wrap-break-word text-xs font-semibold text-slate-500">{{ $historyJob->job_type ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-4 wrap-break-word font-mono text-xs font-bold text-amber-700">{{ $part->part_number ?? '-' }}</td>
                            <td class="px-4 py-4 wrap-break-word text-sm font-bold text-slate-900">{{ $part->part_name ?? '-' }}</td>
                            <td class="px-4 py-4 text-sm font-black text-slate-800">{{ $part->qty ?? '-' }}</td>
                            <td class="px-4 py-4">
                                @if($history['is_installed'])
                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-700">
                                    Sudah Dipasang · Qty {{ number_format($history['installed_qty'], 0, ',', '.') }}
                                </span>
                                @else
                                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-black text-slate-500">
                                    Belum Dipasang
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 wrap-break-word whitespace-pre-line text-sm font-semibold text-slate-600">{{ $part->remarks ?? '-' }}</td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('update-jobs.show', $historyJob->id) }}"
                                    class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-3 py-2 text-xs font-black text-white transition hover:bg-amber-700">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center">
                                <p class="text-sm font-bold text-slate-600">Belum ada histori rekomendasi sparepart.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 xl:hidden">
                @forelse($recommendationHistories as $history)
                @php
                    $historyJob = $history['job'];
                    $part = $history['part'];
                @endphp
                <div class="p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                {{ $historyJob->work_date ? \Carbon\Carbon::parse($historyJob->work_date)->translatedFormat('d M Y') : '-' }}
                            </p>
                            <h3 class="mt-1 wrap-break-word text-sm font-black text-slate-950">{{ $part->part_name ?? '-' }}</h3>
                            <p class="mt-1 wrap-break-word font-mono text-xs font-bold text-amber-700">{{ $part->part_number ?? '-' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="w-max rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">Qty {{ $part->qty ?? '-' }}</span>
                            @if($history['is_installed'])
                            <span class="w-max rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                                Sudah Dipasang · Qty {{ number_format($history['installed_qty'], 0, ',', '.') }}
                            </span>
                            @else
                            <span class="w-max rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-500">
                                Belum Dipasang
                            </span>
                            @endif
                        </div>
                    </div>

                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Job</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800">#{{ str_pad($historyJob->id, 5, '0', STR_PAD_LEFT) }} · {{ $historyJob->job_type ?? '-' }}</dd>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Remarks</dt>
                            <dd class="mt-1 wrap-break-word whitespace-pre-line text-sm font-bold text-slate-800">{{ $part->remarks ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('update-jobs.show', $historyJob->id) }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-amber-700">
                            Lihat Detail Job
                        </a>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center">
                    <p class="text-sm font-bold text-slate-600">Belum ada histori rekomendasi sparepart.</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-indigo-100 bg-indigo-50/60 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-indigo-900">
                        Histori Pemasangan Sparepart
                    </h2>
                    <p class="mt-1 text-xs font-semibold text-indigo-700/80">
                        Semua sparepart terpasang untuk S/N {{ $job->serial_number ?? '-' }}.
                    </p>
                </div>

                <span class="inline-flex w-max rounded-full border border-indigo-200 bg-white px-3 py-1 text-xs font-black text-indigo-700">
                    {{ $installPartHistories->count() }} Pemasangan
                </span>
            </div>

            <div class="hidden xl:block">
                <table class="min-w-full table-fixed divide-y divide-indigo-100">
                    <thead class="bg-white">
                        <tr>
                            <th class="w-[11%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Tanggal</th>
                            <th class="w-[13%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Job</th>
                            <th class="w-[13%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Part Number</th>
                            <th class="w-[22%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Part Name</th>
                            <th class="w-[6%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Qty</th>
                            <th class="w-[10%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">No Job</th>
                            <th class="w-[10%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">No PR</th>
                            <th class="w-[15%] px-4 py-3 text-right text-[11px] font-black uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($installPartHistories as $history)
                        @php
                            $historyJob = $history['job'];
                            $part = $history['part'];
                        @endphp
                        <tr class="align-top transition hover:bg-indigo-50/50">
                            <td class="px-4 py-4 text-sm font-bold text-slate-800">
                                {{ $historyJob->work_date ? \Carbon\Carbon::parse($historyJob->work_date)->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm font-black text-slate-900">#{{ str_pad($historyJob->id, 5, '0', STR_PAD_LEFT) }}</p>
                                <p class="mt-1 wrap-break-word text-xs font-semibold text-slate-500">{{ $historyJob->job_type ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-4 wrap-break-word font-mono text-xs font-bold text-indigo-700">{{ $part->part_number ?? '-' }}</td>
                            <td class="px-4 py-4 wrap-break-word text-sm font-bold text-slate-900">{{ $part->part_name ?? '-' }}</td>
                            <td class="px-4 py-4 text-sm font-black text-slate-800">{{ $part->qty ?? '-' }}</td>
                            <td class="px-4 py-4 wrap-break-word text-sm font-semibold text-slate-700">{{ $part->no_job ?? '-' }}</td>
                            <td class="px-4 py-4 wrap-break-word text-sm font-semibold text-slate-700">{{ $part->no_pr ?? '-' }}</td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('update-jobs.show', $historyJob->id) }}"
                                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-3 py-2 text-xs font-black text-white transition hover:bg-indigo-700">
                                        Detail
                                    </a>
                                    @if($part->remarks)
                                    <span title="{{ $part->remarks }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600">
                                        Note
                                    </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center">
                                <p class="text-sm font-bold text-slate-600">Belum ada histori pemasangan sparepart.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 xl:hidden">
                @forelse($installPartHistories as $history)
                @php
                    $historyJob = $history['job'];
                    $part = $history['part'];
                @endphp
                <div class="p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                {{ $historyJob->work_date ? \Carbon\Carbon::parse($historyJob->work_date)->translatedFormat('d M Y') : '-' }}
                            </p>
                            <h3 class="mt-1 wrap-break-word text-sm font-black text-slate-950">{{ $part->part_name ?? '-' }}</h3>
                            <p class="mt-1 wrap-break-word font-mono text-xs font-bold text-indigo-700">{{ $part->part_number ?? '-' }}</p>
                        </div>
                        <span class="w-max rounded-full bg-indigo-50 px-3 py-1 text-xs font-black text-indigo-700">Qty {{ $part->qty ?? '-' }}</span>
                    </div>

                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Job</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800">#{{ str_pad($historyJob->id, 5, '0', STR_PAD_LEFT) }} · {{ $historyJob->job_type ?? '-' }}</dd>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">No Job / No PR</dt>
                            <dd class="mt-1 wrap-break-word text-sm font-bold text-slate-800">{{ $part->no_job ?? '-' }} / {{ $part->no_pr ?? '-' }}</dd>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3 sm:col-span-2">
                            <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Remarks</dt>
                            <dd class="mt-1 wrap-break-word whitespace-pre-line text-sm font-bold text-slate-800">{{ $part->remarks ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('update-jobs.show', $historyJob->id) }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-indigo-700">
                            Lihat Detail Job
                        </a>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center">
                    <p class="text-sm font-bold text-slate-600">Belum ada histori pemasangan sparepart.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
