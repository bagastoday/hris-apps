<!-- resources/views/admin/tickets/index.blade.php -->
@extends('layouts.admin')

@section('title', 'Inbox Tiket ' . (auth()->user()->isFinance() ? 'Finance' : 'HR'))
@section('page-title', 'Inbox Tiket ' . (auth()->user()->isFinance() ? 'Finance' : 'HR'))
@section('page-subtitle', 'Kelola dan tindak lanjuti tiket yang ditujukan ke tim ' . (auth()->user()->isFinance() ? 'Finance' : 'HR'))

@section('content')
<div class="space-y-6">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-lg">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-400">Total Tiket Masuk</p>
                <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-amber-600">Menunggu Respon</p>
                <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['open']) }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-blue-600">Sedang Diproses</p>
                <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['in_progress']) }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-emerald-600">Terselesaikan</p>
                <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($stats['resolved']) }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari No. Tiket, judul kendala, atau nama karyawan..."
                   class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 min-w-[240px] flex-1">

            <select name="status" class="px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="">Semua Status</option>
                <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Menunggu Tanggapan</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Sedang Diproses</option>
                <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Selesai</option>
                <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Ditutup</option>
            </select>

            <select name="priority" class="px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="">Semua Prioritas</option>
                <option value="darurat" {{ $priority === 'darurat' ? 'selected' : '' }}>Darurat</option>
                <option value="tinggi" {{ $priority === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                <option value="sedang" {{ $priority === 'sedang' ? 'selected' : '' }}>Sedang</option>
                <option value="rendah" {{ $priority === 'rendah' ? 'selected' : '' }}>Rendah</option>
            </select>

            <select name="category" class="px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="">Semua Kategori</option>
                @foreach(\App\Models\Ticket::CATEGORY_LABELS as $value => $label)
                    <option value="{{ $value }}" {{ $category === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition">
                Filter
            </button>

            @if($search || $status || $priority || $category)
                <a href="{{ route('tickets.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Tickets Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. Tiket</th>
                        <th class="px-5 py-3.5">Karyawan / Pelapor</th>
                        <th class="px-5 py-3.5">Subjek Kendala</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Prioritas</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Terakhir Update</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($tickets as $t)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('tickets.show', $t) }}" class="text-brand-600 hover:underline">
                                    #{{ $t->ticket_code }}
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                @if($t->is_anonymous)
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">?</span>
                                        <span class="font-medium text-slate-600 italic">Anonim (Rahasia)</span>
                                    </div>
                                @else
                                    <div class="font-semibold text-slate-900">{{ $t->user?->name ?? 'Karyawan' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $t->user?->employee?->department?->name ?? 'Staf' }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                <a href="{{ route('tickets.show', $t) }}" class="font-semibold text-slate-900 hover:text-brand-600 line-clamp-1">
                                    {{ $t->title }}
                                </a>
                                <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ Str::limit($t->description, 60) }}</p>
                                @if($t->team_last_read_reply_id === null || $t->has_unread_for_team)
                                    <span class="mt-1 inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span> Pesan baru
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $t->category_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $t->priority_badge_class }}">
                                    {{ $t->priority_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $t->status_badge_class }}">
                                    {{ $t->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-400 text-[11px]">
                                {{ $t->updated_at->diffForHumans() }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('tickets.show', $t) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 font-semibold rounded-lg text-xs transition">
                                    Buka Tiket
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada tiket bantuan ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Belum ada pengaduan atau filter tidak cocok dengan pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
