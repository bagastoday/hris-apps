@extends('layouts.admin')

@section('title', 'Edit Pegawai')
@section('page-title', 'Edit Pegawai')
@section('page-subtitle', 'Ubah departemen, jabatan, dan status')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-brand-600 mb-5 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

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

        {{-- Info pegawai (read-only) --}}
        <div class="flex items-center gap-4 p-4 mb-6 bg-slate-50 rounded-xl">
            <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold">
                {{ strtoupper(substr($employee->full_name, 0, 2)) }}
            </div>
            <div>
                <p class="font-semibold text-slate-900">{{ $employee->full_name }}</p>
                <p class="text-sm text-slate-500">{{ $employee->employee_code }}</p>
            </div>
            <span class="ml-auto text-xs text-slate-400">Nama & NIK tidak dapat diubah</span>
        </div>

        <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Departemen</label>
                    <select name="department_id" id="department_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="">— Pilih —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $employee->department_id) == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jabatan</label>
                    <select name="position_id" id="position_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="">— Pilih —</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" data-department="{{ $pos->department_id }}" @selected(old('position_id', $employee->position_id) == $pos->id)>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                    @php $currentStatus = old('employment_status', $employee->employment_status === 'aktif' ? 'aktif' : 'resign'); @endphp
                    <select name="employment_status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="aktif" @selected($currentStatus === 'aktif')>Aktif</option>
                        <option value="resign" @selected($currentStatus === 'resign')>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
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
                Kalau pegawai lupa password, kembalikan ke password default ({{ \App\Models\Employee::DEFAULT_PASSWORD }}).
            @else
                Pegawai ini belum punya akun login.
            @endif
        </p>

        <form method="POST" action="{{ route('employees.reset-password', $employee) }}"
              onsubmit="return confirm('{{ $employee->user_id ? 'Reset password pegawai ini ke default?' : 'Buat akun login untuk pegawai ini?' }}');">
            @csrf
            <button type="submit" class="px-5 py-2.5 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition">
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
        Array.from(posSelect.options).forEach(opt => {
            if (!opt.value) return;
            const match = opt.dataset.department === deptId;
            opt.hidden = !match;
            if (!match && opt.selected) posSelect.value = '';
        });
    }

    deptSelect.addEventListener('change', filterPositions);
    filterPositions();
</script>
@endsection