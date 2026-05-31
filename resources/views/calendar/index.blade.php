@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-blue-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Kalender</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Planning Kerja Mekanik</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Jadwal planning kerja untuk mekanik dan partner. Form planning dibuat ringkas agar koordinator cepat menyusun rencana kerja harian.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Total Planning</p>
                    <p class="mt-1 text-xl font-black text-slate-900">{{ $plannings->count() }}</p>
                </div>
                <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-blue-500">Bulan</p>
                    <p class="mt-1 text-xl font-black text-blue-700">{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-sm font-bold text-red-700">Data belum bisa disimpan:</p>
        <ul class="mt-2 list-inside list-disc text-sm text-red-600">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-1">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-900">Filter Kalender</h2>

                <form method="GET" action="{{ route('calendar.index') }}" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-slate-500">Bulan</label>
                            <select name="month" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500">Tahun</label>
                            <input type="number" name="year" value="{{ $year }}"
                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Mekanik / Partner</label>
                        <select name="mechanic_id" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Semua Mekanik</option>
                            @foreach($mechanics as $mechanic)
                            <option value="{{ $mechanic->id }}" {{ (string) $selectedMechanicId === (string) $mechanic->id ? 'selected' : '' }}>
                                {{ $mechanic->name }} — {{ $mechanic->department ?: 'NO DEPT' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Status</label>
                        <select name="status" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Semua Status</option>
                            <option value="PLANNED" {{ $selectedStatus === 'PLANNED' ? 'selected' : '' }}>PLANNED</option>
                            <option value="DONE" {{ $selectedStatus === 'DONE' ? 'selected' : '' }}>DONE</option>
                            <option value="CANCELLED" {{ $selectedStatus === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                            Terapkan
                        </button>
                        <a href="{{ route('calendar.index') }}"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            @if($canManagePlanning)
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-900">Buat Planning</h2>
                <p class="mt-1 text-xs text-slate-500">Kolom dibuat minimal: mekanik, partner, customer, lokasi, dan jenis pekerjaan.</p>

                <form method="POST" action="{{ route('calendar.plannings.store') }}" class="mt-4 space-y-4" x-data="calendarPlanningForm({{ Js::from($customerLocations) }})">
                    @csrf

                    <div>
                        <label class="text-xs font-bold text-slate-500">Mekanik <span class="text-red-500">*</span></label>
                        <select name="mechanic_id" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Pilih Mekanik</option>
                            @foreach($mechanics as $mechanic)
                            <option value="{{ $mechanic->id }}" {{ old('mechanic_id') == $mechanic->id ? 'selected' : '' }}>
                                {{ $mechanic->name }} — {{ $mechanic->position ?: 'NO POS' }} / {{ $mechanic->department ?: 'NO DEPT' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Partner</label>
                        <select name="partner_id" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Tanpa Partner</option>
                            @foreach($mechanics as $mechanic)
                            <option value="{{ $mechanic->id }}" {{ old('partner_id') == $mechanic->id ? 'selected' : '' }}>
                                {{ $mechanic->name }} — {{ $mechanic->position ?: 'NO POS' }} / {{ $mechanic->department ?: 'NO DEPT' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Customer <span class="text-red-500">*</span></label>
                        <select name="customer" x-model="selectedCustomer" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Pilih Customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer }}" {{ old('customer') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Lokasi <span class="text-red-500">*</span></label>
                        <select name="location" x-model="selectedLocation" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Pilih Lokasi</option>
                            <template x-for="location in availableLocations" :key="location">
                                <option :value="location" x-text="location"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Jenis Pekerjaan <span class="text-red-500">*</span></label>
                        <select name="job_type" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Pilih Jenis Pekerjaan</option>
                            @foreach($jobTypes as $jobType)
                            <option value="{{ $jobType }}" {{ old('job_type') == $jobType ? 'selected' : '' }}>{{ $jobType }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700">
                        Simpan Planning
                    </button>
                </form>
            </div>
            @endif
        </div>

        <div class="space-y-4 xl:col-span-2">
            @forelse($groupedPlannings as $date => $items)
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Tanggal</p>
                    <h2 class="mt-1 text-lg font-black text-slate-900">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d M Y') }}
                    </h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($items as $planning)
                    <div class="p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $planning->status_badge_class }}">
                                        {{ $planning->status }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                        {{ $planning->department ?: 'NO DEPT' }}
                                    </span>
                                </div>

                                <h3 class="mt-3 text-base font-black text-slate-950">
                                    {{ $planning->job_type ?: 'Planning Kerja' }}
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Mekanik:
                                    <span class="font-bold text-slate-800">{{ $planning->mechanic->name ?? '-' }}</span>
                                    @if($planning->partner)
                                    <span class="mx-1 text-slate-300">|</span>
                                    Partner:
                                    <span class="font-bold text-slate-800">{{ $planning->partner->name }}</span>
                                    @endif
                                </p>

                                <div class="mt-3 grid grid-cols-1 gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                    <div>
                                        <span class="font-bold text-slate-800">Customer:</span>
                                        {{ $planning->customer ?: '-' }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800">Lokasi:</span>
                                        {{ $planning->location ?: '-' }}
                                    </div>
                                </div>
                            </div>

                            @if($canManagePlanning)
                            <div class="flex shrink-0 gap-2 sm:flex-col">
                                <form method="POST" action="{{ route('calendar.plannings.status', $planning->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $planning->status === 'DONE' ? 'PLANNED' : 'DONE' }}">
                                    <button type="submit"
                                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700 hover:bg-emerald-100">
                                        {{ $planning->status === 'DONE' ? 'Reset' : 'Done' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('calendar.plannings.destroy', $planning->id) }}"
                                    onsubmit="return confirm('Hapus planning ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-700 hover:bg-red-100">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
                <p class="text-lg font-black text-slate-800">Belum ada planning kerja.</p>
                <p class="mt-2 text-sm text-slate-500">
                    Planning bulan ini masih kosong. Kalendernya bersih, tapi jangan sampai pekerjaan ikut menghilang.
                </p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function calendarPlanningForm(customerLocations) {
        return {
            selectedCustomer: @json(old('customer', '')),
            selectedLocation: @json(old('location', '')),
            customerLocations: customerLocations || {},
            get availableLocations() {
                return this.customerLocations[this.selectedCustomer] || [];
            }
        }
    }
</script>
@endsection
