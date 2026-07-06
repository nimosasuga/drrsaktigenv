@extends('layouts.app')

@section('content')
@php
    $statusClass = function ($status, $expiredAt = null) {
        if ($status === 'active' && $expiredAt && $expiredAt->isFuture()) {
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }

        return match ($status) {
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
            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-500">Super Admin</p>
            <h1 class="mt-2 text-2xl font-black text-slate-950">Lisensi Bulk</h1>
            <p class="mt-1 max-w-2xl text-sm font-medium leading-6 text-slate-500">
                Pilih banyak user, lihat preview perubahan, lalu simpan dengan audit log lengkap.
            </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('admin.license-bulk.users.export', request()->query()) }}"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700">
                Export User + Lisensi
            </a>
            <a href="{{ route('admin.subscriptions') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                Verifikasi Pembayaran
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ route('admin.license-bulk.index') }}"
        class="grid grid-cols-1 gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-6 xl:items-end">
        <div class="xl:col-span-2">
            <label class="text-xs font-black uppercase tracking-wide text-slate-500">Cari User</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama atau NRPP"
                class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
        </div>
        <div>
            <label class="text-xs font-black uppercase tracking-wide text-slate-500">Role</label>
            <select name="role" class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
                <option value="">Semua</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ strtoupper($role) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-black uppercase tracking-wide text-slate-500">Department</label>
            <select name="department" class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
                <option value="">Semua</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" @selected(($filters['department'] ?? '') === $department)>{{ $department }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-black uppercase tracking-wide text-slate-500">Branch</label>
            <select name="branch" class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
                <option value="">Semua</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch }}" @selected(($filters['branch'] ?? '') === $branch)>{{ $branch }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-black uppercase tracking-wide text-slate-500">Status Lisensi</label>
            <select name="license_status" class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
                <option value="">Semua</option>
                <option value="active" @selected(($filters['license_status'] ?? '') === 'active')>Aktif</option>
                <option value="expired" @selected(($filters['license_status'] ?? '') === 'expired')>Expired</option>
                <option value="pending" @selected(($filters['license_status'] ?? '') === 'pending')>Pending</option>
                <option value="cancelled" @selected(($filters['license_status'] ?? '') === 'cancelled')>Cancelled</option>
                <option value="none" @selected(($filters['license_status'] ?? '') === 'none')>Belum Ada</option>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-2 xl:col-span-6">
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white">Filter</button>
            <a href="{{ route('admin.license-bulk.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-center text-sm font-black text-slate-700">Reset</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.license-bulk.preview') }}" id="licenseBulkForm"
        class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        @csrf
        <div class="grid grid-cols-1 gap-4 border-b border-slate-100 p-4 lg:grid-cols-5 lg:items-end">
            <div>
                <label class="text-xs font-black uppercase tracking-wide text-slate-500">Aksi</label>
                <select name="action" class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold" required>
                    @foreach($actions as $value => $label)
                        <option value="{{ $value }}" @selected(old('action') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-wide text-slate-500">Paket</label>
                <select name="subscription_package_id" class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
                    <option value="">Pilih paket bila dibutuhkan</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" @selected(old('subscription_package_id') == $package->id)>
                            {{ strtoupper($package->role_name) }} - {{ $package->package_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-wide text-slate-500">Durasi Bulan</label>
                <input type="number" name="duration_months" min="1" max="120" value="{{ old('duration_months') }}"
                    placeholder="Ikuti paket"
                    class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-wide text-slate-500">Expired Manual</label>
                <input type="date" name="expired_at" value="{{ old('expired_at') }}"
                    class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-wide text-slate-500">Catatan</label>
                <input type="text" name="note" value="{{ old('note') }}" placeholder="Opsional"
                    class="mt-1 block w-full rounded-xl border-slate-300 text-sm font-semibold">
            </div>
        </div>

        <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-black text-slate-900">Daftar User</p>
                <p class="text-xs font-bold text-slate-500">Centang user yang akan diproses. Super Admin tidak ditampilkan.</p>
            </div>
            <button type="submit"
                class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700">
                Preview Perubahan
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="w-12 px-4 py-3">
                            <input type="checkbox" id="checkAllUsers" class="rounded border-slate-300">
                        </th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3">Paket</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Expired</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        @php
                            $subscription = $user->latestSubscription;
                            $status = $subscription?->status ?? 'belum_ada';
                            if ($status === 'active' && $subscription?->expired_at && $subscription->expired_at->isPast()) {
                                $status = 'expired';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                    class="license-user-checkbox rounded border-slate-300">
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-black text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs font-semibold text-slate-500">NRPP: {{ $user->nrpp }}</p>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-700">{{ strtoupper($user->status_user) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->department ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $subscription?->package?->package_name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black {{ $statusClass($status, $subscription?->expired_at) }}">
                                    {{ strtoupper(str_replace('_', ' ', $status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-700">
                                {{ $subscription?->expired_at?->format('d M Y') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm font-bold text-slate-500">
                                Tidak ada user sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            {{ $users->links() }}
        </div>
    </form>

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-black text-slate-900">Audit Bulk Terakhir</p>
                <p class="text-xs font-bold text-slate-500">Riwayat tindakan Super Admin terbaru.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Paket</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Admin</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-700">{{ $batch->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 font-black text-slate-900">{{ $actions[$batch->action] ?? strtoupper($batch->action) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $batch->package?->package_name ?? '-' }}</td>
                            <td class="px-4 py-3 font-bold text-slate-700">{{ $batch->processed_users }}/{{ $batch->total_users }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $batch->creator?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $batch->note ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.license-bulk.show', $batch) }}"
                                        class="inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 hover:bg-blue-100">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.license-bulk.export', $batch) }}"
                                        class="inline-flex rounded-xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700 hover:bg-emerald-100">
                                        CSV
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm font-bold text-slate-500">Belum ada audit bulk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('checkAllUsers')?.addEventListener('change', function () {
        document.querySelectorAll('.license-user-checkbox').forEach((checkbox) => {
            checkbox.checked = this.checked;
        });
    });
</script>
@endsection
