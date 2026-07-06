<!-- PATH FILE: resources/views/assets/show.blade.php -->
@extends('layouts.app')

@section('content')
@php
$user = Auth::user();

$assetManageRoles = ['super_admin', 'admin', 'koordinator', 'sect_head'];

$canManageAsset = in_array(strtolower((string) ($user->role ?? '')), $assetManageRoles, true)
|| in_array(strtolower((string) ($user->status_user ?? '')), $assetManageRoles, true);

$statusClass = match ($asset->status) {
'ACTIVE' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
'INACTIVE' => 'bg-rose-50 text-rose-700 border-rose-100',
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

$moduleBadgeClass = function ($module) {
return match ($module) {
'Update Job' => 'bg-blue-50 text-blue-700 border-blue-100',
'Battery' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
'Charger' => 'bg-amber-50 text-amber-700 border-amber-100',
'Delivery' => 'bg-purple-50 text-purple-700 border-purple-100',
default => 'bg-slate-50 text-slate-700 border-slate-100',
};
};

$timelineTypeClass = function ($type) {
return match ($type) {
'pm' => 'border-cyan-100 bg-cyan-50 text-cyan-700',
'recommendation' => 'border-purple-100 bg-purple-50 text-purple-700',
'install_part' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
default => 'border-blue-100 bg-blue-50 text-blue-700',
};
};

$timelineTypeLabel = function ($type) {
return match ($type) {
'pm' => 'Histori PM',
'recommendation' => 'Rekomendasi',
'install_part' => 'Pemasangan',
default => 'Histori Job',
};
};
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
            <div
                class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Semua Kolom Unit Asset
                    </h2>
                    <p class="text-sm text-slate-500">
                        Mengikuti struktur: id, supported_by, customer, location, branch, serial_number, unit_type,
                        year, status, delivery, jenis_unit, note, qr_token, created_at, updated_at.
                    </p>
                </div>

                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">
                    {{ $asset->status ?? '-' }}
                </span>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <div class="hidden md:block">
                    <table class="min-w-full table-fixed divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="w-55 px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">
                                    Field</th>
                                <th
                                    class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">
                                    Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($assetFields as $field => $value)
                            <tr class="align-top transition hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-sm font-black text-slate-600">{{ str_replace('_', ' ', $field)
                                    }}</td>
                                <td class="px-4 py-3">
                                    @if($field === 'status')
                                    <span
                                        class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                        {{ filled($value) ? $value : '-' }}
                                    </span>
                                    @else
                                    <p class="wrap-break-word whitespace-pre-line text-sm font-semibold text-slate-900">
                                        {{ filled($value) ? $value : '-' }}</p>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach($assetFields as $field => $value)
                    <div class="bg-white px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">{{ str_replace('_', '
                            ', $field) }}</p>
                        @if($field === 'status')
                        <span
                            class="mt-1 inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                            {{ filled($value) ? $value : '-' }}
                        </span>
                        @else
                        <p class="mt-1 wrap-break-word whitespace-pre-line text-sm font-bold text-slate-900">{{
                            filled($value) ? $value : '-' }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:p-7">
            <h2 class="text-lg font-bold text-slate-900">
                Ringkasan Asset
            </h2>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                    <span class="text-sm text-slate-500">Status</span>
                    <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ $asset->status
                        ?? '-' }}</span>
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

    <div class="grid gap-4 lg:grid-cols-[360px_minmax(0,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Status Terakhir Unit</p>
            <div class="mt-4 space-y-3">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Status Asset</p>
                    <div class="mt-2">
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                            {{ $asset->status ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Status Job Terakhir</p>
                    @if($latestJob)
                        <div class="mt-2">
                            <x-status-badge :status="$latestJob->status_unit" />
                        </div>
                        <p class="mt-2 wrap-break-word text-sm font-black text-slate-900">{{ $latestJob->job_type ?: 'Update Job' }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">
                            {{ $latestJob->work_date ? \Carbon\Carbon::parse($latestJob->work_date)->translatedFormat('d M Y') : '-' }}
                            · {{ $latestJob->pic ?: 'Tanpa PIC' }}
                        </p>
                    @else
                        <p class="mt-2 text-sm font-bold text-slate-500">Belum ada histori job.</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-blue-500">Job</p>
                        <p class="mt-1 text-2xl font-black text-blue-800">{{ number_format($timelineStats['job_total'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-cyan-500">PM</p>
                        <p class="mt-1 text-2xl font-black text-cyan-800">{{ number_format($timelineStats['pm_total'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-2xl border border-purple-100 bg-purple-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-purple-500">Rekom</p>
                        <p class="mt-1 text-2xl font-black text-purple-800">{{ number_format($timelineStats['recommendation_total'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-emerald-500">Terpasang</p>
                        <p class="mt-1 text-2xl font-black text-emerald-800">{{ number_format($timelineStats['install_part_total'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Timeline Unit</h2>
                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Job, PM, rekomendasi sparepart, dan pemasangan sparepart dalam satu alur.
                    </p>
                </div>
                <span class="w-max rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600">
                    {{ number_format($unitTimeline->count(), 0, ',', '.') }} event
                </span>
            </div>

            <div class="mt-5 space-y-4">
                @forelse($unitTimeline->take(12) as $event)
                    @php
                    $eventDate = filled($event['date'] ?? null)
                    ? \Carbon\Carbon::parse($event['date'])->translatedFormat('d M Y')
                    : '-';
                    @endphp
                    <article class="relative pl-7">
                        <span class="absolute left-2 top-2 h-full w-px bg-slate-200"></span>
                        <span class="absolute left-0 top-1.5 h-4 w-4 rounded-full border-4 border-white shadow-sm {{ $timelineTypeClass($event['type'] ?? 'job') }}"></span>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border px-2.5 py-1 text-[10px] font-black {{ $timelineTypeClass($event['type'] ?? 'job') }}">
                                            {{ $timelineTypeLabel($event['type'] ?? 'job') }}
                                        </span>
                                        <span class="text-[11px] font-bold text-slate-400">{{ $eventDate }}</span>
                                    </div>
                                    <h3 class="mt-2 wrap-break-word text-sm font-black text-slate-950">{{ $event['title'] ?? '-' }}</h3>
                                    <p class="mt-1 wrap-break-word text-xs font-semibold text-slate-500">{{ $event['subtitle'] ?? '-' }}</p>
                                </div>
                                @if(!empty($event['status']) && in_array($event['type'] ?? 'job', ['job', 'pm'], true))
                                    <x-status-badge :status="$event['status']" size="xs" />
                                @endif
                            </div>

                            <p class="mt-3 wrap-break-word whitespace-pre-line text-sm font-semibold leading-relaxed text-slate-700">
                                {{ $event['description'] ?? '-' }}
                            </p>

                            @if(!empty($event['route']))
                                <a href="{{ $event['route'] }}"
                                    class="mt-3 inline-flex rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white hover:bg-blue-700">
                                    Lihat Detail
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center">
                        <p class="text-sm font-black text-slate-800">Timeline unit belum tersedia.</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Belum ada job, PM, rekomendasi, atau pemasangan sparepart untuk S/N ini.</p>
                    </div>
                @endforelse
            </div>

            @if($unitTimeline->count() > 12)
                <p class="mt-4 text-xs font-bold text-slate-500">
                    Menampilkan 12 event terbaru. Detail lengkap tetap tersedia di tabel histori unit di bawah.
                </p>
            @endif
        </section>
    </div>

    {{-- Histori Unit --}}
    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
        <div
            class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                    Tabel Histori Unit
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Riwayat gabungan berdasarkan Serial Number: {{ $asset->serial_number ?? '-' }}
                </p>
            </div>

            <span
                class="inline-flex w-max items-center rounded-xl border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                {{ $timeline->count() }} Histori
            </span>
        </div>

        <div class="hidden xl:block">
            <table class="min-w-full table-fixed divide-y divide-slate-100">
                <thead class="bg-white">
                    <tr>
                        <th
                            class="w-[12%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">
                            Tanggal</th>
                        <th
                            class="w-[12%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">
                            Module</th>
                        <th
                            class="w-[18%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">
                            Pekerjaan</th>
                        <th
                            class="w-[14%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">
                            PIC</th>
                        <th
                            class="w-[32%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">
                            Deskripsi</th>
                        <th
                            class="w-[12%] px-4 py-3 text-right text-[11px] font-black uppercase tracking-wide text-slate-500">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($timeline as $item)
                    @php
                    $historyDate = filled($item['date'] ?? null)
                    ? \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y')
                    : '-';
                    @endphp
                    <tr class="align-top transition hover:bg-blue-50/40">
                        <td class="px-4 py-4 text-sm font-black text-slate-900">{{ $historyDate }}</td>
                        <td class="px-4 py-4">
                            <span
                                class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black {{ $moduleBadgeClass($item['module'] ?? '') }}">
                                {{ $item['module'] ?? '-' }}
                            </span>
                        </td>
                        <td class="wrap-break-word px-4 py-4 text-sm font-bold text-slate-800">{{ $item['title'] ?? '-'
                            }}</td>
                        <td class="wrap-break-word px-4 py-4 text-sm font-semibold text-slate-700">{{ $item['pic'] ??
                            '-' }}</td>
                        <td class="px-4 py-4">
                            <p class="wrap-break-word whitespace-pre-line text-sm font-semibold text-slate-700">{{
                                $item['desc'] ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4 text-right">
                            @if(!empty($item['route']))
                            <a href="{{ $item['route'] }}"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white transition-colors hover:bg-blue-700">
                                Lihat Detail
                            </a>
                            @else
                            <span class="text-xs font-semibold text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center">
                            <p class="text-sm font-bold text-slate-700">Histori unit belum tersedia.</p>
                            <p class="mt-1 text-xs text-slate-500">Belum ada aktivitas yang tercatat untuk serial number
                                ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 xl:hidden">
            @forelse($timeline as $item)
            @php
            $historyDate = filled($item['date'] ?? null)
            ? \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y')
            : '-';
            @endphp
            <div class="p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">{{ $historyDate }}</p>
                        <h3 class="mt-1 wrap-break-word text-sm font-black text-slate-950">{{ $item['title'] ?? '-' }}
                        </h3>
                    </div>
                    <span
                        class="w-max rounded-full border px-2.5 py-1 text-[11px] font-black {{ $moduleBadgeClass($item['module'] ?? '') }}">
                        {{ $item['module'] ?? '-' }}
                    </span>
                </div>

                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">PIC</dt>
                        <dd class="mt-1 wrap-break-word text-sm font-bold text-slate-800">{{ $item['pic'] ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 sm:col-span-2">
                        <dt class="text-[11px] font-black uppercase tracking-wide text-slate-400">Deskripsi</dt>
                        <dd class="mt-1 wrap-break-word whitespace-pre-line text-sm font-bold text-slate-800">{{
                            $item['desc'] ?? '-' }}</dd>
                    </div>
                </dl>

                @if(!empty($item['route']))
                <div class="mt-4 flex justify-end">
                    <a href="{{ $item['route'] }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white transition-colors hover:bg-blue-700">
                        Lihat Detail
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="p-8 text-center">
                <p class="text-sm font-bold text-slate-700">Histori unit belum tersedia.</p>
                <p class="mt-1 text-xs text-slate-500">Belum ada aktivitas yang tercatat untuk serial number ini.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
