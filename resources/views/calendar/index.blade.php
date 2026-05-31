<!--
|--------------------------------------------------------------------------
| PATH FILE:
| resources/views/calendar/index.blade.php
|--------------------------------------------------------------------------
-->
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <!-- TABS NAVIGATION -->
    <div class="flex space-x-4 border-b border-slate-200">
        <button onclick="switchTab('planning')" id="btn-planning"
            class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm border-blue-600 text-blue-600 transition-colors">
            Planning Kerja
        </button>
        @if($canViewPiket ?? false)
        <button onclick="switchTab('piket')" id="btn-piket"
            class="whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors">
            Jadwal Piket (Sabtu)
        </button>
        @endif
    </div>

    <!-- TAB 1: PLANNING KERJA (KODE LAMA) -->
    <div id="tab-planning" class="block space-y-6 transition-opacity duration-300">
        <div class="rounded-3xl border border-blue-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Kalender</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Planning Kerja
                        Mekanik</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Jadwal planning kerja untuk mekanik dan partner. Form planning dibuat ringkas agar koordinator
                        cepat
                        menyusun rencana kerja harian.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Total Planning</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $plannings->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Bulan Ini</p>
                        <p class="mt-1 text-xl font-black text-blue-900">{{ Carbon\Carbon::create($year, $month,
                            1)->translatedFormat('F Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Formulir Filter Bulan & Tahun -->
            <form method="GET" action="{{ route('calendar.index') }}"
                class="mt-8 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div>
                    <label for="month"
                        class="block text-xs font-bold uppercase tracking-wider text-slate-500">Bulan</label>
                    <select name="month" id="month"
                        class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-40">
                        @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ $month==$i ? 'selected' : '' }}>
                            {{ Carbon\Carbon::create(null, $i, 1)->translatedFormat('F') }}
                            </option>
                            @endfor
                    </select>
                </div>

                <div>
                    <label for="year"
                        class="block text-xs font-bold uppercase tracking-wider text-slate-500">Tahun</label>
                    <select name="year" id="year"
                        class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-32">
                        @for ($i = now()->year - 2; $i <= now()->year + 2; $i++)
                            <option value="{{ $i }}" {{ $year==$i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                            @endfor
                    </select>
                </div>

                <div>
                    <label for="mechanic_id"
                        class="block text-xs font-bold uppercase tracking-wider text-slate-500">Mekanik/Partner</label>
                    <select name="mechanic_id" id="mechanic_id"
                        class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-48">
                        <option value="">Semua Mekanik</option>
                        @foreach ($mechanics as $mech)
                        <option value="{{ $mech->id }}" {{ request('mechanic_id')==$mech->id ? 'selected' : '' }}>
                            {{ $mech->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Status
                        Pekerjaan</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-40">
                        <option value="">Semua Status</option>
                        <option value="planned" {{ request('status')=='planned' ? 'selected' : '' }}>Planned</option>
                        <option value="in_progress" {{ request('status')=='in_progress' ? 'selected' : '' }}>In Progress
                        </option>
                        <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed
                        </option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-800">
                        Filter
                    </button>
                    <a href="{{ route('calendar.index') }}"
                        class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                        Reset
                    </a>
                </div>
            </form>

            @if ($canManagePlanning)
            <div x-data="calendarPlanningForm({{ json_encode($customerLocations) }})"
                class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-base font-bold text-slate-900">Buat Planning Baru</h2>
                <form method="POST" action="{{ route('calendar.plannings.store') }}"
                    class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6 items-end">
                    @csrf

                    <div class="sm:col-span-1">
                        <label for="date" class="block text-sm font-medium leading-6 text-slate-900">Tanggal</label>
                        <div class="mt-1">
                            <input type="date" name="date" id="date" required
                                value="{{ old('date', request('year') . '-' . str_pad(request('month'), 2, '0', STR_PAD_LEFT) . '-01') }}"
                                class="block w-full rounded-xl border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="mechanic_id"
                            class="block text-sm font-medium leading-6 text-slate-900">Mekanik</label>
                        <div class="mt-1">
                            <select id="mechanic_id" name="mechanic_id" required
                                class="block w-full rounded-xl border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                <option value="">Pilih Mekanik...</option>
                                @foreach ($mechanics as $mechanic)
                                <option value="{{ $mechanic->id }}" {{ old('mechanic_id')==$mechanic->id ? 'selected' :
                                    '' }}>
                                    {{ $mechanic->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="partner_id" class="block text-sm font-medium leading-6 text-slate-900">Partner
                            (Opsional)</label>
                        <div class="mt-1">
                            <select id="partner_id" name="partner_id"
                                class="block w-full rounded-xl border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                <option value="">Tidak ada partner...</option>
                                @foreach ($mechanics as $mechanic)
                                <option value="{{ $mechanic->id }}" {{ old('partner_id')==$mechanic->id ? 'selected' :
                                    '' }}>
                                    {{ $mechanic->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="job_type" class="block text-sm font-medium leading-6 text-slate-900">Jenis
                            Pekerjaan</label>
                        <div class="mt-1">
                            <select id="job_type" name="job_type" required
                                class="block w-full rounded-xl border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                <option value="PM" {{ old('job_type')=='PM' ? 'selected' : '' }}>PM (Preventive
                                    Maintenance)</option>
                                <option value="BS" {{ old('job_type')=='BS' ? 'selected' : '' }}>BS (Breakdown Service)
                                </option>
                                <option value="SCHEDULE" {{ old('job_type')=='SCHEDULE' ? 'selected' : '' }}>SCHEDULE
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="customer"
                            class="block text-sm font-medium leading-6 text-slate-900">Customer</label>
                        <div class="mt-1">
                            <select id="customer" name="customer" required x-model="selectedCustomer"
                                @change="selectedLocation = ''"
                                class="block w-full rounded-xl border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                <option value="">Pilih Customer...</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer }}">{{ $customer }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="location" class="block text-sm font-medium leading-6 text-slate-900">Lokasi</label>
                        <div class="mt-1">
                            <select id="location" name="location" required x-model="selectedLocation"
                                class="block w-full rounded-xl border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                <option value="">Pilih Lokasi...</option>
                                <template x-for="loc in availableLocations" :key="loc">
                                    <option :value="loc" x-text="loc"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-sm font-medium leading-6 text-slate-900">Catatan</label>
                        <div class="mt-1">
                            <input type="text" name="notes" id="notes" value="{{ old('notes') }}"
                                placeholder="Contoh: Bawa alat khusus..."
                                class="block w-full rounded-xl border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-6 mt-2 flex justify-end">
                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                            Simpan Planning
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <div class="mt-10">
                <div class="space-y-6">
                    @forelse ($groupedPlannings as $date => $plans)
                    @php
                    $dateObj = \Carbon\Carbon::parse($date);
                    $isToday = $dateObj->isToday();
                    $isWeekend = $dateObj->isWeekend();
                    @endphp
                    <div
                        class="relative overflow-hidden rounded-2xl border {{ $isToday ? 'border-blue-300 shadow-md ring-1 ring-blue-100' : 'border-slate-200 shadow-sm' }} bg-white">

                        @if($isToday)
                        <div
                            class="absolute right-0 top-0 rounded-bl-xl bg-blue-600 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">
                            HARI INI
                        </div>
                        @endif

                        <div
                            class="border-b {{ $isToday ? 'border-blue-100 bg-blue-50/50' : 'border-slate-100 bg-slate-50/50' }} px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 flex-col items-center justify-center rounded-xl {{ $isToday ? 'bg-blue-600 text-white' : ($isWeekend ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                                    <span class="text-[10px] font-bold uppercase leading-none">{{
                                        $dateObj->translatedFormat('M') }}</span>
                                    <span class="mt-0.5 text-lg font-black leading-none">{{ $dateObj->format('d')
                                        }}</span>
                                </div>
                                <div>
                                    <h3
                                        class="text-lg font-bold {{ $isToday ? 'text-blue-950' : ($isWeekend ? 'text-rose-950' : 'text-slate-900') }}">
                                        {{ $dateObj->translatedFormat('l') }}
                                    </h3>
                                    <p class="text-xs font-medium text-slate-500">{{ $plans->count() }} pekerjaan
                                        dijadwalkan</p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach ($plans as $plan)
                            <div class="group px-5 py-4 hover:bg-slate-50/50 transition-colors">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $plan->job_type == 'PM' ? 'bg-emerald-100 text-emerald-700' : ($plan->job_type == 'BS' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                                {{ $plan->job_type }}
                                            </span>
                                            @if($plan->department)
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black tracking-wider {{ $plan->department == 'RENTAL' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                {{ $plan->department }}
                                            </span>
                                            @endif
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-600">
                                                {{ ucfirst($plan->status) }}
                                            </span>
                                        </div>

                                        <div class="mt-2.5">
                                            <p class="text-base font-bold text-slate-900">{{ $plan->customer }}</p>
                                            <div class="mt-1 flex items-center gap-1.5 text-sm text-slate-500">
                                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                                {{ $plan->location }}
                                            </div>
                                        </div>

                                        <div class="mt-3 flex flex-wrap items-center gap-3">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 ring-2 ring-white">
                                                    {{ substr($plan->mechanic->name, 0, 2) }}
                                                </div>
                                                <span class="text-xs font-bold text-slate-700">{{ $plan->mechanic->name
                                                    }}</span>
                                            </div>

                                            @if ($plan->partner)
                                            <div class="flex items-center gap-1.5 text-slate-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-600 ring-2 ring-white">
                                                    {{ substr($plan->partner->name, 0, 2) }}
                                                </div>
                                                <span class="text-xs font-medium text-slate-600">{{ $plan->partner->name
                                                    }}</span>
                                            </div>
                                            @endif
                                        </div>

                                        @if ($plan->notes)
                                        <div
                                            class="mt-3 rounded-xl bg-amber-50 px-3 py-2 border border-amber-100/50 text-xs text-amber-800">
                                            <span class="font-bold">Catatan:</span> {{ $plan->notes }}
                                        </div>
                                        @endif
                                    </div>

                                    @if ($canManagePlanning)
                                    <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <form action="{{ route('calendar.plannings.destroy', $plan) }}" method="POST"
                                            onsubmit="return confirm('Hapus planning kerja ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-700 hover:bg-red-100 transition-colors">
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
                            Planning bulan ini masih kosong. Kalendernya bersih, tapi jangan sampai pekerjaan ikut
                            menghilang.
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: JADWAL PIKET -->
    @if($canViewPiket ?? false)
    <div id="tab-piket" style="display: none;" class="space-y-6 transition-opacity duration-300">
        <div class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between mb-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600">Kalender</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Smart Planning Piket
                    </h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Jadwal khusus piket hari Sabtu untuk RENTAL FIELD. Sistem merekomendasikan mekanik berdasarkan
                        <b>Hutang Piket</b> dan riwayat terlama tidak piket.
                    </p>
                </div>
            </div>

            <div class="space-y-6">
                @foreach($saturdays as $saturday)
                @php
                $dateObj = \Carbon\Carbon::parse($saturday)->locale('id');
                $piketForDate = $pikets->get($saturday, collect());
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 flex-col items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <span class="text-[10px] font-bold uppercase leading-none">{{ $dateObj->isoFormat('MMM')
                                    }}</span>
                                <span class="mt-0.5 text-lg font-black leading-none">{{ $dateObj->format('d') }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">{{ $dateObj->isoFormat('dddd, D MMMM YYYY')
                                    }}</h3>
                                <p class="text-xs font-medium text-slate-500">{{ $piketForDate->count() }} mekanik
                                    ditugaskan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Piket List -->
                    @if($piketForDate->isNotEmpty())
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 mb-4">
                        @foreach($piketForDate as $pkt)
                        <div
                            class="rounded-xl border {{ $pkt->status === 'jalan' ? 'border-blue-200 bg-blue-50' : 'border-rose-200 bg-rose-50' }} p-4 relative group">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 flex-shrink-0 rounded-full bg-white flex items-center justify-center font-bold {{ $pkt->status === 'jalan' ? 'text-blue-600' : 'text-rose-600' }} shadow-sm">
                                    {{ strtoupper(substr($pkt->user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-slate-900">{{ $pkt->user->name }}</p>
                                    <p
                                        class="text-[10px] font-black tracking-wider uppercase {{ $pkt->status === 'jalan' ? 'text-blue-600' : 'text-rose-600' }}">
                                        STATUS: {{ $pkt->status }}</p>
                                </div>
                            </div>
                            @if($canManagePlanning)
                            <form action="{{ url('calendar/piket/'.$pkt->id) }}" method="POST"
                                onsubmit="return confirm('Hapus jadwal piket ini?');"
                                class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-1.5 text-slate-400 hover:text-red-600 transition-colors bg-white rounded-md shadow-sm border border-slate-200"
                                    title="Hapus Piket">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-slate-500 mb-4 italic">Belum ada mekanik yang ditugaskan untuk piket hari
                        ini.</p>
                    @endif

                    <!-- Form Add Piket -->
                    @if($canManagePlanning)
                    <form action="{{ url('calendar/piket') }}" method="POST"
                        class="mt-4 flex flex-col lg:flex-row gap-3 items-end bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                        @csrf
                        <input type="hidden" name="date" value="{{ $saturday }}">

                        <div class="flex-1 w-full">
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Mekanik
                                (Rekomendasi Cerdas)</label>
                            <select name="user_id" required
                                class="block w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                <option value="">-- Pilih Rekomendasi Teratas --</option>
                                @foreach($recommendedMechanics as $rm)
                                <option value="{{ $rm->id }}">
                                    {{ $rm->name }}
                                    @if($rm->piket_priority == 1)
                                    (⭐ PRIORITAS: HUTANG PIKET)
                                    @else
                                    (Terakhir Jalan: {{ $rm->last_piket_date == '2000-01-01' ? 'Belum Pernah' :
                                    \Carbon\Carbon::parse($rm->last_piket_date)->format('d M Y') }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full lg:w-48">
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Kehadiran</label>
                            <select name="status" required
                                class="block w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                <option value="jalan">JALAN (Bisa Piket)</option>
                                <option value="berhalangan">BERHALANGAN</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full lg:w-auto rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700 shadow-sm transition-colors mt-2 lg:mt-0">
                            + Tambah Piket
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>

<script>
    // Tab Controller Script
    function switchTab(tab) {
        document.getElementById('tab-planning').style.display = tab === 'planning' ? 'block' : 'none';
        var tabPiket = document.getElementById('tab-piket');
        if (tabPiket) tabPiket.style.display = tab === 'piket' ? 'block' : 'none';

        // Update Active Button styling
        document.getElementById('btn-planning').className = tab === 'planning'
            ? 'whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm border-blue-600 text-blue-600 transition-colors'
            : 'whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors';

        var btnPiket = document.getElementById('btn-piket');
        if(btnPiket) {
            btnPiket.className = tab === 'piket'
                ? 'whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm border-emerald-600 text-emerald-600 transition-colors'
                : 'whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors';
        }
    }

    // Existing Alpine Planning logic
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
