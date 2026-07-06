@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                <span>/</span>
                <span>{{ $title }}</span>
            </div>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $title }}</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">
                Periode {{ $pmMonth->translatedFormat('F Y') }} · {{ number_format($assets->total(), 0, ',', '.') }} unit
            </p>
        </div>

        <a href="{{ route('dashboard') }}"
            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
            Kembali
        </a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('dashboard.pm-status', $status) }}" class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari S/N, customer, lokasi, tipe unit, status..."
                class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            <div class="grid grid-cols-2 gap-2 sm:flex sm:w-auto">
                <button type="submit"
                    class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">
                    Filter
                </button>
                <a href="{{ route('dashboard.pm-status', $status) }}"
                    class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-black text-slate-700 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="hidden xl:block">
            <table class="w-full table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        <th class="w-[13%] px-4 py-3">S/N</th>
                        <th class="w-[20%] px-4 py-3">Customer / Lokasi</th>
                        <th class="w-[17%] px-4 py-3">Unit</th>
                        <th class="w-[11%] px-4 py-3">Status</th>
                        <th class="w-[16%] px-4 py-3">PM Bulan Ini</th>
                        <th class="w-[13%] px-4 py-3">PIC / HM</th>
                        <th class="w-[10%] px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($assets as $asset)
                    @php
                        $pmJob = $pmJobsBySerial->get($asset->serial_number);
                        $assetStatus = strtoupper(trim((string) ($asset->status ?? '')));
                        $statusClass = $assetStatus === 'RENTAL'
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                            : ($assetStatus === 'BACKUP' || $assetStatus === 'STANDBY'
                                ? 'bg-amber-50 text-amber-700 border-amber-100'
                                : 'bg-slate-50 text-slate-700 border-slate-200');
                    @endphp
                    <tr class="align-top hover:bg-slate-50/70">
                        <td class="px-4 py-4">
                            <p class="break-words font-black text-slate-950">{{ $asset->serial_number ?: '-' }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-400">#{{ $asset->id }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="break-words font-bold text-slate-900">{{ $asset->customer ?: '-' }}</p>
                            <p class="mt-1 break-words text-xs font-semibold text-slate-500">{{ $asset->location ?: '-' }}</p>
                            <p class="mt-1 break-words text-[11px] font-semibold text-slate-400">{{ $asset->branch ?: '-' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="break-words font-bold text-slate-900">{{ $asset->unit_type ?: '-' }}</p>
                            <p class="mt-1 break-words text-xs font-semibold text-slate-500">{{ $asset->jenis_unit ?: '-' }}</p>
                            <p class="mt-1 text-[11px] font-semibold text-slate-400">Year {{ $asset->year ?: '-' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black {{ $statusClass }}">
                                {{ $asset->status ?: '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            @if($pmJob)
                                <x-status-badge status="Sudah PM" />
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    {{ $pmJob->work_date ? $pmJob->work_date->format('d/m/Y') : '-' }}
                                </p>
                                <p class="mt-1 break-words text-[11px] font-semibold text-slate-400">
                                    {{ $pmJob->job_type ?: 'Preventive Maintenance' }}
                                </p>
                            @else
                                <x-status-badge status="Belum PM" />
                                <p class="mt-1 text-xs font-semibold text-slate-500">Belum ada PM bulan ini</p>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($pmJob)
                                <p class="break-words font-bold text-slate-900">{{ $pmJob->pic ?: '-' }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    HM {{ number_format((float) $pmJob->hour_meter, 0, ',', '.') }}
                                </p>
                                <div class="mt-2">
                                    <x-status-badge :status="$pmJob->status_unit" size="xs" />
                                </div>
                            @else
                                <p class="text-xs font-semibold text-slate-400">-</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex flex-col items-end gap-2">
                                <a href="{{ route('assets.show', $asset->id) }}"
                                    class="inline-flex w-full justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">
                                    Detail Unit
                                </a>
                                @if($pmJob)
                                <a href="{{ route('update-jobs.show', $pmJob->id) }}"
                                    class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white hover:bg-blue-700">
                                    Detail PM
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <p class="font-black text-slate-900">Data tidak ditemukan</p>
                            <p class="mt-1 text-sm text-slate-500">Tidak ada unit yang cocok dengan filter ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 xl:hidden">
            @forelse($assets as $asset)
            @php
                $pmJob = $pmJobsBySerial->get($asset->serial_number);
                $assetStatus = strtoupper(trim((string) ($asset->status ?? '')));
                $statusClass = $assetStatus === 'RENTAL'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                    : ($assetStatus === 'BACKUP' || $assetStatus === 'STANDBY'
                        ? 'bg-amber-50 text-amber-700 border-amber-100'
                        : 'bg-slate-50 text-slate-700 border-slate-200');
            @endphp
            <article class="p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="break-words text-base font-black text-slate-950">{{ $asset->serial_number ?: '-' }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-400">#{{ $asset->id }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black {{ $statusClass }}">
                            {{ $asset->status ?: '-' }}
                        </span>
                        @if($pmJob)
                            <x-status-badge status="Sudah PM" />
                        @else
                            <x-status-badge status="Belum PM" />
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Customer / Lokasi</p>
                        <p class="mt-1 break-words text-sm font-bold text-slate-900">{{ $asset->customer ?: '-' }}</p>
                        <p class="mt-1 break-words text-xs font-semibold text-slate-500">{{ $asset->location ?: '-' }}</p>
                        <p class="mt-1 break-words text-[11px] font-semibold text-slate-400">{{ $asset->branch ?: '-' }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Unit</p>
                        <p class="mt-1 break-words text-sm font-bold text-slate-900">{{ $asset->unit_type ?: '-' }}</p>
                        <p class="mt-1 break-words text-xs font-semibold text-slate-500">{{ $asset->jenis_unit ?: '-' }}</p>
                        <p class="mt-1 text-[11px] font-semibold text-slate-400">Year {{ $asset->year ?: '-' }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">PM Bulan Ini</p>
                        @if($pmJob)
                            <x-status-badge status="Sudah PM" />
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $pmJob->work_date ? $pmJob->work_date->format('d/m/Y') : '-' }}</p>
                            <p class="mt-1 break-words text-[11px] font-semibold text-slate-400">{{ $pmJob->job_type ?: 'Preventive Maintenance' }}</p>
                        @else
                            <x-status-badge status="Belum PM" />
                            <p class="mt-1 text-xs font-semibold text-slate-500">Belum ada PM bulan ini</p>
                        @endif
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">PIC / HM</p>
                        @if($pmJob)
                            <p class="mt-1 break-words text-sm font-bold text-slate-900">{{ $pmJob->pic ?: '-' }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">HM {{ number_format((float) $pmJob->hour_meter, 0, ',', '.') }}</p>
                            <div class="mt-2">
                                <x-status-badge :status="$pmJob->status_unit" size="xs" />
                            </div>
                        @else
                            <p class="mt-1 text-sm font-semibold text-slate-400">-</p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <a href="{{ route('assets.show', $asset->id) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">
                        Detail Unit
                    </a>
                    @if($pmJob)
                    <a href="{{ route('update-jobs.show', $pmJob->id) }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700">
                        Detail PM
                    </a>
                    @endif
                </div>
            </article>
            @empty
            <div class="px-4 py-12 text-center">
                <p class="font-black text-slate-900">Data tidak ditemukan</p>
                <p class="mt-1 text-sm text-slate-500">Tidak ada unit yang cocok dengan filter ini.</p>
            </div>
            @endforelse
        </div>

        @if($assets->hasPages())
        <div class="border-t border-slate-100 px-4 py-4">
            {{ $assets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
