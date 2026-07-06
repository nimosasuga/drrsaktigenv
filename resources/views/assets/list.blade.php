<!-- resources/views/assets/list.blade.php -->
@extends('layouts.app')

@section('content')
@php
$user = Auth::user();

$assetManageRoles = ['super_admin', 'admin', 'koordinator', 'sect_head'];

$canManageAsset = in_array(strtolower((string) ($user->role ?? '')), $assetManageRoles, true)
|| in_array(strtolower((string) ($user->status_user ?? '')), $assetManageRoles, true);

$statusBadgeClass = function ($status) {
    return match (strtoupper((string) $status)) {
        'ACTIVE' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'INACTIVE' => 'bg-rose-50 text-rose-700 border-rose-100',
        'RENTAL' => 'bg-blue-50 text-blue-700 border-blue-100',
        'BACKUP' => 'bg-amber-50 text-amber-700 border-amber-100',
        'DITARIK' => 'bg-rose-50 text-rose-700 border-rose-100',
        default => 'bg-slate-50 text-slate-700 border-slate-100',
    };
};
@endphp

<div class="mx-auto max-w-7xl space-y-6 pb-24">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <a href="{{ route('assets.index') }}"
                class="mb-4 inline-flex items-center text-sm font-bold text-blue-600 transition-colors hover:text-blue-800">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Lokasi
            </a>

            <p class="text-xs font-black uppercase tracking-wide text-blue-600">Detail Customer</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">{{ $customer }}</h1>
            <p class="mt-2 flex items-center text-sm font-semibold text-slate-500">
                <svg class="mr-1.5 h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Site: {{ $location }}
            </p>
        </div>

        <div class="inline-flex w-max rounded-2xl bg-slate-950 px-4 py-2 text-sm font-black text-white shadow-sm">
            Total {{ $assets->total() }} Unit
        </div>
    </div>

    <form method="GET" action="{{ route('assets.index') }}"
        class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="location" value="{{ $location }}">
        <input type="hidden" name="customer" value="{{ $customer }}">

        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_auto] lg:items-end">
            <div>
                <label for="search" class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">
                    Cari Unit
                </label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Serial number, tipe, status, branch, delivery..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label for="filter_status" class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">
                    Status
                </label>
                <select name="filter_status" id="filter_status"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('filter_status') === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="inline-flex flex-1 items-center justify-center rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-blue-700 lg:flex-none">
                    Terapkan
                </button>
                @if(request()->filled('search') || request()->filled('filter_status'))
                <a href="{{ route('assets.index', ['location' => $location, 'customer' => $customer]) }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                    Reset
                </a>
                @endif
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="hidden overflow-x-auto xl:block">
            <table class="min-w-[1100px] w-full table-fixed divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="w-[135px] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Serial Number</th>
                        <th class="w-[130px] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Type</th>
                        <th class="w-[110px] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Jenis</th>
                        <th class="w-[75px] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Tahun</th>
                        <th class="w-[105px] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Status</th>
                        <th class="w-[90px] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Branch</th>
                        <th class="w-[110px] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Delivery</th>
                        <th class="w-[165px] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Supported</th>
                        <th class="w-[180px] px-4 py-3 text-right text-[11px] font-black uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($assets as $asset)
                    <tr class="align-top transition hover:bg-blue-50/40">
                        <td class="px-4 py-4">
                            <p class="break-words text-sm font-black text-slate-950">{{ $asset->serial_number ?? '-' }}</p>
                            <p class="mt-1 break-words text-xs font-semibold text-slate-500">{{ $asset->nomor_lambung ?? $asset->qr_token ?? '-' }}</p>
                        </td>
                        <td class="wrap-break-word px-4 py-4 text-sm font-semibold text-slate-700">{{ $asset->unit_type ?? $asset->unit_model ?? $asset->tipe_unit ?? '-' }}</td>
                        <td class="wrap-break-word px-4 py-4 text-sm font-semibold text-slate-700">{{ $asset->jenis_unit ?? '-' }}</td>
                        <td class="px-4 py-4 text-sm font-semibold text-slate-700">{{ $asset->year ?? $asset->tahun ?? '-' }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black {{ $statusBadgeClass($asset->status) }}">
                                {{ $asset->status ?? '-' }}
                            </span>
                        </td>
                        <td class="wrap-break-word px-4 py-4 text-sm font-semibold text-slate-700">{{ $asset->branch ?? '-' }}</td>
                        <td class="wrap-break-word px-4 py-4 text-sm font-semibold text-slate-700">{{ $asset->delivery ?? '-' }}</td>
                        <td class="wrap-break-word px-4 py-4 text-sm font-semibold text-slate-700">{{ $asset->supported_by ?? '-' }}</td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('assets.show', $asset->id) }}"
                                    class="inline-flex w-24 items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-center text-xs font-black text-white transition hover:bg-blue-700">
                                    Detail Histori
                                </a>
                                @if($canManageAsset)
                                <a href="{{ route('assets.edit', $asset->id) }}"
                                    class="inline-flex w-16 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50">
                                    Edit
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">
                            Tidak ada unit ditemukan untuk lokasi dan customer ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 xl:hidden">
            @forelse($assets as $asset)
            <div class="p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Serial Number</p>
                        <h2 class="mt-1 break-words text-base font-black text-slate-950">{{ $asset->serial_number ?? '-' }}</h2>
                    </div>
                    <span class="w-max rounded-full border px-2.5 py-1 text-[11px] font-black {{ $statusBadgeClass($asset->status) }}">
                        {{ $asset->status ?? '-' }}
                    </span>
                </div>

                <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Type</dt>
                        <dd class="mt-1 break-words text-sm font-bold text-slate-800">{{ $asset->unit_type ?? $asset->unit_model ?? $asset->tipe_unit ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Jenis</dt>
                        <dd class="mt-1 break-words text-sm font-bold text-slate-800">{{ $asset->jenis_unit ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Tahun</dt>
                        <dd class="mt-1 break-words text-sm font-bold text-slate-800">{{ $asset->year ?? $asset->tahun ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Branch</dt>
                        <dd class="mt-1 break-words text-sm font-bold text-slate-800">{{ $asset->branch ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Delivery</dt>
                        <dd class="mt-1 break-words text-sm font-bold text-slate-800">{{ $asset->delivery ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Supported</dt>
                        <dd class="mt-1 break-words text-sm font-bold text-slate-800">{{ $asset->supported_by ?? '-' }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('assets.show', $asset->id) }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-blue-700">
                        Detail Histori Unit
                    </a>
                    @if($canManageAsset)
                    <a href="{{ route('assets.edit', $asset->id) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                        Edit Asset
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-sm font-semibold text-slate-500">
                Tidak ada unit ditemukan untuk lokasi dan customer ini.
            </div>
            @endforelse
        </div>
    </div>

    <div>
        {{ $assets->links() }}
    </div>
</div>
@endsection
