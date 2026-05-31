<!--
|--------------------------------------------------------------------------
| PATH FILE:
| resources/views/calendar/planning.blade.php
|--------------------------------------------------------------------------
-->
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 pb-24">
    <a href="{{ route('calendar.index', ['month' => $month, 'year' => $year]) }}" class="inline-flex rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 shadow-sm hover:bg-slate-50">← Calendar</a>

    @if (session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-sm">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-800 shadow-sm">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm"><p class="font-black">Input belum valid.</p><ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="relative overflow-hidden rounded-[2rem] border border-blue-200 bg-white p-6 shadow-xl shadow-blue-100/70 sm:p-8">
        <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-blue-100 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Planning Kerja</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Planning Kerja Mekanik</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Buat dan pantau rencana kerja harian mekanik sesuai Department Scope.</p>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3"><p class="text-[11px] font-black uppercase tracking-wide text-blue-700">Total Bulan Ini</p><p class="mt-1 text-2xl font-black text-blue-950">{{ $plannings->count() }}</p></div>
        </div>
    </div>

    <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Group Bulan</p><h2 class="text-xl font-black text-slate-950">Planning 6 Bulan Ke Depan</h2></div><p class="text-xs font-bold text-slate-500">Klik card untuk melihat planning bulan tersebut.</p></div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
            @foreach($planningMonthCards as $card)
                @php $isSelected = (int) $month === (int) $card['month'] && (int) $year === (int) $card['year']; @endphp
                <a href="{{ route('calendar.planning', ['month' => $card['month'], 'year' => $card['year']]) }}" class="group relative overflow-hidden rounded-2xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg {{ $isSelected ? 'border-blue-300 bg-blue-50 ring-2 ring-blue-100' : 'border-slate-200 bg-slate-50 hover:bg-white' }}">
                    <div class="absolute -right-8 -top-8 h-20 w-20 rounded-full {{ $isSelected ? 'bg-blue-200' : 'bg-slate-200' }} blur-2xl"></div>
                    <div class="relative"><p class="text-[10px] font-black uppercase tracking-wider {{ $isSelected ? 'text-blue-700' : 'text-slate-400' }}">{{ $card['is_current'] ? 'Bulan Berjalan' : 'Bulan +' . $loop->index }}</p><h3 class="mt-1 text-base font-black text-slate-950">{{ $card['short_label'] }}</h3><div class="mt-4 grid grid-cols-2 gap-2 text-center"><div class="rounded-xl bg-white px-2 py-2 shadow-sm"><p class="text-[10px] font-bold text-slate-400">Total</p><p class="font-black text-slate-900">{{ $card['total_count'] }}</p></div><div class="rounded-xl bg-white px-2 py-2 shadow-sm"><p class="text-[10px] font-bold text-slate-400">Plan</p><p class="font-black text-blue-700">{{ $card['planned_count'] }}</p></div><div class="rounded-xl bg-white px-2 py-2 shadow-sm"><p class="text-[10px] font-bold text-slate-400">Done</p><p class="font-black text-emerald-700">{{ $card['done_count'] }}</p></div><div class="rounded-xl bg-white px-2 py-2 shadow-sm"><p class="text-[10px] font-bold text-slate-400">Cancel</p><p class="font-black text-red-700">{{ $card['cancelled_count'] }}</p></div></div></div>
                </a>
            @endforeach
        </div>
    </div>

    <form method="GET" action="{{ route('calendar.planning') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
        <div><label class="block text-xs font-black uppercase tracking-wider text-slate-500">Bulan</label><select name="month" class="mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 text-sm shadow-sm">@for ($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ Carbon\Carbon::create(null, $i, 1)->translatedFormat('F') }}</option>@endfor</select></div>
        <div><label class="block text-xs font-black uppercase tracking-wider text-slate-500">Tahun</label><select name="year" class="mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 text-sm shadow-sm">@for ($i = now()->year - 2; $i <= now()->year + 2; $i++)<option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>@endfor</select></div>
        <div><label class="block text-xs font-black uppercase tracking-wider text-slate-500">Mekanik</label><select name="mechanic_id" class="mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 text-sm shadow-sm"><option value="">Semua</option>@foreach ($mechanics as $mech)<option value="{{ $mech->id }}" {{ request('mechanic_id') == $mech->id ? 'selected' : '' }}>{{ $mech->name }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-black uppercase tracking-wider text-slate-500">Status</label><select name="status" class="mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 text-sm shadow-sm"><option value="">Semua</option><option value="PLANNED" {{ strtoupper((string) request('status')) == 'PLANNED' ? 'selected' : '' }}>PLANNED</option><option value="DONE" {{ strtoupper((string) request('status')) == 'DONE' ? 'selected' : '' }}>DONE</option><option value="CANCELLED" {{ strtoupper((string) request('status')) == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option></select></div>
        <div class="grid grid-cols-2 gap-2"><button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button><a href="{{ route('calendar.planning') }}" class="rounded-xl bg-white px-4 py-2 text-center text-sm font-bold text-slate-700 ring-1 ring-slate-300">Reset</a></div>
    </form>

    @if ($canManagePlanning)
    <div x-data="calendarPlanningForm({{ json_encode($customerLocations) }})" class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-base font-black text-slate-900">Buat Planning Baru</h2><form method="POST" action="{{ route('calendar.plannings.store') }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-6 sm:items-end">@csrf
        <div class="sm:col-span-1"><label class="block text-sm font-medium text-slate-900">Tanggal</label><input type="date" name="date" required value="{{ old('date', sprintf('%04d-%02d-01', $year, $month)) }}" class="mt-1 block w-full rounded-xl border-0 py-2 shadow-sm ring-1 ring-slate-300"></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-900">Mekanik</label><select name="mechanic_id" required class="mt-1 block w-full rounded-xl border-0 py-2 shadow-sm ring-1 ring-slate-300"><option value="">Pilih Mekanik</option>@foreach ($mechanics as $mechanic)<option value="{{ $mechanic->id }}">{{ $mechanic->name }}</option>@endforeach</select></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-900">Partner</label><select name="partner_id" class="mt-1 block w-full rounded-xl border-0 py-2 shadow-sm ring-1 ring-slate-300"><option value="">Tidak ada</option>@foreach ($mechanics as $mechanic)<option value="{{ $mechanic->id }}">{{ $mechanic->name }}</option>@endforeach</select></div>
        <div class="sm:col-span-1"><label class="block text-sm font-medium text-slate-900">Jenis</label><select name="job_type" required class="mt-1 block w-full rounded-xl border-0 py-2 shadow-sm ring-1 ring-slate-300"><option value="PM">PM</option><option value="BS">BS</option><option value="SCHEDULE">SCHEDULE</option></select></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-900">Customer</label><select name="customer" required x-model="selectedCustomer" @change="selectedLocation = ''" class="mt-1 block w-full rounded-xl border-0 py-2 shadow-sm ring-1 ring-slate-300"><option value="">Pilih Customer</option>@foreach($customers as $customer)<option value="{{ $customer }}">{{ $customer }}</option>@endforeach</select></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-900">Lokasi</label><select name="location" required x-model="selectedLocation" class="mt-1 block w-full rounded-xl border-0 py-2 shadow-sm ring-1 ring-slate-300"><option value="">Pilih Lokasi</option><template x-for="loc in availableLocations" :key="loc"><option :value="loc" x-text="loc"></option></template></select></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-900">Catatan</label><input type="text" name="notes" placeholder="Catatan" class="mt-1 block w-full rounded-xl border-0 py-2 shadow-sm ring-1 ring-slate-300"></div>
        <div class="sm:col-span-6 flex justify-end"><button class="w-full rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-500 sm:w-auto">Simpan Planning</button></div>
    </form></div>
    @endif

    <div class="space-y-4">@forelse ($groupedPlannings as $date => $plans)@php $dateObj = Carbon\Carbon::parse($date); @endphp<div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 bg-slate-50 px-5 py-4"><h3 class="text-lg font-black text-slate-900">{{ $dateObj->translatedFormat('l, d F Y') }}</h3><p class="text-xs font-bold text-slate-500">{{ $plans->count() }} pekerjaan</p></div><div class="divide-y divide-slate-100">@foreach($plans as $plan)<div class="p-5"><div class="flex flex-col gap-4 lg:flex-row lg:justify-between"><div><div class="flex flex-wrap gap-2"><span class="rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-black text-blue-700">{{ $plan->job_type }}</span><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-700">{{ $plan->status }}</span></div><p class="mt-3 font-black text-slate-950">{{ $plan->customer }}</p><p class="text-sm text-slate-500">{{ $plan->location }}</p><p class="mt-2 text-xs font-bold text-slate-500">Mekanik: {{ optional($plan->mechanic)->name ?? 'User tidak ditemukan' }}</p>@if($plan->note)<p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">{{ $plan->note }}</p>@endif</div>@if($canManagePlanning)<div class="grid gap-2 sm:grid-cols-2 lg:w-64"><form action="{{ route('calendar.plannings.status', $plan) }}" method="POST">@csrf @method('PATCH')<select name="status" onchange="this.form.submit()" class="block w-full rounded-xl border-slate-300 bg-white text-xs font-bold"><option value="PLANNED" {{ $plan->status == 'PLANNED' ? 'selected' : '' }}>PLANNED</option><option value="DONE" {{ $plan->status == 'DONE' ? 'selected' : '' }}>DONE</option><option value="CANCELLED" {{ $plan->status == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option></select></form><form action="{{ route('calendar.plannings.destroy', $plan) }}" method="POST" onsubmit="return confirm('Hapus planning kerja ini?');">@csrf @method('DELETE')<button class="w-full rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-700">Hapus</button></form></div>@endif</div></div>@endforeach</div></div>@empty<div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm"><p class="font-black text-slate-800">Belum ada planning kerja.</p></div>@endforelse</div>
</div>
<script>function calendarPlanningForm(customerLocations) { return { selectedCustomer: @json(old('customer', '')), selectedLocation: @json(old('location', '')), customerLocations: customerLocations || {}, get availableLocations() { return this.customerLocations[this.selectedCustomer] || []; } } }</script>
@endsection
