<!-- resources/views/admin/users/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Pengguna Baru</h1>
        <p class="mt-1 text-sm text-slate-500">Daftarkan akun baru ke dalam sistem DRR SAKTI.</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden ring-1 ring-slate-900/5 max-w-4xl">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="p-6 sm:p-8 space-y-8">

            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label for="nrpp" class="block text-sm font-medium text-slate-700">NRPP / ID Karyawan <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nrpp" id="nrpp" value="{{ old('nrpp') }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label for="status_user" class="block text-sm font-medium text-slate-700">Role Pengguna <span
                            class="text-red-500">*</span></label>
                    <select name="status_user" id="status_user" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors bg-white">
                        <option value="mekanik" {{ old('status_user')=='mekanik' ? 'selected' : '' }}>Mekanik</option>
                        <option value="koordinator" {{ old('status_user')=='koordinator' ? 'selected' : '' }}>
                            Koordinator</option>
                        <option value="sect_head" {{ old('status_user')=='sect_head' ? 'selected' : '' }}>Sect Head
                        </option>
                        <option value="super_admin" {{ old('status_user')=='super_admin' ? 'selected' : '' }}>Super
                            Admin</option>
                    </select>
                </div>

                <div>
                    <label for="branch" class="block text-sm font-medium text-slate-700">Branch (Cabang)</label>
                    <input type="text" name="branch" id="branch" value="{{ old('branch') }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label for="position" class="block text-sm font-medium text-slate-700">Posisi</label>
                    <select name="position" id="position"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors bg-white">
                        <option value="">Pilih Posisi</option>
                        <option value="FIELD" {{ old('position')=='FIELD' ? 'selected' : '' }}>FIELD</option>
                        <option value="FMC" {{ old('position')=='FMC' ? 'selected' : '' }}>FMC</option>
                    </select>
                </div>

                <div>
                    <label for="department" class="block text-sm font-medium text-slate-700">Department</label>
                    <select name="department" id="department"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors bg-white">
                        <option value="">Pilih Department</option>
                        <option value="RENTAL" {{ old('department')=='RENTAL' ? 'selected' : '' }}>RENTAL</option>
                        <option value="SERVICE" {{ old('department')=='SERVICE' ? 'selected' : '' }}>SERVICE</option>
                    </select>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi (Password) <span
                            class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                    <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter.</p>
                </div>

                <div>
                    <label for="is_verified" class="block text-sm font-medium text-slate-700">Status Verifikasi
                        Sistem</label>
                    <select name="is_verified" id="is_verified" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors bg-white">
                        <option value="0" {{ old('is_verified')=='0' ? 'selected' : '' }}>Belum Diverifikasi (Akan
                            diminta bayar)</option>
                        <option value="1" {{ old('is_verified')=='1' ? 'selected' : '' }}>Terverifikasi (Bypass
                            Pembayaran / Aktif)</option>
                    </select>
                </div>
            </div>

        </div>

        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-200">
            <button type="submit"
                class="w-full inline-flex justify-center rounded-xl border border-transparent bg-blue-600 px-6 py-2.5 text-base font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none transition-colors sm:ml-3 sm:w-auto sm:text-sm">
                Simpan Pengguna
            </button>
        </div>
    </form>
</div>
@endsection
