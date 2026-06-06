{{-- PATH FILE: resources/views/reminders/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl space-y-6 pb-24">
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
                        Halaman ini disiapkan sebagai pusat kontrol untuk pekerjaan yang perlu ditindaklanjuti,
                        seperti Waiting Part, RFU tertunda, follow-up customer, dan jadwal kerja yang mendekati tenggat.
                    </p>
                </div>

                <div class="rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:min-w-64">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status modul</p>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-900">Tahap 1</p>
                            <p class="text-xs text-slate-500">Tampilan awal siap</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 104 0M9 5a2 2 0 114 0m-6 8l2 2 4-4" />
                </svg>
            </div>
            <h2 class="mt-4 text-base font-black text-slate-900">Tugas Tertunda</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Nanti menampilkan pekerjaan yang belum selesai, perlu tindak lanjut, atau menunggu update dari mekanik.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="mt-4 text-base font-black text-slate-900">Deadline & Jadwal</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Disiapkan untuk RFU date, jadwal planning, dan pekerjaan yang mendekati batas waktu pengerjaan.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 19h14.14c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.198 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="mt-4 text-base font-black text-slate-900">Prioritas Merah</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Nanti digunakan untuk menandai pengingat penting yang perlu segera dibereskan oleh user terkait.
            </p>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Rencana Aktivasi</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Tahap berikutnya dibuat bertahap</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Tampilan ini belum mengambil data produksi. Tahap berikutnya baru menghubungkan pengingat ke data existing
                    dengan isolasi user dan role agar tidak bocor antar mekanik, koordinator, sect head, admin, dan super admin.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                View-only
            </span>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-black text-slate-900">Tahap 2</p>
                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Buat controller khusus Reminder dan tampilkan data dari sumber existing tanpa mengubah modul stabil.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-black text-slate-900">Tahap 3</p>
                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Tambahkan badge merah angka di menu Pengingat setelah halaman dan sumber data sudah stabil.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
