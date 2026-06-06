@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-red-600">Pengingat Operasional</p>
        <div class="mt-3 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Pengingat Pekerjaan</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Daftar pekerjaan yang perlu perhatian. Mekanik hanya melihat pekerjaan sendiri. Koordinator dan sect head melihat pekerjaan satu department. Admin dan super admin melihat semua.
                </p>
            </div>
            <a href="{{ route('reminders.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-100">
                Reset Filter
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
        <a href="{{ route('reminders.index') }}" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:bg-slate-50">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($summary['total'] ?? 0) }}</p>
        </a>
        <a href="{{ route('reminders.index', ['filter' => 'overdue']) }}" class="rounded-3xl border border-red-200 bg-red-50 p-4 shadow-sm transition hover:bg-red-100">
            <p class="text-xs font-bold uppercase tracking-wide text-red-500">Overdue</p>
            <p class="mt-2 text-3xl font-black text-red-700">{{ number_format($summary['overdue'] ?? 0) }}</p>
        </a>
        <a href="{{ route('reminders.index', ['filter' => 'breakdown']) }}" class="rounded-3xl border border-rose-200 bg-rose-50 p-4 shadow-sm transition hover:bg-rose-100">
            <p class="text-xs font-bold uppercase tracking-wide text-rose-500">Breakdown</p>
            <p class="mt-2 text-3xl font-black text-rose-700">{{ number_format($summary['breakdown'] ?? 0) }}</p>
        </a>
        <a href="{{ route('reminders.index', ['filter' => 'waiting_part']) }}" class="rounded-3xl border border-amber-200 bg-amber-50 p-4 shadow-sm transition hover:bg-amber-100">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-500">Waiting Part</p>
            <p class="mt-2 text-3xl font-black text-amber-700">{{ number_format($summary['waiting_part'] ?? 0) }}</p>
        </a>
        <a href="{{ route('reminders.index', ['filter' => 'monitoring']) }}" class="rounded-3xl border border-blue-200 bg-blue-50 p-4 shadow-sm transition hover:bg-blue-100">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-500">Monitoring</p>
            <p class="mt-2 text-3xl font-black text-blue-700">{{ number_format($summary['monitoring'] ?? 0) }}</p>
        </a>
        <div class="rounded-3xl border border-red-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-red-500">Urgent</p>
            <p class="mt-2 text-3xl font-black text-red-700">{{ number_format($summary['urgent'] ?? 0) }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-500">High</p>
            <p class="mt-2 text-3xl font-black text-amber-700">{{ number_format($summary['high'] ?? 0) }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reminders.index') }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ blank($filter) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Semua</a>
            <a href="{{ route('reminders.index', ['filter' => 'overdue']) }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ $filter === 'overdue' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">Overdue</a>
            <a href="{{ route('reminders.index', ['filter' => 'breakdown']) }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ $filter === 'breakdown' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">Breakdown</a>
            <a href="{{ route('reminders.index', ['filter' => 'waiting_part']) }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ $filter === 'waiting_part' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">Waiting Part</a>
            <a href="{{ route('reminders.index', ['filter' => 'monitoring']) }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ $filter === 'monitoring' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">Monitoring</a>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($reminders as $reminder)
            @php
                $priority = $reminder['priority'] ?? 'normal';
                $priorityClass = 'border-blue-200 bg-blue-50 text-blue-700';
                if ($priority === 'urgent') {
                    $priorityClass = 'border-red-200 bg-red-50 text-red-700';
                } elseif ($priority === 'high') {
                    $priorityClass = 'border-amber-200 bg-amber-50 text-amber-700';
                }
            @endphp
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-2.5 py-1 text-xs font-black uppercase {{ $priorityClass }}">{{ strtoupper($priority) }}</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $reminder['status_unit'] }}</span>
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">{{ $reminder['department'] }}</span>
                        </div>

                        <h2 class="mt-3 text-lg font-black text-slate-950">{{ $reminder['title'] }}</h2>
                        <div class="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-4">
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Serial Number</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $reminder['serial_number'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Customer</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $reminder['customer'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Lokasi</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $reminder['location'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">PIC</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $reminder['pic'] }}</p>
                            </div>
                        </div>
                        <div class="mt-3 text-xs font-semibold text-slate-500">
                            Problem: {{ $reminder['problem_date'] ? $reminder['problem_date']->format('d M Y') : '-' }} · Update: {{ $reminder['updated_at'] ? $reminder['updated_at']->diffForHumans() : '-' }}
                        </div>
                    </div>
                    <div class="lg:w-44">
                        <a href="{{ $reminder['url'] }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white transition hover:bg-blue-700">
                            Lihat Job
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <p class="text-lg font-black text-slate-800">Tidak ada pengingat aktif.</p>
                <p class="mt-2 text-sm text-slate-500">Semua pekerjaan dalam scope Anda sedang aman. Ini langka, nikmati sebentar.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
