<!-- resources/views/admin/subscriptions.blade.php -->
@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi Pembayaran Lisensi</h1>
        <p class="mt-1 text-sm text-slate-500">Daftar pengguna yang telah mentransfer pembayaran dan menunggu
            persetujuan Anda.</p>
    </div>
</div>

<!-- Alert Sukses -->
@if(session('success'))
<div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-r-md shadow-sm">
    <div class="flex items-center">
        <svg class="h-5 w-5 text-emerald-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
    </div>
</div>
@endif

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden ring-1 ring-slate-900/5">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Pengguna
                    </th>
                    <th scope="col"
                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Paket &
                        Role</th>
                    <th scope="col"
                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Metode &
                        Nominal</th>
                    <th scope="col"
                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu
                        Submit</th>
                    <th scope="col"
                        class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @forelse($payments as $payment)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div
                                class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                {{ substr($payment->user->name, 0, 1) }}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-slate-900">{{ $payment->user->name }}</div>
                                <div class="text-sm text-slate-500">NRPP: {{ $payment->user->nrpp }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-900 font-medium">{{ $payment->package->package_name }}</div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mt-0.5">{{
                            $payment->user->status_user }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $payment->payment_method }}
                        </span>
                        <div class="text-sm font-bold text-slate-900 mt-1">Rp{{ number_format($payment->amount, 0, ',',
                            '.') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                        {{ $payment->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <form method="POST" action="{{ route('admin.subscriptions.approve', $payment->id) }}"
                            onsubmit="return confirm('Apakah Anda yakin dana sudah masuk dan ingin mengaktifkan lisensi ini?');">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Approve
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-slate-500 font-medium">Belum ada antrean pembayaran saat ini.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
