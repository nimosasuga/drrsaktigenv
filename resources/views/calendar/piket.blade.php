<!--
|--------------------------------------------------------------------------
| PATH FILE:
| resources/views/calendar/piket.blade.php
|--------------------------------------------------------------------------
-->
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 pb-24">
    <a href="{{ route('calendar.index', ['month' => $month, 'year' => $year]) }}" class="inline-flex rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 shadow-sm hover:bg-slate-50">← Calendar</a>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-sm">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-800 shadow-sm">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm">
            <p class="font-black">Input belum valid.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="relative overflow-hidden rounded-[2rem] border border-emerald-200 bg-white p-6 shadow-xl shadow-emerald-100/70 sm:p-8">
        <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-emerald-100 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-600">Rental Field</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Jadwal Piket Sabtu</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Kelola piket Sabtu. Histori status tidak ada kerjaan tetap tersimpan untuk audit.</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <p class="text-[11px] font-black uppercase tracking-wide text-emerald-700">Sabtu Bulan Ini</p>
                <p class="mt-1 text-2xl font-black text-emerald-950">{{ count($saturdays) }}</p>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('calendar.piket') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3 sm:items-end">
        <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-500">Bulan</label>
            <select name="month" class="mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 text-sm shadow-sm">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ Carbon\Carbon::create(null, $i, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-500">Tahun</label>
            <select name="year" class="mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 text-sm shadow-sm">
                @for ($i = now()->year - 2; $i <= now()->year + 2; $i++)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
            <a href="{{ route('calendar.piket') }}" class="rounded-xl bg-white px-4 py-2 text-center text-sm font-bold text-slate-700 ring-1 ring-slate-300">Reset</a>
        </div>
    </form>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <p class="font-black">Alur tidak ada kerjaan:</p>
        <p class="mt-1">Status Sabtu lama menjadi <b>tidak_ada_kerjaan</b>, lalu jadwal baru dibuat pada Sabtu berikutnya dengan mekanik yang sama.</p>
    </div>

    <div class="space-y-5">
        @foreach($saturdays as $saturday)
            @php
                $dateObj = Carbon\Carbon::parse($saturday)->locale('id');
                $piketForDate = $pikets->get($saturday, collect());
                $activePiketCount = $piketForDate->whereIn('status', ['jalan', 'berhalangan'])->count();
                $noWorkCount = $piketForDate->where('status', 'tidak_ada_kerjaan')->count();
            @endphp

            <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 flex-col items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <span class="text-[10px] font-black uppercase">{{ $dateObj->isoFormat('MMM') }}</span>
                            <span class="text-lg font-black">{{ $dateObj->format('d') }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900">{{ $dateObj->isoFormat('dddd, D MMMM YYYY') }}</h3>
                            <p class="text-xs font-bold text-slate-500">{{ $activePiketCount }} jadwal aktif @if($noWorkCount > 0) · {{ $noWorkCount }} tidak ada kerjaan @endif</p>
                        </div>
                    </div>
                </div>

                @if($piketForDate->isNotEmpty())
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($piketForDate as $pkt)
                            @php
                                $cardClass = match($pkt->status) {
                                    'jalan' => 'border-blue-200 bg-blue-50 text-blue-700',
                                    'berhalangan' => 'border-rose-200 bg-rose-50 text-rose-700',
                                    'tidak_ada_kerjaan' => 'border-amber-200 bg-amber-50 text-amber-700',
                                    default => 'border-slate-200 bg-white text-slate-700',
                                };
                                $label = match($pkt->status) {
                                    'jalan' => 'SIAP PIKET',
                                    'berhalangan' => 'BERHALANGAN',
                                    'tidak_ada_kerjaan' => 'TIDAK ADA KERJAAN',
                                    default => strtoupper((string) $pkt->status),
                                };
                            @endphp
                            <div class="rounded-2xl border p-4 {{ $cardClass }}">
                                <p class="text-sm font-black text-slate-950">{{ optional($pkt->user)->name ?? 'User tidak ditemukan' }}</p>
                                <p class="mt-1 text-[10px] font-black uppercase tracking-wider">{{ $label }}</p>

                                @if($pkt->status === 'tidak_ada_kerjaan')
                                    <div class="mt-3 rounded-xl border border-amber-200 bg-white/70 px-3 py-2 text-xs font-semibold text-amber-800">Histori tetap disimpan. Jadwal pengganti ada pada Sabtu berikutnya.</div>
                                @endif

                                @if($canManagePlanning)
                                    <div class="mt-4 grid grid-cols-1 gap-2">
                                        @if($pkt->status === 'jalan')
                                            <form action="{{ route('calendar.piket.defer', $pkt) }}" method="POST" onsubmit="return confirm('Sabtu ini tidak ada pekerjaan?');">
                                                @csrf
                                                @method('PATCH')
                                                <button class="w-full rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs font-black text-amber-700 hover:bg-amber-50">Tidak Ada Kerjaan → Sabtu Depan</button>
                                            </form>
                                        @endif

                                        <form action="{{ route('calendar.piket.destroy', $pkt) }}" method="POST" onsubmit="return confirm('Hapus jadwal piket ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-black text-red-700 hover:bg-red-50">Hapus Piket</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center text-sm font-bold text-slate-500">Belum ada mekanik yang ditugaskan.</div>
                @endif

                @if($canManagePlanning)
                    <form action="{{ route('calendar.piket.store') }}" method="POST" class="mt-4 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-12 lg:items-end">
                        @csrf
                        <input type="hidden" name="date" value="{{ $saturday }}">
                        <div class="lg:col-span-7">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-wider text-slate-500">Mekanik Rekomendasi</label>
                            <select name="user_id" required class="block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm">
                                <option value="">Pilih mekanik</option>
                                @foreach($recommendedMechanics as $rm)
                                    <option value="{{ $rm->id }}">{{ $rm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-3">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-wider text-slate-500">Status</label>
                            <select name="status" required class="block w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm">
                                <option value="jalan">JALAN / Siap Piket</option>
                                <option value="berhalangan">BERHALANGAN</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <button class="w-full rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">Tambah</button>
                        </div>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
