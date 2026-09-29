<!-- resources/views/karyawan/announcements/index.blade.php -->
@extends('layouts.karyawan')

@section('title', 'Papan Pengumuman')

@section('content')
<div x-data="{
    readModal: false,
    selectedItem: null,
    openRead(item) {
        this.selectedItem = item;
        this.readModal = true;
    }
}" class="space-y-6">

    {{-- Top Hero Section --}}
    <div class="gradient-hero rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-medium backdrop-blur-md mb-3 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    INFORMASI & MEMO PERUSAHAAN
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Papan Pengumuman Kantor
                </h1>
                <p class="text-blue-100/80 text-sm mt-1">
                    Informasi resmi manajemen, pengumuman libur bersama, dan edaran kebijakan operasional TalentaCore.
                </p>
            </div>
        </div>

        {{-- Decorative circles --}}
        <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-white/5 pointer-events-none"></div>
        <div class="absolute left-1/3 -top-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-xl"></div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @foreach([
                '' => 'Semua',
                'penting' => '🔥 Penting',
                'libur' => '🌴 Libur & Cuti Bersama',
                'kebijakan' => '📋 Kebijakan HR',
                'event' => '🎉 Event Kantor',
                'umum' => '📢 Info Umum'
            ] as $key => $label)
                <a href="{{ route('karyawan.announcements', ['category' => $key, 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $category === $key ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('karyawan.announcements') }}" class="flex items-center gap-2">
            <input type="hidden" name="category" value="{{ $category }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari topik pengumuman..."
                   class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 w-full sm:w-48">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition">
                Cari
            </button>
        </form>
    </div>

    {{-- Announcements Grid --}}
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
            <div class="bg-white rounded-3xl border {{ $item->is_pinned ? 'border-brand-300 ring-2 ring-brand-100/70 shadow-md' : 'border-slate-100 shadow-sm' }} p-6 flex flex-col justify-between transition hover:-translate-y-0.5 hover:shadow-lg">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-{{ $color }}-50 text-{{ $color }}-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500"></span>
                                {{ ucfirst($item->category) }}
                            </span>
                            @if($item->is_pinned)
                                <span class="text-[11px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full">
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
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <button type="button" @click="openRead({{ json_encode($item) }})"
                            class="font-bold text-brand-600 hover:text-brand-800 transition flex items-center gap-1">
                        Baca Selengkapnya
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    @if($item->attachment)
                        <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Lampiran
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl border border-slate-100 p-12 text-center text-slate-400 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <p class="font-bold text-slate-700 text-sm">Tidak Ada Pengumuman</p>
                <p class="text-xs text-slate-400 mt-1">Saat ini belum ada pengumuman kantor yang tersedia pada kategori ini.</p>
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    @endif

    {{-- Modal Baca Pengumuman Lengkap --}}
    <div x-show="readModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="readModal = false"
             class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider" x-text="selectedItem ? selectedItem.category : 'Pengumuman'"></span>
                    <span x-show="selectedItem && selectedItem.is_pinned" class="text-[10px] font-bold bg-brand-50 text-brand-700 px-2 py-0.5 rounded-full">📌 Pinned</span>
                </div>
                <button type="button" @click="readModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-4 text-slate-800">
                <h2 class="text-xl font-bold text-slate-900 leading-snug" x-text="selectedItem ? selectedItem.title : ''"></h2>
                
                <p class="text-xs text-slate-400 font-mono" x-text="selectedItem && selectedItem.published_at ? 'Diterbitkan: ' + new Date(selectedItem.published_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : ''"></p>

                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line border-t border-slate-100 pt-4" x-text="selectedItem ? selectedItem.content : ''"></div>

                <template x-if="selectedItem && selectedItem.attachment">
                    <div class="pt-4 border-t border-slate-100">
                        <a :href="'{{ asset('storage') }}/' + selectedItem.attachment" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-50 text-brand-700 hover:bg-brand-100 font-semibold text-xs border border-brand-200 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Unduh Lampiran Resmi (PDF / Gambar)
                        </a>
                    </div>
                </template>
            </div>

            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 text-right">
                <button type="button" @click="readModal = false" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
