@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-purple-600">Rental Sparepart</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Review Usage Sparepart</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review hasil smart matching dari Update Job Install Part. Approve akan membuat movement OUT final, reject akan mengembalikan reserved stock jika ada.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row lg:flex-col">
                <a href="{{ route('rental-spareparts.reviews.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                    Export CSV
                </a>
                <a href="{{ route('rental-spareparts.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Kembali ke Stok
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total</p><p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total']) }}</p></div>
        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-blue-500">Pending</p><p class="mt-2 text-2xl font-black text-blue-700">{{ number_format($summary['pending']) }}</p></div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-amber-500">Need Source</p><p class="mt-2 text-2xl font-black text-amber-700">{{ number_format($summary['need_source']) }}</p></div>
        <div class="rounded-3xl border border-orange-200 bg-orange-50 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-orange-500">Pinjam</p><p class="mt-2 text-2xl font-black text-orange-700">{{ number_format($summary['borrowed']) }}</p></div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-emerald-500">Approved</p><p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($summary['approved']) }}</p></div>
        <div class="rounded-3xl border border-red-200 bg-red-50 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-red-500">Rejected</p><p class="mt-2 text-2xl font-black text-red-700">{{ number_format($summary['rejected']) }}</p></div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('rental-spareparts.reviews.index') }}" class="grid gap-3 lg:grid-cols-8">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Status</label>
                <select name="review_status" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100">
                    <option value="">Semua</option>
                    <option value="PENDING_REVIEW" {{ $filters['review_status'] === 'PENDING_REVIEW' ? 'selected' : '' }}>Pending</option>
                    <option value="NEED_SOURCE_SELECTION" {{ $filters['review_status'] === 'NEED_SOURCE_SELECTION' ? 'selected' : '' }}>Need Source</option>
                    <option value="APPROVED" {{ $filters['review_status'] === 'APPROVED' ? 'selected' : '' }}>Approved</option>
                    <option value="REJECTED" {{ $filters['review_status'] === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                    <option value="CANCELLED_BY_JOB_EDIT" {{ $filters['review_status'] === 'CANCELLED_BY_JOB_EDIT' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Match</label>
                <select name="match_type" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100">
                    <option value="">Semua</option>
                    <option value="NO_JOB_EXACT" {{ $filters['match_type'] === 'NO_JOB_EXACT' ? 'selected' : '' }}>No Job Exact</option>
                    <option value="SN_EXACT" {{ $filters['match_type'] === 'SN_EXACT' ? 'selected' : '' }}>SN Exact</option>
                    <option value="PART_ONLY" {{ $filters['match_type'] === 'PART_ONLY' ? 'selected' : '' }}>Part Only</option>
                    <option value="NOT_FOUND" {{ $filters['match_type'] === 'NOT_FOUND' ? 'selected' : '' }}>Not Found</option>
                </select>
            </div>
            <div><label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Part Number</label><input type="text" name="part_number" value="{{ $filters['part_number'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100"></div>
            <div><label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">SN Unit</label><input type="text" name="sn_unit" value="{{ $filters['sn_unit'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100"></div>
            <div><label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">No Job</label><input type="text" name="no_job" value="{{ $filters['no_job'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100"></div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Pinjam</label>
                <select name="borrowed" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100">
                    <option value="">Semua</option>
                    <option value="yes" {{ $filters['borrowed'] === 'yes' ? 'selected' : '' }}>Pinjam</option>
                    <option value="no" {{ $filters['borrowed'] === 'no' ? 'selected' : '' }}>Normal</option>
                </select>
            </div>
            <div><label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Dari</label><input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100"></div>
            <div><label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Sampai</label><input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100"></div>
            <div class="flex flex-wrap gap-2 lg:col-span-8 lg:justify-end">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-purple-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-purple-700 sm:flex-none">Filter</button>
                <a href="{{ route('rental-spareparts.reviews.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">Export CSV</a>
                <a href="{{ route('rental-spareparts.reviews.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($reviews as $review)
            @php
                $statusClass = 'bg-slate-50 text-slate-700 border-slate-200';
                if ($review->review_status === 'PENDING_REVIEW') { $statusClass = 'bg-blue-50 text-blue-700 border-blue-200'; }
                if ($review->review_status === 'NEED_SOURCE_SELECTION') { $statusClass = 'bg-amber-50 text-amber-700 border-amber-200'; }
                if ($review->review_status === 'APPROVED') { $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200'; }
                if ($review->review_status === 'REJECTED') { $statusClass = 'bg-red-50 text-red-700 border-red-200'; }
                $matchClass = $review->is_borrowed ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-slate-50 text-slate-700 border-slate-200';
                $canAct = in_array($review->review_status, ['PENDING_REVIEW', 'NEED_SOURCE_SELECTION'], true);
                $stockChoices = $sourceOptions[strtoupper((string) $review->part_number)] ?? [];
            @endphp

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $statusClass }}">{{ $review->review_status }}</span>
                            <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $matchClass }}">{{ $review->match_type }}</span>
                            @if($review->is_borrowed)<span class="rounded-full border border-orange-200 bg-orange-50 px-2.5 py-1 text-xs font-black text-orange-700">PINJAM</span>@endif
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ optional($review->work_date)->format('d M Y') ?: '-' }}</span>
                        </div>

                        <h2 class="mt-3 text-lg font-black text-slate-950">{{ $review->part_number ?: '-' }}</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $review->part_name ?: '-' }}</p>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Update Job</p><p class="mt-1 font-bold text-slate-800">SN: {{ $review->job_serial_number ?: '-' }}</p><p class="text-xs text-slate-500">{{ $review->job_customer ?: '-' }}</p><p class="text-xs text-slate-500">{{ $review->job_location ?: '-' }}</p><p class="text-xs text-slate-500">No Job: {{ $review->no_job ?: '-' }}</p></div>
                            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Source / Alokasi Stok</p><p class="mt-1 font-bold text-slate-800">{{ $review->original_allocation_customer ?: '-' }}</p><p class="text-xs text-slate-500">{{ $review->original_allocation_location ?: '-' }}</p><p class="text-xs text-slate-500">{{ $review->original_allocation_type_unit ?: '-' }} / {{ $review->original_allocation_sn_unit ?: '-' }}</p></div>
                            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Actual Pemakaian</p><p class="mt-1 font-bold text-slate-800">{{ $review->actual_customer ?: '-' }}</p><p class="text-xs text-slate-500">{{ $review->actual_location ?: '-' }}</p><p class="text-xs text-slate-500">{{ $review->actual_type_unit ?: '-' }} / {{ $review->actual_sn_unit ?: '-' }}</p></div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-3">
                            <div class="rounded-2xl bg-purple-50 p-3"><p class="text-xs font-bold uppercase tracking-wide text-purple-400">Qty Request</p><p class="mt-1 text-xl font-black text-purple-700">{{ number_format($review->qty_requested) }}</p></div>
                            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Mekanik</p><p class="mt-1 font-bold text-slate-800">{{ $review->mechanic_name ?: optional($review->mechanic)->name ?: '-' }}</p></div>
                            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Stock Ref</p><p class="mt-1 font-bold text-slate-800">{{ optional(optional($review->stock)->item)->part_number ?: '-' }}</p><p class="text-xs text-slate-500">{{ optional(optional($review->stock)->location)->location_name ?: '-' }}</p></div>
                        </div>

                        @if($review->review_note)
                            <p class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">{{ $review->review_note }}</p>
                        @endif

                        @if($canAct)
                            <div class="mt-4 rounded-3xl border border-purple-100 bg-purple-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-purple-700">Aksi Review</p>

                                <form method="POST" action="{{ route('rental-spareparts.reviews.approve', $review) }}" class="mt-3 space-y-3">
                                    @csrf
                                    @if($review->review_status === 'NEED_SOURCE_SELECTION')
                                        <div>
                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Pilih Stok Sumber</label>
                                            <select name="stock_id" required class="w-full rounded-2xl border border-purple-200 bg-white px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100">
                                                <option value="">Pilih stok part yang tersedia</option>
                                                @foreach($stockChoices as $choice)
                                                    <option value="{{ $choice['id'] }}">{{ $choice['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <textarea name="review_note" rows="2" placeholder="Catatan approve opsional" class="w-full rounded-2xl border border-purple-200 bg-white px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-100"></textarea>

                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <button type="submit" onclick="return confirm('Approve review ini dan buat movement OUT final?')" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">Approve</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('rental-spareparts.reviews.reject', $review) }}" class="mt-3 space-y-3">
                                    @csrf
                                    <textarea name="review_note" rows="2" placeholder="Catatan reject opsional" class="w-full rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100"></textarea>
                                    <button type="submit" onclick="return confirm('Reject review ini? Reserved stock akan dikembalikan jika ada.')" class="inline-flex w-full items-center justify-center rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white hover:bg-red-700">Reject</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm"><p class="text-lg font-black text-slate-800">Belum ada usage review.</p><p class="mt-2 text-sm text-slate-500">Data akan muncul setelah Update Job RENTAL menyimpan Install Part.</p></div>
        @endforelse
    </div>

    <div>{{ $reviews->links() }}</div>
</div>
@endsection
