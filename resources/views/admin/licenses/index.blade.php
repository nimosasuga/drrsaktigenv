<!--
|--------------------------------------------------------------------------
| PATH FILE:
| resources/views/admin/licenses/index.blade.php
|--------------------------------------------------------------------------
-->
@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kontrol Lisensi</h1>
        <p class="mt-1 text-sm text-slate-500">
            Semua user tampil di sini, termasuk yang belum punya lisensi. Bulk action bisa membuat lisensi dan mencatat pembayaran paid secara massal.
        </p>
    </div>

    <a href="{{ route('admin.licenses.index') }}"
        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        Reset Filter
    </a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-r-md border-l-4 border-emerald-500 bg-emerald-50 p-4 shadow-sm">
        <p class="text-sm font-bold text-emerald-700">{{ session('success') }}</p>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 rounded-r-md border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
        <p class="text-sm font-bold text-red-700">Gagal memproses lisensi</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total User</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $summary['users_total'] ?? 0 }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Lisensi</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $summary['total'] ?? 0 }}</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm ring-1 ring-emerald-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Aktif</p>
        <p class="mt-2 text-3xl font-extrabold text-emerald-900">{{ $summary['active'] ?? 0 }}</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm ring-1 ring-amber-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Pending</p>
        <p class="mt-2 text-3xl font-extrabold text-amber-900">{{ $summary['pending'] ?? 0 }}</p>
    </div>
    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm ring-1 ring-rose-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Expired</p>
        <p class="mt-2 text-3xl font-extrabold text-rose-900">{{ $summary['expired'] ?? 0 }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-slate-100 p-5 shadow-sm ring-1 ring-slate-900/5">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-600">Belum Ada</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $summary['none'] ?? 0 }}</p>
    </div>
</div>

