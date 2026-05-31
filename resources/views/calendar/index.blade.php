<!--
|--------------------------------------------------------------------------
| PATH FILE:
| resources/views/calendar/index.blade.php
|--------------------------------------------------------------------------
-->
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 pb-24">

    @if (session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-sm">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-800 shadow-sm">
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm">
        <p class="font-black">Input belum valid.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="grid grid-cols-2 gap-2">
            <button onclick="switchTab('planning')" id="btn-planning"
                class="rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-sm transition-colors">
                Planning Kerja
            </button>
            @if($canViewPiket ?? false)
            <button onclick="switchTab('piket')" id="btn-piket"
                class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-600 transition-colors hover:bg-slate-200">
                Jadwal Piket
            </button>
            @endif
        </div>
    </div>

    <div id="tab-planning" class="block space-y-6 transition-opacity duration-300">
        <div class="rounded-3xl border border-blue-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Kalender Operasional</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Planning Kerja Mekanik</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Kelola rencana kerja harian mekanik dan partner. Data mengikuti Department Scope user login.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Total Planning</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $plannings->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Periode</p>
                        <p class="mt-1 text-xl font-black text-blue-900">{{ Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('calendar.index') }}"
                class="mt-8 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                <div>
                    <label for="month" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Bulan</label>
                    <select name="month" id="month" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ Carbon\Carbon::create(null, $i, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label for="year" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Tahun</label>
                    <select name="year" id="year" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @for ($i = now()->year - 2; $i <= now()->year + 2; $i++)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label for="mechanic_id" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Mekanik/Partner</label>
                    <select name="mechanic_id" id="mechanic_id" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Mekanik</option>
                        @foreach ($mechanics as $mech)
                        <option value="{{ $mech->id }}" {{ request('mechanic_id') == $mech->id ? 'selected' : '' }}>{{ $mech->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="PLANNED" {{ strtoupper((string) request('status')) == 'PLANNED' ? 'selected' : '' }}>PLANNED</option>
                        <option value="DONE" {{ strtoupper((string) request('status')) == 'DONE' ? 'selected' : '' }}>DONE</option>
                        <option value="CANCELLED" {{ strtoupper((string) request('status')) == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-800">Filter</button>
                    <a href="{{ route('calendar.index') }}" class="rounded-xl bg-white px-4 py-2 text-center text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">Reset</a>
                </div>
            </form>

            @if ($canManagePlanning)
            <div x-data="calendarPlanningForm({{ json_encode($customerLocations) }})" class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-1">
                    <h2 class="text-base font-black text-slate-900">Buat Planning Baru</h2>
                    <p class="text-xs text-slate-500">Pastikan customer dan lokasi sesuai department mekanik.</p>
                </div>

                <form method="POST" action="{{ route('calendar.plannings.store') }}" class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6 sm:items-end">
                    @csrf

                    <div class="sm:col-span-1">
                        <label for="date" class="block text-sm font-medium leading-6 text-slate-900">Tanggal</label>
                        <input type="date" name="date" id="date" required value="{{ old('date', sprintf('%04d-%02d-01', $year, $month)) }}" class="mt-1 block w-full rounded-xl border-0 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="form_mechanic_id" class="block text-sm font-medium leading-6 text-slate-900">Mekanik</label>
                        <select id="form_mechanic_id" name="mechanic_id" required class="mt-1 block w-full rounded-xl border-0 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                            <option value="">Pilih Mekanik...</option>
                            @foreach ($mechanics as $mechanic)
                            <option value="{{ $mechanic->id }}" {{ old('mechanic_id') == $mechanic->id ? 'selected' : '' }}>{{ $mechanic->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="partner_id" class="block text-sm font-medium leading-6 text-slate-900">Partner <span class="text-slate-400">(Opsional)</span></label>
                        <select id="partner_id" name="partner_id" class="mt-1 block w-full rounded-xl border-0 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                            <option value="">Tidak ada partner...</option>
                            @foreach ($mechanics as $mechanic)
                            <option value="{{ $mechanic->id }}" {{ old('partner_id') == $mechanic->id ? 'selected' : '' }}>{{ $mechanic->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="job_type" class="block text-sm font-medium leading-6 text-slate-900">Jenis</label>
                        <select id="job_type" name="job_type" required class="mt-1 block w-full rounded-xl border-0 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                            <option value="PM" {{ old('job_type') == 'PM' ? 'selected' : '' }}>PM</option>
                            <option value="BS" {{ old('job_type') == 'BS' ? 'selected' : '' }}>BS</option>
                            <option value="SCHEDULE" {{ old('job_type') == 'SCHEDULE' ? 'selected' : '' }}>SCHEDULE</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="customer" class="block text-sm font-medium leading-6 text-slate-900">Customer</label>
                        <select id="customer" name="customer" required x-model="selectedCustomer" @change="selectedLocation = ''" class="mt-1 block w-full rounded-xl border-0 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                            <option value="">Pilih Customer...</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer }}">{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="location" class="block text-sm font-medium leading-6 text-slate-900">Lokasi</label>
                        <select id="location" name="location" required x-model="selectedLocation" class="mt-1 block w-full rounded-xl border-0 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                            <option value="">Pilih Lokasi...</option>
                            <template x-for="loc in availableLocations" :key="loc">
                                <option :value="loc" x-text="loc"></option>
                            </template>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-sm font-medium leading-6 text-slate-900">Catatan</label>
                        <input type="text" name="notes" id="notes" value="{{ old('notes') }}" placeholder="Contoh: Bawa alat khusus..." class="mt-1 block w-full rounded-xl border-0 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                    </div>

                    <div class="sm:col-span-6 flex justify-end">
                        <button type="submit" class="w-full rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-blue-500 sm:w-auto">Simpan Planning</button>
                    </div>
                </form>
            </div>
            @endif

            <div class="mt-10 space-y-6">
                @forelse ($groupedPlannings as $date => $plans)
                @php
                $dateObj = \Carbon\Carbon::parse($date);
                $isToday = $dateObj->isToday();
                $isWeekend = $dateObj->isWeekend();
                @endphp
                <div class="relative overflow-hidden rounded-2xl border {{ $isToday ? 'border-blue-300 shadow-md ring-1 ring-blue-100' : 'border-slate-200 shadow-sm' }} bg-white">
                    @if($isToday)
                    <div class="absolute right-0 top-0 rounded-bl-xl bg-blue-600 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">HARI INI</div>
                    @endif

                    <div class="border-b {{ $isToday ? 'border-blue-100 bg-blue-50/50' : 'border-slate-100 bg-slate-50/50' }} px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 flex-col items-center justify-center rounded-xl {{ $isToday ? 'bg-blue-600 text-white' : ($isWeekend ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                                <span class="text-[10px] font-bold uppercase leading-none">{{ $dateObj->translatedFormat('M') }}</span>
                                <span class="mt-0.5 text-lg font-black leading-none">{{ $dateObj->format('d') }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold {{ $isToday ? 'text-blue-950' : ($isWeekend ? 'text-rose-950' : 'text-slate-900') }}">{{ $dateObj->translatedFormat('l') }}</h3>
                                <p class="text-xs font-medium text-slate-500">{{ $plans->count() }} pekerjaan dijadwalkan</p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($plans as $plan)
                        @php
                        $statusClass = match($plan->status) {
                            'DONE' => 'bg-emerald-100 text-emerald-700',
                            'CANCELLED' => 'bg-red-100 text-red-700',
                            default => 'bg-blue-100 text-blue-700',
                        };
                        @endphp
                        <div class="group px-5 py-4 transition-colors hover:bg-slate-50/50">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $plan->job_type == 'PM' ? 'bg-emerald-100 text-emerald-700' : ($plan->job_type == 'BS' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">{{ $plan->job_type }}</span>
                                        @if($plan->department)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black tracking-wider {{ $plan->department == 'RENTAL' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $plan->department }}</span>
                                        @endif
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $statusClass }}">{{ $plan->status }}</span>
                                    </div>

                                    <div class="mt-2.5">
                                        <p class="text-base font-bold text-slate-900">{{ $plan->customer }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $plan->location }}</p>
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                                        <span class="rounded-full bg-blue-50 px-3 py-1 font-bold text-blue-700">Mekanik: {{ optional($plan->mechanic)->name ?? 'User tidak ditemukan' }}</span>
                                        @if ($plan->partner)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 font-bold text-slate-600">Partner: {{ $plan->partner->name }}</span>
                                        @endif
                                    </div>

                                    @if ($plan->note)
                                    <div class="mt-3 rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                        <span class="font-bold">Catatan:</span> {{ $plan->note }}
                                    </div>
                                    @endif
                                </div>

                                @if ($canManagePlanning)
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-64">
                                    <form action="{{ route('calendar.plannings.status', $plan) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="block w-full rounded-xl border-slate-300 bg-white text-xs font-bold shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="PLANNED" {{ $plan->status == 'PLANNED' ? 'selected' : '' }}>PLANNED</option>
                                            <option value="DONE" {{ $plan->status == 'DONE' ? 'selected' : '' }}>DONE</option>
                                            <option value="CANCELLED" {{ $plan->status == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                                        </select>
                                    </form>
                                    <form action="{{ route('calendar.plannings.destroy', $plan) }}" method="POST" onsubmit="return confirm('Hapus planning kerja ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-700 transition-colors hover:bg-red-100">Hapus</button>
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
                    <p class="mt-2 text-sm text-slate-500">Planning bulan ini masih kosong. Kalender bersih, tapi jangan sampai pekerjaan ikut menghilang.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($canViewPiket ?? false)
    <div id="tab-piket" style="display: none;" class="space-y-6 transition-opacity duration-300">
        <div class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600">Rental Field</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Smart Planning Piket Sabtu</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Jadwal piket khusus Sabtu. Jika Sabtu tidak ada pekerjaan, koordinator bisa menggeser jadwal ke Sabtu berikutnya dengan mekanik yang sama.
                    </p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-700">Sabtu Bulan Ini</p>
                    <p class="mt-1 text-xl font-black text-emerald-900">{{ count($saturdays) }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-black">Catatan alur:</p>
                <p class="mt-1">Tombol <b>Tidak Ada Kerjaan</b> tidak membuat status database baru. Jadwal lama langsung dipindahkan ke Sabtu berikutnya agar tetap aman tanpa migration.</p>
            </div>

            <div class="mt-6 space-y-6">
                @foreach($saturdays as $saturday)
                @php
                $dateObj = \Carbon\Carbon::parse($saturday)->locale('id');
                $piketForDate = $pikets->get($saturday, collect());
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:p-5">
                    <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 flex-col items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <span class="text-[10px] font-bold uppercase leading-none">{{ $dateObj->isoFormat('MMM') }}</span>
                                <span class="mt-0.5 text-lg font-black leading-none">{{ $dateObj->format('d') }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">{{ $dateObj->isoFormat('dddd, D MMMM YYYY') }}</h3>
                                <p class="text-xs font-medium text-slate-500">{{ $piketForDate->count() }} mekanik ditugaskan</p>
                            </div>
                        </div>
                    </div>

                    @if($piketForDate->isNotEmpty())
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($piketForDate as $pkt)
                        <div class="rounded-xl border {{ $pkt->status === 'jalan' ? 'border-blue-200 bg-blue-50' : 'border-rose-200 bg-rose-50' }} p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white font-bold {{ $pkt->status === 'jalan' ? 'text-blue-600' : 'text-rose-600' }} shadow-sm">
                                    {{ strtoupper(substr(optional($pkt->user)->name ?? 'NA', 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ optional($pkt->user)->name ?? 'User tidak ditemukan' }}</p>
                                    <p class="text-[10px] font-black uppercase tracking-wider {{ $pkt->status === 'jalan' ? 'text-blue-600' : 'text-rose-600' }}">{{ $pkt->status === 'jalan' ? 'SIAP PIKET' : 'BERHALANGAN' }}</p>
                                </div>
                            </div>

                            @if($canManagePlanning)
                            <div class="mt-4 grid grid-cols-1 gap-2">
                                @if($pkt->status === 'jalan')
                                <form action="{{ route('calendar.piket.defer', $pkt) }}" method="POST" onsubmit="return confirm('Sabtu ini tidak ada pekerjaan? Jadwal akan digeser ke Sabtu berikutnya dengan mekanik yang sama.');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs font-black text-amber-700 transition-colors hover:bg-amber-50">Tidak Ada Kerjaan → Sabtu Depan</button>
                                </form>
                                @endif

                                <form action="{{ route('calendar.piket.destroy', $pkt) }}" method="POST" onsubmit="return confirm('Hapus jadwal piket ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-black text-red-700 transition-colors hover:bg-red-50">Hapus Piket</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-500">
                        Belum ada mekanik yang ditugaskan untuk piket hari ini.
                    </div>
                    @endif

                    @if($canManagePlanning)
                    <form action="{{ route('calendar.piket.store') }}" method="POST" class="mt-4 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:grid-cols-12 lg:items-end">
                        @csrf
                        <input type="hidden" name="date" value="{{ $saturday }}">

                        <div class="lg:col-span-7">
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Mekanik Rekomendasi</label>
                            <select name="user_id" required class="block w-full rounded-xl border-slate-300 bg-slate-50 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Pilih mekanik...</option>
                                @foreach($recommendedMechanics as $rm)
                                <option value="{{ $rm->id }}">
                                    {{ $rm->name }}
                                    @if($rm->piket_priority == 1)
                                    (PRIORITAS: HUTANG PIKET)
                                    @else
                                    (Terakhir: {{ $rm->last_piket_date == '2000-01-01' ? 'Belum Pernah' : \Carbon\Carbon::parse($rm->last_piket_date)->format('d M Y') }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-3">
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</label>
                            <select name="status" required class="block w-full rounded-xl border-slate-300 bg-slate-50 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="jalan">JALAN / Siap Piket</option>
                                <option value="berhalangan">BERHALANGAN</option>
                            </select>
                        </div>

                        <div class="lg:col-span-2">
                            <button type="submit" class="w-full rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700">Tambah</button>
                        </div>
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
    function switchTab(tab) {
        document.getElementById('tab-planning').style.display = tab === 'planning' ? 'block' : 'none';
        var tabPiket = document.getElementById('tab-piket');
        if (tabPiket) tabPiket.style.display = tab === 'piket' ? 'block' : 'none';

        document.getElementById('btn-planning').className = tab === 'planning'
            ? 'rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-sm transition-colors'
            : 'rounded-2xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-600 transition-colors hover:bg-slate-200';

        var btnPiket = document.getElementById('btn-piket');
        if (btnPiket) {
            btnPiket.className = tab === 'piket'
                ? 'rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white shadow-sm transition-colors'
                : 'rounded-2xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-600 transition-colors hover:bg-slate-200';
        }
    }

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
