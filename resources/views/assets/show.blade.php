<!-- PATH FILE: resources/views/assets/show.blade.php -->
@extends('layouts.app')

@section('content')
@php
$user = Auth::user();

$assetManageRoles = ['super_admin', 'admin', 'koordinator', 'sect_head'];

$canManageAsset = in_array(strtolower((string) ($user->role ?? '')), $assetManageRoles, true)
|| in_array(strtolower((string) ($user->status_user ?? '')), $assetManageRoles, true);

$statusClass = match ($asset->status) {
    'RENTAL' => 'bg-blue-50 text-blue-700 border-blue-100',
    'BACKUP' => 'bg-amber-50 text-amber-700 border-amber-100',
    'DITARIK' => 'bg-rose-50 text-rose-700 border-rose-100',
    default => 'bg-slate-50 text-slate-700 border-slate-100',
};

$assetFields = [
    'id' => $asset->id,
    'supported_by' => $asset->supported_by,
    'customer' => $asset->customer,
    'location' => $asset->location,
    'branch' => $asset->branch,
    'serial_number' => $asset->serial_number,
    'unit_type' => $asset->unit_type,
    'year' => $asset->year,
    'status' => $asset->status,
    'delivery' => $asset->delivery,
    'jenis_unit' => $asset->jenis_unit,
    'note' => $asset->note,
    'qr_token' => $asset->qr_token,
    'created_at' => $asset->created_at ? $asset->created_at->format('Y-m-d H:i:s') : null,
    'updated_at' => $asset->updated_at ? $asset->updated_at->format('Y-m-d H:i:s') : null,
];
@endphp

<div class="mx-auto max-w-6xl space-y-8 px-1 pb-28 sm:px-2 lg:px-0">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">
                Detail Asset
            </p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">
                {{ $asset->serial_number ?? '-' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Informasi lengkap semua kolom unit asset dan histori pekerjaan mekanik.
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('assets.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
            @if($canManageAsset)
            <a href="{{ route('assets.edit', $asset->id) }}"
                class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                Edit Asset
            </a>
            @endif
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">

        <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:p-8">
            <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Semua Kolom Unit Asset
                    </h2>
                    <p class="text-sm text-slate-500">
                        Mengikuti struktur: id, supported_by, customer, location, branch, serial_number, unit_type, year, status, delivery, jenis_unit, note, qr_token, created_at, updated_at.
                    </p>
                </div>

                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">
                    {{ $asset->status ?? '-' }}
                </span>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach($assetFields as $field => $value)
                    <div class="rounded-2xl bg-slate-50 p-4 {{ in_array($field, ['note', 'qr_token'], true) ? 'sm:col-span-2' : '' }}">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-500">{{ $field }}</p>
                        <p class="mt-1 break-words text-sm font-semibold text-slate-900 whitespace-pre-line">{{ filled($value) ? $value : '-' }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:p-7">
            <h2 class="text-lg font-bold text-slate-900">
                Ringkasan Asset
            </h2>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                    <span class="text-sm text-slate-500">Status</span>
                    <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ $asset->status ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                    <span class="text-sm text-slate-500">Customer</span>
                    <span class="text-right text-sm font-bold text-slate-900">{{ $asset->customer ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                    <span class="text-sm text-slate-500">Location</span>
                    <span class="text-right text-sm font-bold text-slate-900">{{ $asset->location ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                    <span class="text-sm text-slate-500">Delivery</span>
                    <span class="text-right text-sm font-bold text-slate-900">{{ $asset->delivery ?? '-' }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Histori Pekerjaan Mekanik --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                    Histori Pekerjaan Mekanik
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Riwayat pekerjaan berdasarkan Serial Number: {{ $asset->serial_number ?? '-' }}
                </p>
            </div>

            <span class="inline-flex items-center rounded-xl bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 border border-blue-100">
                {{ $asset->jobHistories->count() }} Job
            </span>
        </div>

        <div class="p-6">
            @if($asset->jobHistories->count() > 0)
            <div class="space-y-4">
                @foreach($asset->jobHistories as $job)
                <div class="relative rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="absolute left-0 top-4 bottom-4 w-1.5 rounded-r-full {{ ($job->status_unit ?? '') === 'RFU' ? 'bg-emerald-500' : 'bg-red-500' }}"></div>

                    <div class="pl-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase text-slate-600">
                                        {{ $job->job_type ?? 'JOB' }}
                                    </span>

                                    @if(($job->status_unit ?? '') === 'RFU')
                                    <span class="inline-flex rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold uppercase text-emerald-700">
                                        RFU
                                    </span>
                                    @elseif(($job->status_unit ?? '') === 'BREAKDOWN')
                                    <span class="inline-flex rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-[11px] font-bold uppercase text-red-700">
                                        BREAKDOWN
                                    </span>
                                    @else
                                    <span class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold uppercase text-amber-700">
                                        {{ $job->status_unit ?? 'MONITORING' }}
                                    </span>
                                    @endif
                                </div>

                                <h3 class="mt-3 text-sm font-black text-slate-900">
                                    {{ $job->problem ?: 'Problem belum diisi' }}
                                </h3>

                                <p class="mt-1 text-sm text-slate-600 whitespace-pre-line">
                                    {{ $job->action ?: 'Action belum diisi' }}
                                </p>
                            </div>

                            <div class="shrink-0 text-left sm:text-right">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">
                                    Tanggal Kerja
                                </p>
                                <p class="mt-1 text-sm font-bold text-slate-800">
                                    {{ $job->work_date ? \Carbon\Carbon::parse($job->work_date)->translatedFormat('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 sm:grid-cols-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">PIC</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-800">{{ $job->pic ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">HM</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-800">{{ $job->hour_meter ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Customer</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-800">{{ $job->customer ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Location</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-800">{{ $job->location ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('update-jobs.show', $job->id) }}"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-colors">
                                Lihat Detail Job
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                <p class="text-sm font-bold text-slate-700">
                    Histori pekerjaan belum tersedia.
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Belum ada data update job dengan serial number ini.
                </p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
