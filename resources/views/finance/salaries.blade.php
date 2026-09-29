@extends('layouts.admin')

@section('title', 'Gaji Pokok')
@section('page-title', 'Pengaturan Gaji Pokok')
@section('page-subtitle', 'Kelola acuan gaji bulanan untuk pembuatan payroll')

@section('content')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-slate-500">Perubahan gaji pokok hanya berlaku untuk payroll draft yang dibuat setelah perubahan disimpan.</p>
    <a href="{{ route('finance.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">Kembali ke Payroll</a>
</div>

@if($errors->any())
    <div class="rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif
@if(session('success'))
    <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
@endif

<div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-wide text-slate-400">
                <tr><th class="px-5 py-3.5">Pegawai</th><th class="px-4 py-3.5">Departemen</th><th class="px-4 py-3.5">Status</th><th class="px-4 py-3.5 text-right">Gaji Pokok Bulanan</th><th class="px-5 py-3.5 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($employees as $employee)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ $employee->full_name }}</p>
                            <p class="text-xs text-slate-400">{{ $employee->employee_code }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ $employee->department?->name ?? 'Tanpa Departemen' }}</td>
                        <td class="px-4 py-4"><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ ucfirst($employee->employment_status) }}</span></td>
                        <td class="px-4 py-4">
                            <form id="salary-{{ $employee->id }}" method="POST" action="{{ route('finance.salary.update', $employee) }}">
                                @csrf
                                @method('PUT')
                            </form>
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-xs text-slate-400">Rp</span>
                                <input form="salary-{{ $employee->id }}" type="number" name="base_salary" min="0" step="1" value="{{ (int) $employee->base_salary }}" required
                                       class="w-44 rounded-lg border-slate-200 text-right text-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button form="salary-{{ $employee->id }}" class="rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">Simpan</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada pegawai aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
