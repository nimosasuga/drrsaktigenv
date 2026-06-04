@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="grid gap-5 xl:grid-cols-12 xl:items-start">
            <div class="xl:col-span-8">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-indigo-600">Recommendation Control</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    Detail Rekomendasi Unit
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Detail semua rekomendasi sparepart berdasarkan serial number unit. Gunakan tombol List Sparepart
                    Terfilter
                    untuk melakukan action approval, need supply, mark supplied, reject, close, atau cancel.
                </p>
            </div>

            <div class="xl:col-span-4">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('sparepart-recommendations.units', ['department' => $department]) }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">
                        ← Kembali
                    </a>

                    <a href="{{ route('sparepart-recommendations.parts', ['serial_number' => $serialNumber, 'department' => $department]) }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                        List Terfilter
                    </a>

                    <a href="{{ route('rental-spareparts.index') }}"
                        class="col-span-2 inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">
                        Management Sparepart
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-indigo-200 bg-linear-to-br from-indigo-50 to-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-600">Serial Number</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    {{ $unit->serial_number }}
                </h2>
                <p class="mt-2 text-sm font-semibold text-slate-600">
                    {{ $unit->customer }} / {{ $unit->location }} • {{ $unit->unit_type }}
                </p>
                <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">
                    Department {{ $department }} • Last Work Date {{ $unit->latest_work_date ?: '-' }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:w-1/2">
                <div class="rounded-2xl bg-white/80 px-4 py-3">
                    <p class="text-[10px] font-black uppercase text-slate-400">Items</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($unit->total_items) }}</p>
                </div>
                <div class="rounded-2xl bg-amber-50 px-4 py-3">
                    <p class="text-[10px] font-black uppercase text-amber-500">Need</p>
                    <p class="text-lg font-black text-amber-700">{{ number_format($unit->need_supply_count) }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50 px-4 py-3">
                    <p class="text-[10px] font-black uppercase text-emerald-500">Supplied</p>
                    <p class="text-lg font-black text-emerald-700">{{ number_format($unit->supplied_count) }}</p>
                </div>
                <div class="rounded-2xl bg-purple-50 px-4 py-3">
                    <p class="text-[10px] font-black uppercase text-purple-500">Installed</p>
                    <p class="text-lg font-black text-purple-700">{{ number_format($unit->installed_count) }}</p>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-2 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white/80 px-4 py-3">
                <p class="text-[10px] font-black uppercase text-slate-400">Qty Recommended</p>
                <p class="mt-1 text-lg font-black text-slate-900">{{ number_format($unit->qty_recommended) }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white/80 px-4 py-3">
                <p class="text-[10px] font-black uppercase text-emerald-500">Qty Supplied</p>
                <p class="mt-1 text-lg font-black text-emerald-700">{{ number_format($unit->qty_supplied) }}</p>
            </div>
            <div class="rounded-2xl border border-purple-100 bg-white/80 px-4 py-3">
                <p class="text-[10px] font-black uppercase text-purple-500">Qty Installed</p>
                <p class="mt-1 text-lg font-black text-purple-700">{{ number_format($unit->qty_installed) }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-black text-slate-900">Daftar Rekomendasi Part Unit Ini</p>
                <p class="mt-1 text-sm text-slate-500">
                    Action tetap dilakukan dari List Sparepart Terfilter agar alur approval dan supply tetap memakai
                    form stabil.
                </p>
            </div>

            <a href="{{ route('sparepart-recommendations.parts', ['serial_number' => $serialNumber, 'department' => $department]) }}"
                class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white hover:bg-indigo-700">
                Buka Action di List Sparepart
            </a>
        </div>
    </div>

    <div class="space-y-3">
        @foreach($controls as $control)
        @php
        $recClass = match($control->recommendation_status) {
        'RECOMMENDED', 'REVIEWED' => 'border-blue-200 bg-blue-50 text-blue-700',
        'APPROVED', 'SUPPLIED', 'INSTALLED' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'NEED_SUPPLY', 'PARTIAL_INSTALLED' => 'border-amber-200 bg-amber-50 text-amber-700',
        'REJECTED', 'CANCELLED' => 'border-red-200 bg-red-50 text-red-700',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
        };

        $supplyClass = match($control->supply_status) {
        'SUPPLIED' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'NEED_SUPPLY', 'PARTIAL_SUPPLIED' => 'border-amber-200 bg-amber-50 text-amber-700',
        'NOT_REQUIRED' => 'border-slate-200 bg-slate-50 text-slate-600',
        default => 'border-red-200 bg-red-50 text-red-700',
        };
        @endphp

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $recClass }}">
                            {{ $control->recommendation_status }}
                        </span>
                        <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $supplyClass }}">
                            {{ $control->supply_status }}
                        </span>
                    </div>

                    <h3 class="mt-3 text-lg font-black tracking-tight text-slate-950">
                        {{ $control->part_number ?: '-' }} — {{ $control->part_name ?: '-' }}
                    </h3>

                    <p class="mt-1 text-sm font-semibold text-slate-600">
                        Work Date {{ $control->work_date ?: '-' }} • Recommended By {{ $control->recommended_by_name ?:
                        '-' }}
                    </p>

                    @if($control->remarks || $control->review_note || $control->supply_note)
                    <div class="mt-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">
                        @if($control->remarks)
                        <p><span class="font-black">Mekanik:</span> {{ $control->remarks }}</p>
                        @endif
                        @if($control->review_note)
                        <p><span class="font-black">Review:</span> {{ $control->review_note }}</p>
                        @endif
                        @if($control->supply_note)
                        <p><span class="font-black">Supply:</span> {{ $control->supply_note }}</p>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="space-y-2 xl:w-80">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase text-slate-400">Recommended</p>
                            <p class="text-lg font-black text-slate-900">{{ number_format($control->qty_recommended) }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase text-emerald-500">Supplied</p>
                            <p class="text-lg font-black text-emerald-700">{{ number_format($control->qty_supplied) }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-purple-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase text-purple-500">Installed</p>
                            <p class="text-lg font-black text-purple-700">{{ number_format($control->qty_installed) }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('sparepart-recommendations.parts', array_filter([
                                                    'serial_number' => $serialNumber,
                                                    'part_number' => $control->part_number,
                                                    'department' => $department,
                                                ])) }}"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-black text-white hover:bg-indigo-700">
                        Buka Action
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection