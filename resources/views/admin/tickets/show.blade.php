<!-- resources/views/admin/tickets/show.blade.php -->
@extends('layouts.admin')

@section('title', 'Detail Tiket #' . $ticket->ticket_code)
@section('page-title', 'Detail Tiket #' . $ticket->ticket_code)
@section('page-subtitle', $ticket->title)

@section('content')
<div class="space-y-6">

    {{-- Top Back & Badges --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Tiket
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $ticket->priority_badge_class }}">
                Prioritas: {{ $ticket->priority_label }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $ticket->status_badge_class }}">
                Status: {{ $ticket->status_label }}
            </span>
        </div>
    </div>

    {{-- Main 2-Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Conversation & Inquiry (2 cols) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Original Ticket Problem Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                            {{ $ticket->is_anonymous ? '?' : substr($ticket->user?->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">
                                {{ $ticket->is_anonymous ? 'Anonim (Pengaduan Rahasia)' : ($ticket->user?->name ?? 'Karyawan') }}
                            </div>
                            <div class="text-xs text-slate-400">
                                Dibuat {{ $ticket->created_at->translatedFormat('d F Y, H:i') }} WIB ({{ $ticket->created_at->diffForHumans() }})
                            </div>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-medium">
                        {{ $ticket->category_label }}
                    </span>
                </div>

                {{-- Ticket Body --}}
                <div class="text-slate-800 text-sm leading-relaxed whitespace-pre-line">
                    {{ $ticket->description }}
                </div>

                {{-- Attachment if any --}}
                @if($ticket->attachment)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs font-semibold text-slate-500 mb-2">Lampiran Bukti / Dokumen:</p>
                        @php
                            $ext = strtolower(pathinfo($ticket->attachment, PATHINFO_EXTENSION));
                        @endphp
                        @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                            <div class="max-w-md rounded-xl overflow-hidden border border-slate-200">
                                <img src="{{ asset('storage/' . $ticket->attachment) }}" alt="Lampiran Tiket" class="w-full h-auto object-cover max-h-72">
                            </div>
                        @else
                            <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank"
                               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-brand-600 font-semibold text-xs hover:bg-slate-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Unduh Berkas Lampiran ({{ strtoupper($ext) }})
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Replies Thread --}}
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Riwayat Tanggapan & Percakapan ({{ $ticket->replies->count() }})</h4>

                @forelse($ticket->replies as $reply)
                    <div class="rounded-2xl p-5 border shadow-sm {{ $reply->is_admin_reply ? 'bg-blue-50/40 border-blue-100 ml-4 sm:ml-8' : 'bg-white border-slate-100 mr-4 sm:mr-8' }}">
                        <div class="flex items-center justify-between border-b {{ $reply->is_admin_reply ? 'border-blue-100' : 'border-slate-100' }} pb-3 mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $reply->is_admin_reply ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $reply->is_admin_reply ? 'HR' : substr($reply->user?->name ?? 'K', 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-xs text-slate-900">
                                        {{ $reply->is_admin_reply ? ($reply->user?->name . ' (Tim HR)') : ($ticket->is_anonymous ? 'Pelapor (Anonim)' : ($reply->user?->name ?? 'Karyawan')) }}
                                    </span>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $reply->created_at->translatedFormat('d M Y, H:i') }} WIB
                                    </div>
                                </div>
                            </div>

                            @if($reply->is_admin_reply)
                                <span class="text-[10px] font-semibold bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full">Respon HR</span>
                            @else
                                <span class="text-[10px] font-semibold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">Karyawan</span>
                            @endif
                        </div>

                        <div class="text-slate-800 text-xs leading-relaxed whitespace-pre-line">
                            {{ $reply->message }}
                        </div>

                        @if($reply->attachment)
                            <div class="mt-3 pt-3 border-t {{ $reply->is_admin_reply ? 'border-blue-100' : 'border-slate-100' }}">
                                <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 text-xs text-brand-600 font-semibold hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    Lihat Lampiran Berkas
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-6 text-center text-slate-400">
                        <p class="text-xs">Belum ada tanggapan atau riwayat percakapan pada tiket ini.</p>
                    </div>
                @endforelse
            </div>

            {{-- HR Reply Box --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h4 class="font-bold text-slate-900 text-sm">Kirimkan Tanggapan Solusi</h4>

                <form method="POST" action="{{ route('tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Pesan Tanggapan <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="4" required placeholder="Tuliskan jawaban, panduan solusi, atau konfirmasi tindakan HR..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Lampirkan Berkas / Screenshot (Opsional)</label>
                            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            <p class="text-[10px] text-slate-400 mt-1">Maks. 5MB (Format PDF, PNG, JPG)</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Otomatis Perbarui Status Tiket Ke:</label>
                            <select name="change_status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>Sedang Diproses (In Progress)</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Terselesaikan (Resolved)</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Ditutup Permanen (Closed)</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Kirim Tanggapan
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- Right: Ticket Management & Pelapor Info (1 col) --}}
        <div class="space-y-6">

            {{-- Quick Status & Priority Form --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h4 class="font-bold text-slate-900 text-sm">Kelola Status Tiket</h4>

                <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Status Penanganan</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Menunggu Tanggapan</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>Sedang Diproses</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Selesai Ditangani</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Ditutup</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tingkat Prioritas</label>
                        <select name="priority" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="darurat" {{ $ticket->priority === 'darurat' ? 'selected' : '' }}>Darurat (Urgent)</option>
                            <option value="tinggi" {{ $ticket->priority === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                            <option value="sedang" {{ $ticket->priority === 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="rendah" {{ $ticket->priority === 'rendah' ? 'selected' : '' }}>Rendah</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            {{-- Pelapor Info Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
                <h4 class="font-bold text-slate-900 text-sm">Informasi Pelapor</h4>

                @if($ticket->is_anonymous)
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Pengaduan Rahasia / Whistleblowing
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Identitas karyawan disamarkan secara otomatis agar privasi dan keamanan pelapor tetap terlindungi.
                        </p>
                    </div>
                @else
                    <div class="space-y-2.5 text-xs">
                        <div>
                            <span class="text-slate-400">Nama Pegawai:</span>
                            <div class="font-semibold text-slate-800 mt-0.5">{{ $ticket->user?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-slate-400">Departemen / Posisi:</span>
                            <div class="font-semibold text-slate-800 mt-0.5">
                                {{ $ticket->user?->employee?->department?->name ?? 'Staf' }} - {{ $ticket->user?->employee?->position?->name ?? '-' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400">Email:</span>
                            <div class="font-semibold text-slate-800 mt-0.5">{{ $ticket->user?->email ?? '-' }}</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Metadata Info --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-2.5 text-xs">
                <h4 class="font-bold text-slate-900 text-sm mb-3">Informasi Sistem</h4>

                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Kode Tiket:</span>
                    <span class="font-mono font-bold text-slate-800">#{{ $ticket->ticket_code }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Ditangani Oleh:</span>
                    <span class="font-medium text-slate-800">{{ $ticket->assignedTo?->name ?? 'Belum Ditugaskan' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Waktu Dibuat:</span>
                    <span class="font-medium text-slate-800">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($ticket->resolved_at)
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Waktu Selesai:</span>
                        <span class="font-medium text-emerald-600">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
