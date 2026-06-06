{{-- PATH FILE: resources/views/reminders/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 pb-24">
    <section class="overflow-hidden rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50 via-white to-slate-50 shadow-sm">
        <div class="px-5 py-6 sm:px-7 sm:py-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-amber-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Pengingat Operasional
                    </div>

                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        Pusat Pengingat Pekerjaan
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Menampilkan Update Job yang perlu ditindaklanjuti: Waiting Part, Breakdown, RFU kosong,
                        problem lama, dan rekomendasi sparepart yang belum action.
                    </p>
                </div>

                <div class="rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:min-w-64">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total pengingat unik</p>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-slate-950">{{ number_format($totalUniqueJobs) }}</p>
                            <p class="text-xs text-slate-500">{{ $scopeLabel }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('reminders.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-4">
            <div>
                <label for="type" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Jenis Pengingat</label>
                <select id="type" name="type" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-200">
                    <option value="all" {{ $selectedType === 'all' ? 'selected' : '' }}>Semua jenis</option>
                    @foreach($typeOptions as $key => $option)
                        <option value="{{ $key }}" {{ $selectedType === $key ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>

            @if($canSeeAllDepartments)
            <div>
                <label for="department" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Department</label>
                <select id="department" name="department" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-200">
                    <option value="" {{ $selectedDepartment === '' ? 'selected' : '' }}>Semua department</option>
                    @foreach($departmentOptions as $department)
                        <option value="{{ $department }}" {{ $selectedDepartment === $department ? 'selected' : '' }}>{{ $department }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($canSeeTeam)
            <div>
                <label for="user_id" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">User</label>
                <select id="user_id" name="user_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-200">
                    <option value="0" {{ (int) $selectedUserId === 0 ? 'selected' : '' }}>Semua user yang boleh dilihat</option>
                    @foreach($users as $filterUser)
                        <option value="{{ $filterUser->id }}" {{ (int) $selectedUserId === (int) $filterUser->id ? 'selected' : '' }}>
                            {{ $filterUser->name }} — {{ strtoupper((string) $filterUser->status_user) }}{{ $filterUser->department ? ' / ' . $filterUser->department : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-slate-800">
                    Terapkan Filter
                </button>
                <a href="{{ route('reminders.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 shadow-sm hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach($typeOptions as $key => $option)
            @php $count = $groups[$key]['count'] ?? 0; @endphp
            <a href="{{ route('reminders.index', array_filter(['type' => $key, 'department' => $selectedDepartment, 'user_id' => $selectedUserId ?: null])) }}"
                class="rounded-3xl border {{ $selectedType === $key ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }} p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-500">{{ $option['short_label'] }}</p>
                    <span class="rounded-full bg-slate-950 px-2.5 py-1 text-xs font-black text-white">{{ number_format($count) }}</span>
                </div>
                <h2 class="mt-3 text-sm font-black text-slate-950">{{ $option['label'] }}</h2>
                <p class="mt-2 line-clamp-3 text-xs leading-5 text-slate-500">{{ $option['description'] }}</p>
            </a>
        @endforeach
    </section>

    <section class="space-y-5">
        @forelse($groups as $typeKey => $group)
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-amber-600">{{ $group['priority'] }} Priority</p>
                            <h2 class="mt-1 text-lg font-black text-slate-950">{{ $group['label'] }}</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $group['description'] }}</p>
                        </div>
                        <span class="inline-flex w-fit items-center rounded-full bg-white px-3 py-1 text-xs font-black text-slate-700 shadow-sm">
                            {{ number_format($group['count']) }} data
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($group['jobs'] as $job)
                        <article class="p-5 sm:p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-slate-600">#{{ $job->id }}</span>
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-blue-700">{{ $job->department ?: '-' }}</span>
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-amber-700">{{ $job->status_unit ?: '-' }}</span>
                                    </div>

                                    <h3 class="mt-3 truncate text-base font-black text-slate-950">
                                        {{ $job->customer ?: '-' }} — {{ $job->location ?: '-' }}
                                    </h3>

                                    <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-2 xl:grid-cols-4">
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">S/N</p>
                                            <p class="font-bold text-slate-800">{{ $job->serial_number ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">PIC</p>
                                            <p class="font-bold text-slate-800">{{ $job->pic ?: ($job->user?->name ?: '-') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Work Date</p>
                                            <p class="font-bold text-slate-800">{{ $job->work_date ? $job->work_date->format('d/m/Y') : '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">RFU Date</p>
                                            <p class="font-bold text-slate-800">{{ $job->rfu_date ? $job->rfu_date->format('d/m/Y') : '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-3 rounded-2xl bg-slate-50 p-3 text-sm leading-6 text-slate-600">
                                        <span class="font-black text-slate-800">Problem:</span> {{ $job->problem ?: '-' }}
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-col gap-2 sm:flex-row lg:flex-col">
                                    <a href="{{ route('update-jobs.show', $job->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white shadow-sm hover:bg-slate-800">
                                        Detail Job
                                    </a>
                                    <a href="{{ route('update-jobs.edit', $job->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm hover:bg-slate-50">
                                        Edit Job
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-6 text-center">
                            <p class="text-sm font-bold text-slate-500">Tidak ada data untuk kategori ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-sm font-bold text-slate-500">Belum ada pengingat yang perlu ditampilkan.</p>
            </div>
        @endforelse
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Aturan Isolasi</p>
        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                <span class="font-black text-slate-900">Mekanik</span><br>
                Hanya melihat pengingat miliknya sendiri.
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                <span class="font-black text-slate-900">Koordinator / Sect Head</span><br>
                Bisa melihat list user dan pengingat dalam department yang sama.
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                <span class="font-black text-slate-900">Admin / Super Admin</span><br>
                Bisa melihat seluruh department dan seluruh user.
            </div>
        </div>
    </section>
</div>
@endsection
