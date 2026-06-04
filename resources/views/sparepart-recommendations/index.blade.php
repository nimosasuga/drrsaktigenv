@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-indigo-600">Recommendation Control</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    Sparepart Recommendation Control
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Pilih mode kerja Recommendation Control. Gunakan tampilan berdasarkan unit untuk analisa per serial
                    number,
                    atau buka list sparepart untuk review dan action detail.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Department Aktif</p>
                <p class="mt-1 text-lg font-black text-slate-900">{{ $department }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-500">Recommended</p>
            <p class="mt-2 text-2xl font-black text-blue-700">{{ number_format($summary['recommended']) }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-500">Need Supply</p>
            <p class="mt-2 text-2xl font-black text-amber-700">{{ number_format($summary['need_supply']) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-500">Supplied</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($summary['supplied']) }}</p>
        </div>
        <div class="rounded-3xl border border-purple-200 bg-purple-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-purple-500">Installed</p>
            <p class="mt-2 text-2xl font-black text-purple-700">{{ number_format($summary['installed']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-300 bg-slate-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Closed</p>
            <p class="mt-2 text-2xl font-black text-slate-700">{{ number_format($summary['closed']) }}</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div
            class="rounded-3xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-600">
                        Mode Unit
                    </p>
                    <h2 class="mt-3 text-xl font-black tracking-tight text-slate-950">
                        Rekomendasi Berdasarkan Serial Number Unit
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Breakdown kebutuhan sparepart berdasarkan serial number. Cocok untuk melihat unit mana yang
                        paling banyak membutuhkan rekomendasi part.
                    </p>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 7h.01M5 17h.01M5 12h14M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" />
                    </svg>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-dashed border-indigo-200 bg-white/70 px-4 py-3">
                <p class="text-sm font-bold text-indigo-700">
                    Fase 2
                </p>
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Menu ini akan diaktifkan setelah halaman group by serial number dibuat.
                </p>
            </div>

            <button type="button" disabled
                class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-slate-200 px-5 py-3 text-sm font-black text-slate-500 cursor-not-allowed">
                Segera Dibuat
            </button>
        </div>

        <div
            class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600">
                        Mode Sparepart
                    </p>
                    <h2 class="mt-3 text-xl font-black tracking-tight text-slate-950">
                        List Sparepart Recommendation
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Buka list recommendation control saat ini. Gunakan untuk review, approve, need supply, mark
                        supplied, reject, close, atau cancel.
                    </p>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8 4-8-4m16 0l-8-4-8 4m16 0v10l-8 4m0-10v10m0-10L4 7m0 0v10l8 4" />
                    </svg>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-2">
                <div class="rounded-2xl bg-white/80 px-3 py-3">
                    <p class="text-[10px] font-black uppercase text-slate-400">Need Supply</p>
                    <p class="mt-1 text-lg font-black text-amber-700">{{ number_format($summary['need_supply']) }}</p>
                </div>
                <div class="rounded-2xl bg-white/80 px-3 py-3">
                    <p class="text-[10px] font-black uppercase text-slate-400">Supplied</p>
                    <p class="mt-1 text-lg font-black text-emerald-700">{{ number_format($summary['supplied']) }}</p>
                </div>
                <div class="rounded-2xl bg-white/80 px-3 py-3">
                    <p class="text-[10px] font-black uppercase text-slate-400">Installed</p>
                    <p class="mt-1 text-lg font-black text-purple-700">{{ number_format($summary['installed']) }}</p>
                </div>
            </div>

            <a href="{{ route('sparepart-recommendations.parts') }}"
                class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
                Buka List Sparepart
            </a>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-black text-slate-900">Catatan fase 1</p>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            Halaman ini hanya mengubah pintu masuk Recommendation Control. Workflow approval, Mark Supplied, Create
            Stock IN,
            status lifecycle, role access, dan department isolation tetap menggunakan logic yang sudah stabil.
        </p>
    </div>
</div>
@endsection