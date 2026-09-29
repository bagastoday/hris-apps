<!-- resources/views/karyawan/tickets/index.blade.php -->
@extends('layouts.karyawan')

@section('title', 'Pusat Bantuan & Pengaduan')

@section('content')
<div class="space-y-6">

    {{-- Header & Create CTA --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pusat Bantuan & Pengaduan</h1>
            <p class="text-xs text-slate-500 mt-1">Sampaikan kendala ke tim HR atau Finance sesuai kategori yang kamu pilih.</p>
        </div>

        <a href="{{ route('karyawan.tickets.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-sm shadow-blue-500/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Tiket Baru
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-slate-400">Total Tiket Saya</p>
                <h4 class="text-lg font-bold text-slate-900 mt-0.5">{{ $stats['total'] }}</h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-amber-600 font-medium">Menunggu Respon</p>
                <h4 class="text-lg font-bold text-slate-900 mt-0.5">{{ $stats['open'] }}</h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-blue-600 font-medium">Sedang Diproses</p>
                <h4 class="text-lg font-bold text-slate-900 mt-0.5">{{ $stats['in_progress'] }}</h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-emerald-600 font-medium">Selesai</p>
                <h4 class="text-lg font-bold text-slate-900 mt-0.5">{{ $stats['resolved'] }}</h4>
            </div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('karyawan.tickets') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor tiket atau judul kendala..."
                   class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 min-w-[200px] flex-1">

            <select name="status" class="px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Menunggu Respon</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Sedang Diproses</option>
                <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Selesai</option>
                <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Ditutup</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition">
                Cari
            </button>
            @if($search || $status)
                <a href="{{ route('karyawan.tickets') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Tickets List --}}
    <div class="space-y-3">
        @forelse($tickets as $t)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 hover:border-blue-200 transition group">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg">
                            #{{ $t->ticket_code }}
                        </span>
                        @if($t->has_unread_for_employee)
                            <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[10px] font-semibold text-rose-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span> Balasan baru
                            </span>
                        @endif
                        <span class="text-xs font-medium px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600">
                            {{ $t->category_label }}
                        </span>
                        @if($t->is_anonymous)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 ring-1 ring-purple-200">
                                Rahasia / Anonim
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $t->status_badge_class }}">
                            {{ $t->status_label }}
                        </span>
                    </div>
                </div>

                <div class="py-3">
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-blue-600 transition">
                        <a href="{{ route('karyawan.tickets.show', $t) }}">
                            {{ $t->title }}
                        </a>
                    </h3>
                    <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                        {{ $t->description }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <div class="flex items-center gap-3">
                        <span>Dibuat {{ $t->created_at->diffForHumans() }}</span>
                        @if($t->replies()->count() > 0)
                            <span class="inline-flex items-center gap-1 font-semibold text-blue-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ $t->replies()->count() }} Tanggapan
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('karyawan.tickets.show', $t) }}"
                       class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-700 transition">
                        Buka Percakapan
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h4 class="font-bold text-slate-800 text-sm">Belum Ada Tiket Bantuan</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1 mb-5">Pilih kategori yang sesuai agar tiket diteruskan ke tim HR atau Finance.</p>
                <a href="{{ route('karyawan.tickets.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buka Tiket Pertama
                </a>
            </div>
        @endforelse
    </div>

    @if($tickets->hasPages())
        <div class="pt-2">
            {{ $tickets->links() }}
        </div>
    @endif

</div>
@endsection
