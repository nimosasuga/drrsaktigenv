@extends('layouts.app')

@section('content')
<div class="space-y-6 pb-24">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-500">Preview Bulk Lisensi</p>
            <h1 class="mt-2 text-2xl font-black text-slate-950">{{ $actionLabel }}</h1>
            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">
                Periksa perubahan sebelum disimpan. Setelah konfirmasi, sistem akan membuat audit log per user.
            </p>
        </div>
        <a href="{{ route('admin.license-bulk.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Total User</p>
            <p class="mt-2 text-3xl font-black text-slate-950">{{ $users->count() }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Aksi</p>
            <p class="mt-2 text-base font-black text-slate-950">{{ $actionLabel }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Paket</p>
            <p class="mt-2 text-base font-black text-slate-950">{{ $package?->package_name ?? '-' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Catatan</p>
            <p class="mt-2 text-sm font-bold text-slate-700">{{ $payload['note'] ?? '-' }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-4">
            <p class="text-sm font-black text-slate-900">Perubahan Lisensi</p>
            <p class="text-xs font-bold text-slate-500">Kolom kiri adalah kondisi sekarang, kolom kanan adalah hasil setelah konfirmasi.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[1040px] w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Status Sekarang</th>
                        <th class="px-4 py-3">Paket Sekarang</th>
                        <th class="px-4 py-3">Expired Sekarang</th>
                        <th class="px-4 py-3">Status Baru</th>
                        <th class="px-4 py-3">Paket Baru</th>
                        <th class="px-4 py-3">Expired Baru</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows as $row)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-black text-slate-900">{{ $row['user']->name }}</p>
                                <p class="text-xs font-semibold text-slate-500">NRPP: {{ $row['user']->nrpp }}</p>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-700">{{ strtoupper(str_replace('_', ' ', $row['before']['status'] ?? '-')) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $row['before']['package_name'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $row['before']['expired_at']?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 font-black text-blue-700">{{ strtoupper(str_replace('_', ' ', $row['after']['status'] ?? '-')) }}</td>
                            <td class="px-4 py-3 font-bold text-slate-800">{{ $row['after']['package_name'] ?? '-' }}</td>
                            <td class="px-4 py-3 font-black text-slate-950">{{ $row['after']['expired_at']?->format('d M Y') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.license-bulk.confirm') }}"
        class="flex flex-col gap-3 rounded-3xl border border-blue-200 bg-blue-50 p-4 sm:flex-row sm:items-center sm:justify-between">
        @csrf
        <input type="hidden" name="action" value="{{ $payload['action'] }}">
        <input type="hidden" name="subscription_package_id" value="{{ $payload['subscription_package_id'] ?? '' }}">
        <input type="hidden" name="duration_months" value="{{ $payload['duration_months'] ?? '' }}">
        <input type="hidden" name="expired_at" value="{{ $payload['expired_at'] ?? '' }}">
        <input type="hidden" name="note" value="{{ $payload['note'] ?? '' }}">
        @foreach($payload['user_ids'] as $userId)
            <input type="hidden" name="user_ids[]" value="{{ $userId }}">
        @endforeach

        <div>
            <p class="text-sm font-black text-blue-950">Simpan perubahan ini?</p>
            <p class="text-xs font-bold text-blue-700">Audit batch dan detail per user akan dibuat otomatis.</p>
        </div>
        <button type="submit"
            onclick="return confirm('Proses bulk lisensi untuk {{ $users->count() }} user?')"
            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700">
            Konfirmasi & Simpan
        </button>
    </form>
</div>
@endsection
