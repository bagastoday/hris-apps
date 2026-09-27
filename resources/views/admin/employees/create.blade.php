@extends('layouts.admin')

@section('title', 'Tambah Pegawai')
@section('page-title', 'Tambah Pegawai')
@section('page-subtitle', 'Daftarkan pegawai baru')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-brand-600 mb-5 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
        @if($errors->any())
            <div class="mb-5 p-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('employees.store') }}" class="space-y-5">
            @csrf

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap *</label>
            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
            <p class="text-xs text-slate-400 mt-1">NIK / Kode pegawai akan digenerate otomatis (EMP-001, EMP-002, ...)</p>
        </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Kelamin</label>
                    <select name="gender" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="">— Pilih —</option>
                        <option value="laki-laki" @selected(old('gender')=='laki-laki')>Laki-laki</option>
                        <option value="perempuan" @selected(old('gender')=='perempuan')>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Bergabung *</label>
                    <input type="date" name="join_date" value="{{ old('join_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Departemen</label>
                    <select name="department_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="">— Pilih —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id')==$dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jabatan</label>
                    <select name="position_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="">— Pilih —</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(old('position_id')==$pos->id)>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status *</label>
                    <select name="employment_status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="aktif" @selected(old('employment_status', 'aktif')=='aktif')>Aktif</option>
                        <option value="kontrak" @selected(old('employment_status')=='kontrak')>Kontrak</option>
                        <option value="magang" @selected(old('employment_status')=='magang')>Magang</option>
                    </select>
                </div>
            </div>

            {{-- Buat akun login --}}
            <div class="pt-4 border-t border-slate-100">
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-3">
                    <input type="checkbox" name="create_account" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600"
                           onchange="document.getElementById('password-field').classList.toggle('hidden', !this.checked)">
                    Buat akun login untuk pegawai ini
                </label>
                <div id="password-field" class="hidden">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password Login</label>
                    <input type="password" name="password" minlength="6"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600"
                           placeholder="Minimal 6 karakter">
                    <p class="text-xs text-slate-400 mt-1">Pegawai bisa login pakai email + password ini</p>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                    Simpan Pegawai
                </button>
                <a href="{{ route('employees.index') }}" class="px-6 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection