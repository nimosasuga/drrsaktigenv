@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-indigo-600">Recommendation Control</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Sparepart Recommendation Control Center</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Kontrol rekomendasi sparepart dari Update Job: review kebutuhan, approve/reject, tandai perlu supply, supplied, dan close.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('update-jobs.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Update Job
                </a>
                <a href="{{ route('rental-spareparts.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">
                    Management Sparepart
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

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

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('sparepart-recommendations.index') }}" class="grid gap-3 lg:grid-cols-6">
            @if(in_array(strtolower((string) (auth()->user()->status_user ?? auth()->user()->role ?? '')), ['admin', 'super_admin'], true))
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Department</label>
                    <select name="department" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <option value="RENTAL" {{ $filters['department'] === 'RENTAL' ? 'selected' : '' }}>RENTAL</option>
                        <option value="SERVICE" {{ $filters['department'] === 'SERVICE' ? 'selected' : '' }}>SERVICE</option>
                    </select>
                </div>
            @endif

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="SN, customer, part, lokasi, mekanik..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">SN Unit</label>
                <input type="text" name="serial_number" value="{{ $filters['serial_number'] }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Part Number</label>
                <input type="text" name="part_number" value="{{ $filters['part_number'] }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Rec Status</label>
                <select name="recommendation_status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <option value="">All</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" {{ $filters['recommendation_status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Supply Status</label>
                <select name="supply_status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <option value="">All</option>
                    @foreach($supplyStatusOptions as $status)
                        <option value="{{ $status }}" {{ $filters['supply_status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Date From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Date To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>

            <div class="flex items-end gap-2 lg:col-span-3 lg:justify-end">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white hover:bg-indigo-700 sm:flex-none">Filter</button>
                <a href="{{ route('sparepart-recommendations.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($controls as $control)
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
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $recClass }}">{{ $control->recommendation_status }}</span>
                            <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $supplyClass }}">{{ $control->supply_status }}</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $control->department }}</span>
                            @if($control->is_cross_allocation)
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-black text-red-700">CROSS ALLOCATION</span>
                            @endif
                        </div>

                        <h2 class="mt-3 text-lg font-black text-slate-950">{{ $control->part_number ?: '-' }} — {{ $control->part_name ?: '-' }}</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-600">
                            SN {{ $control->serial_number ?: '-' }} • {{ $control->customer ?: '-' }} / {{ $control->location ?: '-' }} • {{ $control->unit_type ?: '-' }}
                        </p>

                        <div class="mt-4 grid gap-3 text-sm md:grid-cols-4">
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase text-slate-400">Qty Recommended</p>
                                <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($control->qty_recommended) }}</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-3">
                                <p class="text-xs font-bold uppercase text-emerald-500">Qty Supplied</p>
                                <p class="mt-1 text-xl font-black text-emerald-700">{{ number_format($control->qty_supplied) }}</p>
                            </div>
                            <div class="rounded-2xl bg-purple-50 p-3">
                                <p class="text-xs font-bold uppercase text-purple-500">Qty Installed</p>
                                <p class="mt-1 text-xl font-black text-purple-700">{{ number_format($control->qty_installed) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase text-slate-400">Recommended By</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $control->recommended_by_name ?: '-' }}</p>
                            </div>
                        </div>

                        @if($control->sourceStock)
                            <p class="mt-4 rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-800">
                                Source Stock: #{{ $control->sourceStock->id }} • {{ $control->sourceStock->item?->part_number }} • {{ $control->sourceStock->location?->location_name }} • Sisa {{ number_format($control->sourceStock->qty_available) }}
                            </p>
                        @endif

                        @if($control->remarks || $control->review_note || $control->supply_note)
                            <div class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">
                                @if($control->remarks)<p><span class="font-black">Mekanik:</span> {{ $control->remarks }}</p>@endif
                                @if($control->review_note)<p><span class="font-black">Review:</span> {{ $control->review_note }}</p>@endif
                                @if($control->supply_note)<p><span class="font-black">Supply:</span> {{ $control->supply_note }}</p>@endif
                            </div>
                        @endif
                    </div>

                    @if($canManage)
                        <div class="w-full space-y-3 xl:w-80">
                            <form method="POST" action="{{ route('sparepart-recommendations.status', $control) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                @csrf
                                @method('PATCH')

                                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Action</label>
                                <select name="action_type" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold">
                                    <option value="REVIEWED">Mark Reviewed</option>
                                    <option value="APPROVED">Approve</option>
                                    <option value="NEED_SUPPLY">Need Supply</option>
                                    <option value="SUPPLIED">Mark Supplied</option>
                                    <option value="REJECTED">Reject</option>
                                    <option value="CLOSED">Close</option>
                                    <option value="CANCELLED">Cancel</option>
                                </select>

                                <label class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Qty Supplied</label>
                                <input type="number" name="qty_supplied" min="0" placeholder="Opsional" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm">

                                <label class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Source Stock</label>
                                <select name="source_stock_id" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                    <option value="">Manual / Tidak pilih stok</option>
                                    @foreach($sourceStocks as $stock)
                                        <option value="{{ $stock->id }}">
                                            #{{ $stock->id }} | {{ $stock->item?->part_number }} | Sisa {{ $stock->qty_available }} | SN {{ $stock->allocation_sn_unit ?: $stock->source_sn_unit ?: '-' }}
                                        </option>
                                    @endforeach
                                </select>

                                <label class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Source Type</label>
                                <select name="source_type" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                    <option value="MANUAL">MANUAL</option>
                                    <option value="STOCK">STOCK</option>
                                    <option value="PURCHASE">PURCHASE</option>
                                    <option value="BORROWED">BORROWED</option>
                                </select>

                                <label class="mb-1 mt-3 block text-xs font-black uppercase tracking-wide text-slate-500">Note</label>
                                <textarea name="note" rows="2" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Catatan koordinator"></textarea>

                                <button type="submit" onclick="return confirm('Update recommendation control ini?')" class="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-black text-white hover:bg-indigo-700">
                                    Save Action
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <p class="text-lg font-black text-slate-800">Belum ada recommendation control.</p>
                <p class="mt-2 text-sm text-slate-500">Data akan muncul otomatis setelah mekanik mengisi Recommendation Part di Update Job.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $controls->links() }}
    </div>
</div>
@endsection
