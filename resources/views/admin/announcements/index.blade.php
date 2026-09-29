<!-- resources/views/admin/announcements/index.blade.php -->
@extends('layouts.admin')

@section('title', 'Papan Pengumuman Kantor')
@section('page-title', 'Papan Pengumuman Kantor')
@section('page-subtitle', 'Publikasikan memo, edaran libur bersama, dan kebijakan perusahaan kepada seluruh pegawai')

@section('content')
<div x-data="{
    createModal: false,
    editModal: false,
    activeEdit: { id: null, title: '', content: '', category: 'umum', badge_color: 'blue', is_pinned: false, is_active: true },
    openEdit(item) {
        this.activeEdit = { ...item };
        this.editModal = true;
    }
}" class="space-y-6">

    {{-- Top Action & Stats --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="bg-white px-4 py-2.5 rounded-2xl border border-slate-100 shadow-sm">
                <span class="text-xs text-slate-400">Total:</span>
                <span class="font-bold text-sm text-slate-900 ml-1">{{ $stats['total'] }}</span>
            </div>
            <div class="bg-white px-4 py-2.5 rounded-2xl border border-slate-100 shadow-sm">
                <span class="text-xs text-slate-400">Aktif:</span>
                <span class="font-bold text-sm text-emerald-600 ml-1">{{ $stats['active'] }}</span>
            </div>
            <div class="bg-white px-4 py-2.5 rounded-2xl border border-slate-100 shadow-sm">
                <span class="text-xs text-slate-400">Disematkan:</span>
                <span class="font-bold text-sm text-blue-600 ml-1">{{ $stats['pinned'] }}</span>
            </div>
        </div>

        <button type="button" @click="createModal = true"
                class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Pengumuman Baru
        </button>
    </div>

    {{-- Filter bar --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('announcements.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul / isi pengumuman..."
                   class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 min-w-[200px] flex-1">

            <select name="category" class="px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="">Semua Kategori</option>
                <option value="umum" {{ $category === 'umum' ? 'selected' : '' }}>Umum</option>
                <option value="libur" {{ $category === 'libur' ? 'selected' : '' }}>Libur / Cuti Bersama</option>
                <option value="kebijakan" {{ $category === 'kebijakan' ? 'selected' : '' }}>Kebijakan HR</option>
                <option value="penting" {{ $category === 'penting' ? 'selected' : '' }}>Penting / Mendesak</option>
                <option value="event" {{ $category === 'event' ? 'selected' : '' }}>Event & Kegiatan</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition">
                Cari
            </button>
            @if($search || $category)
                <a href="{{ route('announcements.index') }}" class="px-3 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-xl hover:bg-slate-50">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Grid Daftar Pengumuman --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($announcements as $item)
            @php
                $color = match($item->badge_color) {
                    'emerald' => 'emerald',
                    'amber' => 'amber',
                    'rose' => 'rose',
                    'purple' => 'purple',
                    default => 'blue'
                };
            @endphp
            <div class="bg-white rounded-2xl border {{ $item->is_pinned ? 'border-brand-300 ring-2 ring-brand-100 shadow-md' : 'border-slate-100 shadow-sm' }} p-6 flex flex-col justify-between relative transition hover:shadow-md">
                
                <div>
                    {{-- Header Card --}}
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-{{ $color }}-50 text-{{ $color }}-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500"></span>
                                {{ ucfirst($item->category) }}
                            </span>
                            @if($item->is_pinned)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full">
                                    📌 Disematkan
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">
                            {{ $item->published_at ? $item->published_at->format('d M Y') : $item->created_at->format('d M Y') }}
                        </span>
                    </div>

                    <h3 class="font-bold text-slate-900 text-base mb-2 leading-snug line-clamp-2">
                        {{ $item->title }}
                    </h3>

                    <p class="text-xs text-slate-600 leading-relaxed line-clamp-4 whitespace-pre-line mb-4">
                        {{ $item->content }}
                    </p>

                    @if($item->attachment)
                        <div class="mb-4">
                            <a href="{{ asset('storage/' . $item->attachment) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-800 bg-brand-50/60 px-3 py-1.5 rounded-xl border border-brand-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Lihat Lampiran File
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Action Footer --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <form action="{{ route('announcements.toggle-pin', $item) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold {{ $item->is_pinned ? 'text-amber-600 hover:text-amber-700' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $item->is_pinned ? 'Lepas Pin' : 'Sematkan' }}
                        </button>
                    </form>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="openEdit({{ json_encode($item) }})"
                                class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 transition" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>

                        <form action="{{ route('announcements.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah kamu yakin ingin menghapus pengumuman ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <p class="font-bold text-slate-700 text-sm">Belum Ada Pengumuman</p>
                <p class="text-xs text-slate-400 mt-1">Gunakan tombol di atas untuk membuat memo / pengumuman kantor pertama.</p>
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    @endif

    {{-- Modal Tambah Pengumuman --}}
    <div x-show="createModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="createModal = false"
             class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[92vh]">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-base">Buat Pengumuman Baru</h3>
                <button type="button" @click="createModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Judul Pengumuman</label>
                    <input type="text" name="title" required placeholder="Contoh: Jadwal Libur Nasional Idul Fitri 1448 H"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
                        <select name="category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                            <option value="umum">Umum</option>
                            <option value="libur">Libur / Cuti Bersama</option>
                            <option value="kebijakan">Kebijakan HR</option>
                            <option value="penting">Penting / Urgent</option>
                            <option value="event">Event Kantor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Aksen Warna Badge</label>
                        <select name="badge_color" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                            <option value="blue">Biru (Umum/Info)</option>
                            <option value="emerald">Hijau (Libur/Reward)</option>
                            <option value="amber">Kuning (Peringatan)</option>
                            <option value="rose">Merah (Penting/Wajib)</option>
                            <option value="purple">Ungu (Event/Kebijakan)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Isi Pesan Pengumuman</label>
                    <textarea name="content" rows="5" required placeholder="Tuliskan isi pengumuman secara lengkap dan jelas..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Lampiran Dokumen / Surat Edaran (Opsional)</label>
                    <input type="file" name="attachment" accept=".pdf,image/*"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    <p class="text-[10px] text-slate-400 mt-1">Format: PDF, JPG, PNG &bull; Maks. 5MB</p>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="is_pinned" name="is_pinned" value="1" class="rounded border-slate-300 text-brand-600">
                    <label for="is_pinned" class="text-xs font-medium text-slate-700">Sematkan di bagian paling atas (Pin)</label>
                </div>

                <div class="pt-3 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                        Publikasikan Sekarang
                    </button>
                    <button type="button" @click="createModal = false" class="px-4 py-2.5 border border-slate-200 text-slate-600 text-xs font-medium rounded-xl hover:bg-slate-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Pengumuman --}}
    <div x-show="editModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="editModal = false"
             class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[92vh]">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-base">Edit Pengumuman</h3>
                <button type="button" @click="editModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form :action="'{{ url('announcements') }}/' + activeEdit.id" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Judul Pengumuman</label>
                    <input type="text" name="title" x-model="activeEdit.title" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
                        <select name="category" x-model="activeEdit.category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                            <option value="umum">Umum</option>
                            <option value="libur">Libur / Cuti Bersama</option>
                            <option value="kebijakan">Kebijakan HR</option>
                            <option value="penting">Penting / Urgent</option>
                            <option value="event">Event Kantor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Aksen Warna Badge</label>
                        <select name="badge_color" x-model="activeEdit.badge_color" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                            <option value="blue">Biru (Umum/Info)</option>
                            <option value="emerald">Hijau (Libur/Reward)</option>
                            <option value="amber">Kuning (Peringatan)</option>
                            <option value="rose">Merah (Penting/Wajib)</option>
                            <option value="purple">Ungu (Event/Kebijakan)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Isi Pesan Pengumuman</label>
                    <textarea name="content" x-model="activeEdit.content" rows="5" required
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Ganti Lampiran File (Opsional)</label>
                    <input type="file" name="attachment" accept=".pdf,image/*"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700">
                        <input type="checkbox" name="is_pinned" value="1" :checked="activeEdit.is_pinned" class="rounded border-slate-300 text-brand-600">
                        Sematkan (Pin)
                    </label>

                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700">
                        <input type="checkbox" name="is_active" value="1" :checked="activeEdit.is_active" class="rounded border-slate-300 text-brand-600">
                        Status Aktif Tayang
                    </label>
                </div>

                <div class="pt-3 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                        Simpan Perubahan
                    </button>
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 border border-slate-200 text-slate-600 text-xs font-medium rounded-xl hover:bg-slate-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
