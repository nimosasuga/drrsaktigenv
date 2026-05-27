<!-- resources/views/assets/list.blade.php -->
@extends('layouts.app')

@section('content')
@php
$user = Auth::user();

$assetManageRoles = ['super_admin', 'admin', 'koordinator', 'sect_head'];

$canManageAsset = in_array(strtolower((string) ($user->role ?? '')), $assetManageRoles, true)
|| in_array(strtolower((string) ($user->status_user ?? '')), $assetManageRoles, true);
@endphp
<div class="max-w-7xl mx-auto">
    <!-- Header with Back Button -->
    <div class="mb-8">
        <a href="{{ route('assets.index') }}"
            class="inline-flex items-center text-sm font-bold text-blue-600 hover:text-blue-800 mb-4 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Kembali ke Daftar Lokasi
        </a>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">{{ $customer }}</h1>
                <p class="text-sm font-semibold text-slate-500 mt-2 flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Site: {{ $location }}
                </p>
            </div>
            <div class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-bold shadow-sm inline-flex w-max">
                Total {{ $assets->total() }} Unit Alat Berat
            </div>
        </div>
    </div>

    <!-- Grid List Unit -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
        @forelse($assets as $asset)
        <div
            class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg transition-all flex flex-col h-full group relative overflow-hidden">
            <div
                class="absolute right-0 top-0 w-2 h-full bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity">
            </div>

            <div class="flex justify-between items-start mb-5">
                <div
                    class="bg-slate-50 text-slate-600 p-3 rounded-2xl border border-slate-100 group-hover:bg-blue-50 group-hover:text-blue-600 group-hover:border-blue-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <!-- Status Unit / Lambung Placeholder -->
                @if(isset($asset->nomor_lambung) && $asset->nomor_lambung != '')
                <span
                    class="px-2.5 py-1 bg-slate-800 text-white text-[10px] font-bold rounded-lg border border-slate-900 uppercase">No:
                    {{ $asset->nomor_lambung }}</span>
                @endif
            </div>

            <div class="mb-4 flex-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Serial Number</p>
                <h3 class="text-lg font-bold text-slate-900 mb-3">{{ $asset->serial_number }}</h3>

                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Type/Model</p>
                        <p class="text-xs font-bold text-slate-700 truncate">{{ $asset->unit_type ?? $asset->unit_model
                            ?? $asset->tipe_unit ?? '-' }}</p>
                    </div>
                    <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tahun</p>
                        <p class="text-xs font-bold text-slate-700">{{ $asset->year ?? $asset->tahun ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 mt-auto">
                <a href="{{ route('assets.show', $asset->id) }}"
                    class="flex items-center justify-between w-full px-4 py-2.5 bg-white border-2 border-slate-200 text-slate-700 hover:border-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-xl text-sm font-bold transition-all group-btn">
                    Lihat Histori Unit
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-3xl p-8 border border-slate-100 text-center">
            <p class="text-slate-500">Tidak ada unit ditemukan untuk lokasi dan customer ini.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $assets->links() }}
    </div>
</div>
@endsection