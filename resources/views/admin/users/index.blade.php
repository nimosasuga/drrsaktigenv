<!--
|--------------------------------------------------------------------------
| PATH FILE:
| resources/views/admin/users/index.blade.php
|--------------------------------------------------------------------------
-->
@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola data mekanik, koordinator, dan akses sistem.</p>
    </div>

    <div class="mt-4 sm:mt-0">
        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Tambah Pengguna
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Search & Filter Form Section -->
    <div class="p-6 border-b border-slate-200 bg-white">
        <form id="searchForm" action="{{ route('admin.users.index') }}" method="GET"
            class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                    class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors"
                    placeholder="Cari nama, nrpp, cabang, department...">
            </div>

            <!-- Filters Dropdowns -->
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Department Filter -->
                <select name="department" onchange="this.form.submit()"
                    class="block w-full sm:w-auto py-2 pl-3 pr-10 border-slate-300 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                    <option value="">Semua Dept</option>
                    <option value="RENTAL" {{ request('department')=='RENTAL' ? 'selected' : '' }}>RENTAL</option>
                    <option value="SERVICE" {{ request('department')=='SERVICE' ? 'selected' : '' }}>SERVICE</option>
                </select>

                <!-- Position Filter -->
                <select name="position" onchange="this.form.submit()"
                    class="block w-full sm:w-auto py-2 pl-3 pr-10 border-slate-300 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                    <option value="">Semua Posisi</option>
                    <option value="FIELD" {{ request('position')=='FIELD' ? 'selected' : '' }}>FIELD</option>
                    <option value="FMC" {{ request('position')=='FMC' ? 'selected' : '' }}>FMC</option>
                </select>

                <!-- Sort Filter -->
                <select name="sort" onchange="this.form.submit()"
                    class="block w-full sm:w-auto py-2 pl-3 pr-10 border-slate-300 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                    <option value="">Terbaru</option>
                    <option value="az" {{ request('sort')=='az' ? 'selected' : '' }}>A - Z</option>
                    <option value="za" {{ request('sort')=='za' ? 'selected' : '' }}>Z - A</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        Pengguna</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dept &
                        Posisi</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role &
                        Status</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div
                                class="h-10 w-10 flex-shrink-0 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200">
                                <span class="text-sm font-bold text-slate-600">{{ strtoupper(substr($user->name, 0, 2))
                                    }}</span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                                <div class="text-sm text-slate-500">NRPP: {{ $user->nrpp }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col gap-1">
                            @if($user->department)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->department == 'RENTAL' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }} w-fit">
                                {{ $user->department }}
                            </span>
                            @else
                            <span class="text-sm text-slate-500">-</span>
                            @endif

                            @if($user->position)
                            <span class="text-xs text-slate-500 font-medium">{{ $user->position }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col gap-1">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 uppercase w-fit">
                                {{ str_replace('_', ' ', $user->status_user) }}
                            </span>
                            <div>
                                @if($user->is_verified)
                                <span class="inline-flex items-center text-xs font-medium text-emerald-600">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Verified
                                </span>
                                @else
                                <span class="inline-flex items-center text-xs font-medium text-amber-600">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Unverified
                                </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors"
                                title="Edit Pengguna">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                            </a>
                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');"
                                class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-colors"
                                    title="Hapus Pengguna">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-500 font-medium">Belum ada pengguna di
                        dalam sistem.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
        {{ $users->links() }}
    </div>
    @endif
</div>

<script>
    // Live Search Script (Mempertahankan fungsi debounce yang sudah ada)
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let typingTimer;
        const doneTypingInterval = 500;

        if (searchInput && searchForm) {
            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    searchForm.submit();
                }, doneTypingInterval);
            });

            // Fokuskan kursor ke akhir teks saat refresh halaman (bila sedang mencari teks)
            if (searchInput.value) {
                searchInput.focus();
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        }
    });
</script>
@endsection