<div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5 xl:col-span-1">
        <h2 class="text-base font-extrabold text-slate-900">Tambah Lisensi Manual</h2>
        <p class="mt-1 text-sm text-slate-500">Pilih pengguna terlebih dahulu. Paket akan mengikuti role/status user.</p>

        <form method="POST" action="{{ route('admin.licenses.store') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Pengguna</label>
                <select id="licenseUserSelect" name="user_id" required class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="" data-license-role="">Pilih pengguna</option>
                    @foreach($allUsers as $manualUser)
                        @php
                            $manualRole = strtolower(trim((string) ($manualUser->status_user ?? '')));
                            $manualRole = str_replace(['-', '_'], ' ', $manualRole);
                            $manualRole = preg_replace('/\s+/', ' ', $manualRole) ?: '';
                            if ($manualRole === 'sect head' || $manualRole === 'secthead') { $manualRole = 'sect_head'; }
                            elseif ($manualRole === 'super admin' || $manualRole === 'superadmin') { $manualRole = 'super_admin'; }
                            else { $manualRole = str_replace(' ', '_', $manualRole); }
                            $manualRoleLabel = strtoupper(str_replace('_', ' ', $manualRole));
                        @endphp
                        <option value="{{ $manualUser->id }}" data-license-role="{{ $manualRole }}" {{ old('user_id') == $manualUser->id ? 'selected' : '' }}>
                            {{ $manualUser->name }} · {{ $manualRoleLabel }} · NRPP {{ $manualUser->nrpp ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Paket sesuai role</label>
                <select id="licensePackageSelect" name="subscription_package_id" required class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="" data-package-role="">Pilih user dulu</option>
                    @foreach($packages as $package)
                        @php
                            $packageRole = strtolower(trim((string) ($package->role_name ?? '')));
                            $packageRole = str_replace(['-', '_'], ' ', $packageRole);
                            $packageRole = preg_replace('/\s+/', ' ', $packageRole) ?: '';
                            if ($packageRole === 'sect head' || $packageRole === 'secthead') { $packageRole = 'sect_head'; }
                            elseif ($packageRole === 'super admin' || $packageRole === 'superadmin') { $packageRole = 'super_admin'; }
                            else { $packageRole = str_replace(' ', '_', $packageRole); }
                            $packageRoleLabel = strtoupper(str_replace('_', ' ', $packageRole));
                        @endphp
                        <option value="{{ $package->id }}" data-package-role="{{ $packageRole }}" {{ old('subscription_package_id') == $package->id ? 'selected' : '' }}>
                            Lisensi {{ $packageRoleLabel }} · {{ (int) $package->duration_months }} bulan · Rp{{ number_format($package->price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                <p id="licensePackageHelp" class="mt-1 text-xs text-slate-500">Paket otomatis dibatasi sesuai role pengguna yang dipilih.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Status</label>
                    <select name="status" required class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach(['pending', 'active', 'expired', 'cancelled'] as $statusOption)
                            <option value="{{ $statusOption }}" {{ old('status', 'active') === $statusOption ? 'selected' : '' }}>{{ strtoupper($statusOption) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Mulai</label>
                    <input type="date" name="started_at" value="{{ old('started_at', now()->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Expired</label>
                <input type="date" name="expired_at" value="{{ old('expired_at') }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-xs text-slate-500">Kosongkan untuk auto hitung dari durasi paket saat status aktif.</p>
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                Tambah Lisensi
            </button>
        </form>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5 xl:col-span-2">
        <h2 class="text-base font-extrabold text-slate-900">Filter & Bulk Action</h2>
        <form method="GET" action="{{ route('admin.licenses.index') }}" class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NRPP, branch, paket..." class="rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500 lg:col-span-2">
            <select name="status" class="rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua status</option>
                <option value="none" {{ request('status') === 'none' ? 'selected' : '' }}>BELUM ADA LISENSI</option>
                @foreach(['pending', 'active', 'expired', 'cancelled'] as $statusOption)
                    <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>{{ strtoupper($statusOption) }}</option>
                @endforeach
            </select>
            <select name="package_id" class="rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua paket</option>
                @foreach($packages as $package)
                    @php
                        $filterPackageRole = strtolower(trim((string) ($package->role_name ?? '')));
                        $filterPackageRole = str_replace(['-', '_'], ' ', $filterPackageRole);
                        $filterPackageRole = preg_replace('/\s+/', ' ', $filterPackageRole) ?: '';
                        if ($filterPackageRole === 'sect head' || $filterPackageRole === 'secthead') { $filterPackageRole = 'sect_head'; }
                        elseif ($filterPackageRole === 'super admin' || $filterPackageRole === 'superadmin') { $filterPackageRole = 'super_admin'; }
                        else { $filterPackageRole = str_replace(' ', '_', $filterPackageRole); }
                        $filterPackageRoleLabel = strtoupper(str_replace('_', ' ', $filterPackageRole));
                    @endphp
                    <option value="{{ $package->id }}" {{ (string) request('package_id') === (string) $package->id ? 'selected' : '' }}>
                        Lisensi {{ $filterPackageRoleLabel }} · {{ (int) $package->duration_months }} bulan
                    </option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-800 lg:col-span-4">Terapkan Filter</button>
        </form>

        <form id="bulkLicenseForm" method="POST" action="{{ route('admin.licenses.bulk') }}" class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4" onsubmit="return confirm('Proses bulk action untuk user terpilih?');">
            @csrf
            <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Bulk Action User</label>
                    <select name="bulk_action" required class="w-full rounded-xl border-slate-300 bg-white text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih aksi</option>
                        <option value="activate_paid">Buat/Aktifkan Lisensi + Tandai Paid</option>
                        <option value="activate">Buat/Aktifkan Lisensi</option>
                        <option value="expire">Set Expired</option>
                        <option value="cancel">Cancel</option>
                        <option value="delete">Hapus Lisensi</option>
                    </select>
                </div>
                <button type="submit" class="rounded-xl bg-rose-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-700">Jalankan Bulk</button>
            </div>
            <div id="bulkHiddenInputs"></div>
            <p class="mt-2 text-xs text-slate-500">Centang user di tabel bawah. Untuk user tanpa lisensi, bulk activate akan membuat lisensi sesuai role user.</p>
        </form>
    </div>
</div>

<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-900/5">
    <div class="overflow-x-auto">
        <table class="min-w-[1180px] divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"><input id="checkAllLicenses" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"></th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Pengguna</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Paket</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Periode</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Payment</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($users as $user)
                    @php
                        $license = $licenseByUser->get($user->id);
                        $package = $license ? $license->package : null;
                        $payment = $license ? $license->payment : null;
                        $userRole = strtolower(trim((string) ($user->status_user ?? '')));
                        $userRole = str_replace(['-', '_'], ' ', $userRole);
                        $userRole = preg_replace('/\s+/', ' ', $userRole) ?: '';
                        if ($userRole === 'sect head' || $userRole === 'secthead') { $userRole = 'sect_head'; }
                        elseif ($userRole === 'super admin' || $userRole === 'superadmin') { $userRole = 'super_admin'; }
                        else { $userRole = str_replace(' ', '_', $userRole); }
                        $userRoleLabel = strtoupper(str_replace('_', ' ', $userRole));
                        $matchingPackages = [];
                        foreach ($packages as $packageOption) {
                            $optionRole = strtolower(trim((string) ($packageOption->role_name ?? '')));
                            $optionRole = str_replace(['-', '_'], ' ', $optionRole);
                            $optionRole = preg_replace('/\s+/', ' ', $optionRole) ?: '';
                            if ($optionRole === 'sect head' || $optionRole === 'secthead') { $optionRole = 'sect_head'; }
                            elseif ($optionRole === 'super admin' || $optionRole === 'superadmin') { $optionRole = 'super_admin'; }
                            else { $optionRole = str_replace(' ', '_', $optionRole); }
                            if ($optionRole === $userRole) { $matchingPackages[] = $packageOption; }
                        }
                        $displayStatus = 'none';
                        if ($license) {
                            $displayStatus = ($license->status === 'active' && $license->expired_at && $license->expired_at->isPast()) ? 'expired' : $license->status;
                        }
                        $badgeClass = 'bg-slate-100 text-slate-700';
                        if ($displayStatus === 'active') { $badgeClass = 'bg-emerald-100 text-emerald-700'; }
                        elseif ($displayStatus === 'pending') { $badgeClass = 'bg-amber-100 text-amber-700'; }
                        elseif ($displayStatus === 'expired') { $badgeClass = 'bg-rose-100 text-rose-700'; }
                        elseif ($displayStatus === 'cancelled') { $badgeClass = 'bg-slate-200 text-slate-700'; }
                        elseif ($displayStatus === 'none') { $badgeClass = 'bg-slate-100 text-slate-500'; }
                    @endphp
                    <tr class="align-top hover:bg-slate-50/60">
                        <td class="px-4 py-4"><input type="checkbox" value="{{ $user->id }}" class="license-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500"></td>
                        <td class="px-4 py-4">
                            <div class="font-bold text-slate-900">{{ $user->name ?? 'User tidak ditemukan' }}</div>
                            <div class="mt-1 text-xs text-slate-500">NRPP: {{ $user->nrpp ?? '-' }}</div>
                            <div class="mt-1 flex flex-wrap gap-1 text-[11px] font-bold uppercase">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">{{ $user->status_user ?? '-' }}</span>
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-blue-700">{{ $user->department ?? '-' }}</span>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">{{ $user->branch ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($package)
                                <div class="font-bold text-slate-900">{{ $package->package_name ?? '-' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ strtoupper((string) $package->role_name) }} · {{ (int) ($package->duration_months ?? 0) }} bulan</div>
                                <div class="mt-1 text-xs font-bold text-slate-700">Rp{{ number_format((int) ($package->price ?? 0), 0, ',', '.') }}</div>
                            @else
                                <div class="font-bold text-slate-500">Belum ada lisensi</div>
                                <div class="mt-1 text-xs text-slate-400">Role: {{ $userRoleLabel }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold uppercase {{ $badgeClass }}">{{ $displayStatus === 'none' ? 'BELUM ADA' : strtoupper($displayStatus) }}</span></td>
                        <td class="px-4 py-4 text-sm text-slate-700">
                            <div>Start: <strong>{{ $license?->started_at?->format('d M Y') ?? '-' }}</strong></div>
                            <div class="mt-1">Expired: <strong>{{ $license?->expired_at?->format('d M Y') ?? '-' }}</strong></div>
                            <div class="mt-1 text-xs text-slate-500">Update: {{ $license?->updated_at?->diffForHumans() ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-slate-700">
                            @if($payment)
                                <div class="font-bold">#{{ $payment->id }} · {{ strtoupper($payment->payment_status) }}</div>
                                <div class="mt-1 text-xs text-slate-500">Rp{{ number_format((int) $payment->amount, 0, ',', '.') }}</div>
                            @else
                                <span class="text-xs text-slate-400">Belum ada payment terkait</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="min-w-[300px] rounded-2xl border border-slate-200 bg-slate-50 p-3 text-left">
                                <p class="text-[11px] font-black uppercase tracking-wide text-slate-500">Aksi Lisensi</p>
                                <p class="mt-1 text-xs text-slate-500">Paket dibatasi untuk role: <strong>{{ $userRoleLabel }}</strong></p>
                                @if(count($matchingPackages) === 0)
                                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">Belum ada paket lisensi untuk role {{ $userRoleLabel }}.</div>
                                @else
                                    <form method="POST" action="{{ $license ? route('admin.licenses.update', $license) : route('admin.licenses.store') }}" class="mt-3 space-y-2">
                                        @csrf
                                        @if($license)
                                            @method('PATCH')
                                        @endif
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <div>
                                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-500">Paket Role</label>
                                            <select name="subscription_package_id" class="w-full rounded-lg border-slate-300 bg-white text-xs focus:border-blue-500 focus:ring-blue-500">
                                                @foreach($matchingPackages as $packageOption)
                                                    <option value="{{ $packageOption->id }}" {{ $license && $license->subscription_package_id === $packageOption->id ? 'selected' : '' }}>Lisensi {{ $userRoleLabel }} · {{ (int) $packageOption->duration_months }} bulan · Rp{{ number_format((int) $packageOption->price, 0, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                            <div><label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-500">Status</label><select name="status" class="w-full rounded-lg border-slate-300 bg-white text-xs focus:border-blue-500 focus:ring-blue-500">@foreach(['pending', 'active', 'expired', 'cancelled'] as $statusOption)<option value="{{ $statusOption }}" {{ $license && $license->status === $statusOption ? 'selected' : (!$license && $statusOption === 'active' ? 'selected' : '') }}>{{ strtoupper($statusOption) }}</option>@endforeach</select></div>
                                            <div><label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-500">Start</label><input type="date" name="started_at" value="{{ $license?->started_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 bg-white text-xs focus:border-blue-500 focus:ring-blue-500"></div>
                                            <div><label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-500">Expired</label><input type="date" name="expired_at" value="{{ $license?->expired_at?->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 bg-white text-xs focus:border-blue-500 focus:ring-blue-500"></div>
                                        </div>
                                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-blue-700">{{ $license ? 'Update Lisensi' : 'Buat Lisensi' }}</button>
                                    </form>
                                @endif
                                @if($license)
                                    <form method="POST" action="{{ route('admin.licenses.destroy', $license) }}" class="mt-2" onsubmit="return confirm('Hapus lisensi ini? Data payment tidak dihapus, hanya record lisensi.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded-lg bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">Hapus Lisensi</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-sm font-semibold text-slate-500">Belum ada user sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">{{ $users->links() }}</div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('checkAllLicenses');
        const bulkForm = document.getElementById('bulkLicenseForm');
        const hiddenTarget = document.getElementById('bulkHiddenInputs');
        const userSelect = document.getElementById('licenseUserSelect');
        const packageSelect = document.getElementById('licensePackageSelect');
        const packageHelp = document.getElementById('licensePackageHelp');

        function normalizeLicenseRole(role) {
            role = String(role || '').trim().toLowerCase().replace(/[-_]/g, ' ').replace(/\s+/g, ' ');
            if (role === 'sect head' || role === 'secthead') return 'sect_head';
            if (role === 'super admin' || role === 'superadmin') return 'super_admin';
            return role.replace(/\s+/g, '_');
        }

        function syncPackageOptionsByUserRole() {
            if (!userSelect || !packageSelect) return;
            const selectedUser = userSelect.options[userSelect.selectedIndex];
            const selectedRole = normalizeLicenseRole(selectedUser ? selectedUser.dataset.licenseRole : '');
            let visibleCount = 0;
            let firstVisibleValue = '';
            Array.from(packageSelect.options).forEach(function (option) {
                const packageRole = normalizeLicenseRole(option.dataset.packageRole || '');
                const shouldShow = option.value === '' || (selectedRole !== '' && packageRole === selectedRole);
                option.hidden = !shouldShow;
                option.disabled = !shouldShow;
                if (option.value !== '' && shouldShow) {
                    visibleCount += 1;
                    if (!firstVisibleValue) firstVisibleValue = option.value;
                }
            });
            if (selectedRole === '') {
                packageSelect.value = '';
                if (packageHelp) packageHelp.textContent = 'Pilih pengguna dulu agar paket lisensi sesuai role muncul.';
                return;
            }
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            if (!selectedOption || selectedOption.disabled) packageSelect.value = firstVisibleValue;
            if (packageHelp) packageHelp.textContent = visibleCount === 0 ? 'Belum ada paket lisensi untuk role user ini.' : 'Paket sudah difilter sesuai role pengguna.';
        }

        if (userSelect && packageSelect) {
            userSelect.addEventListener('change', syncPackageOptionsByUserRole);
            syncPackageOptionsByUserRole();
        }
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                document.querySelectorAll('.license-checkbox').forEach(function (checkbox) { checkbox.checked = checkAll.checked; });
            });
        }
        if (bulkForm && hiddenTarget) {
            bulkForm.addEventListener('submit', function (event) {
                hiddenTarget.innerHTML = '';
                const checked = Array.from(document.querySelectorAll('.license-checkbox:checked'));
                if (checked.length === 0) {
                    event.preventDefault();
                    alert('Pilih minimal satu user terlebih dahulu.');
                    return;
                }
                checked.forEach(function (checkbox) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = checkbox.value;
                    hiddenTarget.appendChild(input);
                });
            });
        }
    });
</script>
@endsection
