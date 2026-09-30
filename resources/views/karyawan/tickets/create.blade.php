<!-- resources/views/karyawan/tickets/create.blade.php -->
@extends('layouts.karyawan')

@section('title', 'Buat Tiket Bantuan & Pengaduan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb / Back --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('karyawan.tickets') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Pusat Bantuan
        </a>
    </div>

    {{-- Form Container --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h1 class="text-lg font-bold text-slate-900">Formulir Tiket Bantuan & Pengaduan</h1>
            <p class="text-xs text-slate-500 mt-1">Pilih kategori kendala. Tiket akan otomatis diteruskan ke tim HR atau Finance yang sesuai.</p>
        </div>

        <form method="POST" action="{{ route('karyawan.tickets.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Judul / Subjek --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Subjek / Judul Kendala <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Kartu BPJS belum aktif, AC ruangan lantai 2 bocor, kendala absensi GPS..."
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('title') border-red-300 ring-1 ring-red-200 @enderror">
                @error('title')
                    <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori & Prioritas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Kategori Permasalahan <span class="text-red-500">*</span>
                    </label>
                    <select name="category" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('category') border-red-300 @enderror">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach(['Finance' => \App\Models\Ticket::FINANCE_CATEGORIES, 'HR' => array_diff(array_keys(\App\Models\Ticket::CATEGORY_LABELS), \App\Models\Ticket::FINANCE_CATEGORIES)] as $team => $categories)
                            <optgroup label="Tim {{ $team }}">
                                @foreach($categories as $value)
                                    <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ \App\Models\Ticket::CATEGORY_LABELS[$value] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tingkat Prioritas <span class="text-red-500">*</span>
                    </label>
                    <select name="priority" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('priority') border-red-300 @enderror">
                        <option value="rendah" {{ old('priority') === 'rendah' ? 'selected' : '' }}>Rendah (Pertanyaan umum / non-kritis)</option>
                        <option value="sedang" {{ old('priority', 'sedang') === 'sedang' ? 'selected' : '' }}>Sedang (Kendala kerja standar)</option>
                        <option value="tinggi" {{ old('priority') === 'tinggi' ? 'selected' : '' }}>Tinggi (Menghambat produktivitas kerja)</option>
                        <option value="darurat" {{ old('priority') === 'darurat' ? 'selected' : '' }}>Darurat (Sistem mati / butuh penanganan hari ini)</option>
                    </select>
                    @error('priority')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Deskripsi Kendala --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Detail Kronologi & Deskripsi Kendala <span class="text-red-500">*</span>
                </label>
                <textarea name="description" rows="5" required placeholder="Jelaskan secara rinci kendala yang dihadapi, waktu kejadian, dan nomor/kode yang relevan jika ada..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 leading-relaxed @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                <p class="text-[10px] text-slate-400 mt-1">Minimal 10 karakter.</p>
                @error('description')
                    <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Lampiran Berkas --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Lampiran Foto / Dokumen Pendukung (Opsional)
                </label>
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                <p class="text-[10px] text-slate-400 mt-1">Format file yang didukung: JPG, PNG, atau PDF (Ukuran maksimal 5MB).</p>
                @error('attachment')
                    <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>



            {{-- Action Buttons --}}
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('karyawan.tickets') }}"
                   class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim Tiket Sekarang
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
