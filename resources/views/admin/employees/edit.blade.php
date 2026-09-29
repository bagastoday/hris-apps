{{-- resources/views/admin/employees/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Pegawai')
@section('page-title', 'Edit Pegawai')
@section('page-subtitle', 'Ubah data pegawai')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-brand-600 mb-5 transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar Pegawai
    </a>

    @if(session('success'))
        <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
        @if(isset($errors) && $errors->any())
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-100 text-sm text-red-600">
                <p class="font-semibold mb-1">Terjadi kesalahan validasi:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Info pegawai --}}
        <div class="flex items-center gap-4 p-4 mb-6 bg-slate-50 rounded-2xl border border-slate-100">
            <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-base shadow-sm">
                {{ strtoupper(substr($employee->full_name, 0, 2)) }}
            </div>
            <div>
                <p class="font-bold text-slate-900">{{ $employee->full_name }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs font-mono font-medium text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md">{{ $employee->employee_code }}</span>
                    <span class="text-xs text-slate-400">&bull; NIK: {{ $employee->nik }}</span>
                </div>
            </div>
            <span class="ml-auto text-xs text-slate-400 hidden sm:inline-block">Kode & NIK otomatis</span>
        </div>

        <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                </div>

                @if($employee->user && $employee->user->role !== 'hr')
                    <div class="sm:col-span-2 rounded-xl border border-brand-100 bg-brand-50/70 p-3 text-sm text-brand-800">
                        Akses akun mengikuti departemen. Memilih Finance/Accounting memberi akses Finance; departemen lainnya mendapat akses Karyawan.
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Kelamin</label>
                    <select name="gender" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                        <option value="">— Pilih —</option>
                        <option value="laki-laki" @selected(old('gender', $employee->gender) === 'laki-laki')>Laki-laki</option>
                        <option value="perempuan" @selected(old('gender', $employee->gender) === 'perempuan')>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Bergabung *</label>
                    <input type="date" name="join_date" value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Departemen</label>
                    <select name="department_id" id="department_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                        <option value="">— Pilih Departemen —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $employee->department_id) == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jabatan</label>
                    <select name="position_id" id="position_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition disabled:bg-slate-50 disabled:text-slate-400">
                        <option value="">— Pilih Jabatan —</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" data-department="{{ $pos->department_id }}" @selected(old('position_id', $employee->position_id) == $pos->id)>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status Kepegawaian *</label>
                    <select name="employment_status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                        <option value="aktif" @selected(old('employment_status', $employee->employment_status) === 'aktif')>Aktif</option>
                        <option value="kontrak" @selected(old('employment_status', $employee->employment_status) === 'kontrak')>Kontrak</option>
                        <option value="magang" @selected(old('employment_status', $employee->employment_status) === 'magang')>Magang</option>
                        <option value="cuti" @selected(old('employment_status', $employee->employment_status) === 'cuti')>Cuti</option>
                        <option value="resign" @selected(old('employment_status', $employee->employment_status) === 'resign')>Resign (Nonaktif)</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-3 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-md shadow-brand-600/20 hover:shadow-lg hover:-translate-y-0.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                    Simpan Perubahan
                </button>
                <a href="{{ route('employees.index') }}" class="px-6 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Akun login --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 mt-6">
        <h3 class="font-semibold text-slate-900 mb-1">Akun Login</h3>
        <p class="text-sm text-slate-500 mb-4">
            @if($employee->user_id)
                Kalau pegawai lupa password, kembalikan ke password default (<strong>{{ \App\Models\Employee::DEFAULT_PASSWORD }}</strong>).
            @else
                Pegawai ini belum punya akun login.
            @endif
        </p>

        <form method="POST" action="{{ route('employees.reset-password', $employee) }}"
              onsubmit="return confirm('{{ $employee->user_id ? 'Reset password pegawai ini ke default?' : 'Buat akun login untuk pegawai ini?' }}');">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 border border-amber-200 bg-amber-50 text-amber-800 text-sm font-semibold rounded-xl hover:bg-amber-100 hover:border-amber-300 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 0 1 0 8m-8-8a4 4 0 0 0 0 8m-3 5a7 7 0 0 1 14 0M12 3v3m0 12v3"/></svg>
                {{ $employee->user_id ? 'Reset Password ke Default' : 'Buat Akun Login' }}
            </button>
        </form>
    </div>
</div>

{{-- Jabatan otomatis difilter sesuai departemen yang dipilih --}}
<script>
    const deptSelect = document.getElementById('department_id');
    const posSelect = document.getElementById('position_id');

    function filterPositions() {
        const deptId = deptSelect.value;
        let visible = 0;

        posSelect.disabled = !deptId;

        Array.from(posSelect.options).forEach(function (opt) {
            if (!opt.value) return;
            const match = opt.dataset.department === deptId;
            opt.hidden = !match;
            opt.disabled = !match;
            if (match) visible++;
            if (!match && opt.selected) posSelect.value = '';
        });

        posSelect.options[0].textContent = !deptId
            ? '— Pilih departemen dulu —'
            : (visible ? '— Pilih Jabatan —' : '— Belum ada jabatan di departemen ini —');
    }

    deptSelect.addEventListener('change', filterPositions);
    filterPositions();
</script>
@endsection