{{-- resources/views/admin/departments/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Departemen')
@section('page-title', 'Data Departemen')
@section('page-subtitle', 'Manajemen departemen perusahaan')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-900">Daftar Departemen</h2>
        <p class="text-sm text-slate-500">Total {{ $departments->count() }} departemen</p>
    </div>
    <a href="{{ route('departments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Departemen
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                    <th class="px-5 py-3.5 font-medium">Nama Departemen</th>
                    <th class="px-4 py-3.5 font-medium">Kode</th>
                    <th class="px-4 py-3.5 font-medium">Jumlah Pegawai</th>
                    <th class="px-4 py-3.5 font-medium">Status</th>
                    <th class="px-4 py-3.5 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($departments as $dept)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ $dept->name }}</p>
                            @if($dept->description)
                                <p class="text-xs text-slate-400">{{ Str::limit($dept->description, 40) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ $dept->code ?? '-' }}</td>
                        <td class="px-4 py-4 text-slate-600">{{ $dept->employees_count }}</td>
                        <td class="px-4 py-4">
                            @if($dept->is_active)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-full">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('departments.edit', $dept) }}" class="text-brand-600 hover:text-brand-700 font-medium text-xs">Edit</a>
                                <form action="{{ route('departments.destroy', $dept) }}" method="POST" onsubmit="return confirm('Yakin mau hapus departemen ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-600 font-medium text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                            Belum ada data departemen
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection