@extends('layouts.admin')

@section('title', 'Pegawai')
@section('page-title', 'Data Pegawai')
@section('page-subtitle', 'Manajemen data pegawai')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-900">Daftar Pegawai</h2>
        <p class="text-sm text-slate-500">Total {{ $employees->count() }} pegawai</p>
    </div>
    <a href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Pegawai
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                    <th class="px-5 py-3.5 font-medium">Pegawai</th>
                    <th class="px-4 py-3.5 font-medium">Jabatan</th>
                    <th class="px-4 py-3.5 font-medium">Status</th>
                    <th class="px-4 py-3.5 font-medium">Bergabung</th>
                    <th class="px-4 py-3.5 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($employees as $emp)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($emp->full_name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $emp->full_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $emp->employee_code }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-medium text-slate-800">{{ $emp->position->name ?? '-' }}</p>
                            <p class="text-xs text-slate-400">{{ $emp->department->name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            @if($emp->employment_status === 'aktif')
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-full">
                                    {{ ucfirst($emp->employment_status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-500">
                            {{ $emp->join_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-xs text-slate-400">—</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                            Belum ada data pegawai
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection