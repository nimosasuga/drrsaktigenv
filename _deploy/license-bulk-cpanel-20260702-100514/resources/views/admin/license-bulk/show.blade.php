@extends('layouts.app')

@section('content')
@php
    $statusClass = function ($status) {
        return match ($status) {
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'cancelled' => 'bg-slate-100 text-slate-700 border-slate-200',
            'expired' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    };
@endphp

<div class="space-y-6 pb-24">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-500">Audit Lisensi Bulk</p>
            <h1 class="mt-2 text-2xl font-black text-slate-950">Batch #{{ $batch->id }} - {{ $actionLabel }}</h1>
            <p class="mt-1 max-w-2xl text-sm font-medium leading-6 text-slate-500">
                Detail perubahan lisensi per user, termasuk kondisi sebelum dan sesudah diproses.
            </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('admin.license-bulk.export', $batch) }}"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700">
                Export CSV
            </a>
            <a href="{{ route('admin.license-bulk.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                Kembali ke Lisensi Bulk
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Waktu Proses</p>
            <p class="mt-2 text-base font-black text-slate-950">{{ $batch->created_at?->format('d M Y H:i') ?? '-' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Admin</p>
            <p class="mt-2 text-base font-black text-slate-950">{{ $batch->creator?->name ?? '-' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Paket</p>
            <p class="mt-2 text-base font-black text-slate-950">{{ $batch->package?->package_name ?? '-' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">User Diproses</p>
            <p class="mt-2 text-3xl font-black text-slate-950">{{ $batch->processed_users }}/{{ $batch->total_users }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Status Batch</p>
            <p class="mt-2 text-base font-black text-slate-950">{{ strtoupper($batch->status) }}</p>
        </div>
    </div>

    @if($batch->note)
        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-4">
            <p class="text-xs font-black uppercase tracking-wide text-blue-700">Catatan Admin</p>
            <p class="mt-2 text-sm font-bold leading-6 text-blue-950">{{ $batch->note }}</p>
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-4">
            <p class="text-sm font-black text-slate-900">Detail User</p>
            <p class="text-xs font-bold text-slate-500">Tabel ini adalah bukti perubahan lisensi yang dibuat oleh batch ini.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1160px] w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Status Sebelum</th>
                        <th class="px-4 py-3">Paket Sebelum</th>
                        <th class="px-4 py-3">Expired Sebelum</th>
                        <th class="px-4 py-3">Status Baru</th>
                        <th class="px-4 py-3">Paket Baru</th>
                        <th class="px-4 py-3">Expired Baru</th>
                        <th class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-black text-slate-900">{{ $item->user?->name ?? 'User terhapus' }}</p>
                                <p class="text-xs font-semibold text-slate-500">NRPP: {{ $item->user?->nrpp ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 font-black text-slate-900">{{ strtoupper(str_replace('_', ' ', $item->action)) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black {{ $statusClass($item->previous_status) }}">
                                    {{ strtoupper(str_replace('_', ' ', $item->previous_status ?? '-')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->previousPackage?->package_name ?? '-' }}</td>
                            <td class="px-4 py-3 font-bold text-slate-700">{{ $item->previous_expired_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black {{ $statusClass($item->new_status) }}">
                                    {{ strtoupper(str_replace('_', ' ', $item->new_status ?? '-')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->newPackage?->package_name ?? '-' }}</td>
                            <td class="px-4 py-3 font-black text-slate-950">{{ $item->new_expired_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm font-bold text-slate-500">
                                Belum ada item audit pada batch ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
