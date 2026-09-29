{{-- resources/views/admin/departments/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Departemen')
@section('page-title', 'Edit Departemen')
@section('page-subtitle', 'Ubah data departemen')

@section('content')
<div class="max-w-xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <form action="{{ route('departments.update', $department) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Departemen <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $department->name) }}" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm">
            @error('name')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode</label>
            <input type="text" name="code" value="{{ old('code', $department->code) }}" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm">
            @error('code')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm">{{ old('description', $department->description) }}</textarea>
            @error('description')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            <label for="is_active" class="text-sm text-slate-700">Aktif</label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/20 hover:shadow-lg hover:-translate-y-0.5 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                Simpan Perubahan
            </button>
            <a href="{{ route('departments.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm font-medium">
                Batal
            </a>
        </div>
    </form>
    {{-- Tambahkan setelah </form> di admin/departments/edit.blade.php --}}
<div class="max-w-xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mt-6">
    <h3 class="font-semibold text-slate-900 mb-4">Jabatan di Departemen Ini</h3>

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

    <div class="space-y-2 mb-5">
        @forelse($department->positions as $position)
            <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 rounded-xl">
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $position->name }}</p>
                    @if($position->level)
                        <p class="text-xs text-slate-400">{{ $position->level }}</p>
                    @endif
                </div>
                <form action="{{ route('positions.destroy', $position) }}" method="POST" onsubmit="return confirm('Hapus jabatan ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-100 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:border-rose-200 hover:bg-rose-100">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M10 11v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        @empty
            <p class="text-sm text-slate-400">Belum ada jabatan di departemen ini.</p>
        @endforelse
    </div>

    <form action="{{ route('positions.store', $department) }}" method="POST" class="flex items-end gap-3">
        @csrf
        <div class="flex-1">
            <label class="block text-xs font-medium text-slate-600 mb-1">Nama Jabatan</label>
            <input type="text" name="name" required class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Contoh: Admin Gudang">
        </div>
        <div class="w-32">
            <label class="block text-xs font-medium text-slate-600 mb-1">Level</label>
            <input type="text" name="level" class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="staff">
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 hover:shadow-md">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </button>
    </form>
</div>
</div>
@endsection