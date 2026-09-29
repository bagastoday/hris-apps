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
                           oninput="this.value = this.value.toLocaleUpperCase('id-ID')"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                </div>

                <div class="sm:col-span-2 rounded-xl border border-brand-100 bg-brand-50/70 p-3 text-sm text-brand-800">
                    Akses akun mengikuti departemen yang dipilih. Departemen Finance/Accounting otomatis mendapat akses Finance; departemen lainnya mendapat akses Karyawan.
                </div>

                <div class="sm:col-span-2">
                    <span class="block text-sm font-medium text-slate-700 mb-2">Email Pegawai</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 transition has-[:checked]:border-brand-300 has-[:checked]:bg-brand-50/70">
                            <input type="radio" name="email_option" value="email" @checked(old('email_option', 'email') === 'email')
                                   class="mt-0.5 border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">Gunakan email</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Buat email kantor otomatis dari nama pegawai.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 transition has-[:checked]:border-amber-300 has-[:checked]:bg-amber-50/70">
                            <input type="radio" name="email_option" value="no_email" @checked(old('email_option') === 'no_email')
                                   class="mt-0.5 border-slate-300 text-amber-600 focus:ring-amber-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">Tanpa email</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Pegawai login menggunakan NIK/kode kantor.</span>
                            </span>
                        </label>
                    </div>
                    <div id="generated-email-wrap" class="mt-3">
                        <label for="employee_email" class="block text-sm font-medium text-slate-700 mb-1.5">Email kantor otomatis</label>
                        <input type="email" id="employee_email" value="{{ old('email') }}" readonly
                               class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600 focus:outline-none">
                        <p class="text-xs text-slate-400 mt-1">Diambil dari dua kata pertama nama. Jika email sudah digunakan, sistem menambahkan angka.</p>
                    </div>
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
                    <select name="department_id" id="department_id"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="">— Pilih —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id')==$dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jabatan</label>
                    <select name="position_id" id="position_id"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 disabled:bg-slate-50 disabled:text-slate-400">
                        <option value="">— Pilih —</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" data-department="{{ $pos->department_id }}" @selected(old('position_id')==$pos->id)>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400 mt-1">
                        Jabatan mengikuti departemen. Belum ada?
                        <a href="{{ route('departments.index') }}" class="text-brand-600 hover:underline">Tambahkan lewat menu Departemen</a>.
                    </p>
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

            {{-- Info akun login --}}
            <div class="pt-4 border-t border-slate-100">
                <div class="p-3 rounded-xl bg-slate-50 text-xs text-slate-500">
                    Akun login dibuat otomatis. Pegawai bisa masuk pakai <strong>NIK/kode kantor</strong> (EMP-xxx){{ old('email_option', 'email') === 'email' ? ' atau email' : '' }},
                    dengan password default <strong>{{ \App\Models\Employee::DEFAULT_PASSWORD }}</strong>.
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-md shadow-brand-600/20 hover:shadow-lg hover:-translate-y-0.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                    Simpan Pegawai
                </button>
                <a href="{{ route('employees.index') }}" class="px-6 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Jabatan otomatis difilter sesuai departemen yang dipilih --}}
<script>
    const nameInput = document.querySelector('[name="full_name"]');
    const emailInput = document.getElementById('employee_email');
    const emailWrap = document.getElementById('generated-email-wrap');
    const emailOptions = document.querySelectorAll('[name="email_option"]');
    const deptSelect = document.getElementById('department_id');
    const posSelect = document.getElementById('position_id');

    function updateEmployeeEmail() {
        const parts = nameInput.value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .split(/\s+/)
            .map(part => part.replace(/[^a-z0-9]/g, ''))
            .filter(Boolean)
            .slice(0, 2);

        emailInput.value = `${parts.join('.') || 'pegawai'}@talenta.id`;
    }

    function updateEmailOption() {
        const useEmail = document.querySelector('[name="email_option"]:checked').value === 'email';
        emailWrap.hidden = !useEmail;
    }

    function filterPositions() {
        const deptId = deptSelect.value;
        let visible = 0;

        // Jabatan terkunci sampai departemen dipilih
        posSelect.disabled = !deptId;

        Array.from(posSelect.options).forEach(function (opt) {
            if (!opt.value) return;
            const match = opt.dataset.department === deptId;
            opt.hidden = !match;
            opt.disabled = !match; // cadangan untuk browser yang tidak mendukung hidden pada option
            if (match) visible++;
            if (!match && opt.selected) posSelect.value = '';
        });

        posSelect.options[0].textContent = !deptId
            ? '— Pilih departemen dulu —'
            : (visible ? '— Pilih —' : '— Belum ada jabatan di departemen ini —');
    }

    nameInput.addEventListener('input', updateEmployeeEmail);
    updateEmployeeEmail();
    emailOptions.forEach(option => option.addEventListener('change', updateEmailOption));
    updateEmailOption();
    deptSelect.addEventListener('change', filterPositions);
    filterPositions();
</script>
@endsection