@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">Admin Settings</p>
        <h1 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Payment Settings</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            Atur metode pembayaran, nomor tujuan, nama penerima, WhatsApp admin, catatan pembayaran, dan gambar QRIS yang tampil di halaman pembayaran user.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('payment-settings.update') }}" enctype="multipart/form-data" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Metode Pembayaran</label>
                <input type="text" name="payment_method" value="{{ old('payment_method', $setting->payment_method) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100" placeholder="QRIS / Transfer BCA / Transfer Manual" required>
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Nomor Tujuan</label>
                <input type="text" name="receiver_number" value="{{ old('receiver_number', $setting->receiver_number) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100" placeholder="Nomor rekening / ID merchant / nomor tujuan">
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Atas Nama</label>
                <input type="text" name="receiver_name" value="{{ old('receiver_name', $setting->receiver_name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100" placeholder="Nama penerima pembayaran">
            </div>

            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">WhatsApp Admin</label>
                <input type="text" name="admin_whatsapp" value="{{ old('admin_whatsapp', $setting->admin_whatsapp) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100" placeholder="628xxxxxxxxxx">
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Catatan Pembayaran</label>
            <textarea name="payment_note" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100" placeholder="Instruksi tambahan untuk user">{{ old('payment_note', $setting->payment_note) }}</textarea>
        </div>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="flex-1">
                    <p class="text-sm font-black text-slate-900">QRIS / Gambar Pembayaran</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>

                    <div class="mt-4">
                        <input type="file" name="qris_image" accept="image/png,image/jpeg,image/webp" class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white">
                    </div>

                    <label class="mt-4 flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input type="checkbox" name="is_qris_active" value="1" {{ old('is_qris_active', $setting->is_qris_active) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Tampilkan QRIS di halaman pembayaran
                    </label>

                    @if($setting->qris_image_path)
                        <label class="mt-3 flex items-center gap-2 text-sm font-bold text-red-700">
                            <input type="checkbox" name="remove_qris" value="1" class="rounded border-red-300 text-red-600 focus:ring-red-500">
                            Hapus QRIS saat ini
                        </label>
                    @endif
                </div>

                <div class="w-full md:w-56">
                    @if($setting->qris_image_path)
                        <img src="{{ $setting->qrisUrl() }}" alt="QRIS Payment" class="w-full rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                    @else
                        <div class="flex h-56 w-full items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white text-center text-xs font-bold text-slate-400">
                            Belum ada QRIS
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Kembali</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-blue-700">Simpan Payment Settings</button>
        </div>
    </form>
</div>
@endsection
