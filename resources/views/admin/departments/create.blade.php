{{-- resources/views/admin/departments/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Departemen')
@section('page-title', 'Tambah Departemen')
@section('page-subtitle', 'Buat data departemen baru')

@section('content')
<div class="max-w-xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <form action="{{ route('departments.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Departemen <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm" placeholder="Contoh: IT & Engineering">
            @error('name')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode</label>
            <input type="text" name="code" value="{{ old('code') }}" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm" placeholder="Contoh: IT">
            @error('code')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 text-sm">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            <label for="is_active" class="text-sm text-slate-700">Aktif</label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                Simpan
            </button>
            <a href="{{ route('departments.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm font-medium">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection