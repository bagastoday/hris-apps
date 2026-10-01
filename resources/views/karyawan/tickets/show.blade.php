<!-- resources/views/karyawan/tickets/show.blade.php -->
@extends('layouts.karyawan')

@section('title', 'Tiket #' . $ticket->ticket_code . ' - ' . $ticket->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Top Back & Badges --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('karyawan.tickets') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Pusat Bantuan
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

    @if($ticket->category === 'reimburse')
        @php
            $reimbursementStatusLabels = ['pending' => 'Menunggu Approval Finance', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
            $reimbursementStatusClasses = ['pending' => 'bg-amber-50 text-amber-800', 'approved' => 'bg-emerald-50 text-emerald-800', 'rejected' => 'bg-red-50 text-red-800'];
        @endphp
        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Status Reimbursement</h2>
                    <p class="mt-1 text-xs text-slate-500">Nominal diajukan Rp {{ number_format((float) $ticket->reimbursement_amount, 0, ',', '.') }}</p>
                </div>
                @if($ticket->reimbursement_status)
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $reimbursementStatusClasses[$ticket->reimbursement_status] }}">
                        {{ $reimbursementStatusLabels[$ticket->reimbursement_status] }}
                    </span>
                @else
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Belum ada keputusan</span>
                @endif
            </div>
            @if($ticket->reimbursement_review_note)
                <p class="mt-3 rounded-lg bg-slate-50 p-3 text-xs text-slate-700">Catatan Finance: {{ $ticket->reimbursement_review_note }}</p>
            @endif
            @if($ticket->reimbursement_status === 'approved')
                <p class="mt-3 text-xs text-emerald-800">Pengajuan disetujui. Penggantian akan diproses oleh Finance.</p>
            @endif
        </section>
    @endif

    {{-- Initial Ticket Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-md">
                        #{{ $ticket->ticket_code }}
                    </span>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                        {{ $ticket->category_label }}
                    </span>
                    @if($ticket->is_anonymous)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 ring-1 ring-purple-200">
                            Pengaduan Rahasia
                        </span>
                    @endif
                </div>
                <h1 class="text-base font-bold text-slate-900">{{ $ticket->title }}</h1>
            </div>

            <div class="text-left sm:text-right text-xs text-slate-400">
                <div>Diajukan pada {{ $ticket->created_at->translatedFormat('d F Y, H:i') }} WIB</div>
                @if($ticket->assignedTo)
                    <div class="text-blue-600 font-medium mt-0.5">PIC {{ $ticket->assigned_team_label }}: {{ $ticket->assignedTo->name }}</div>
                @endif
            </div>
        </div>

        {{-- Description --}}
        <div class="text-slate-800 text-xs sm:text-sm leading-relaxed whitespace-pre-line">
            {{ $ticket->description }}
        </div>

        {{-- Attachment --}}
        @if($ticket->attachment)
            <div class="pt-4 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-500 mb-2">Lampiran Kamu:</p>
                @php
                    $ext = strtolower(pathinfo($ticket->attachment, PATHINFO_EXTENSION));
                    $attachmentUrl = $ticket->category === 'reimburse' && $ticket->reimbursement_status
                        ? route('tickets.reimbursements.proof', $ticket)
                        : asset('storage/' . $ticket->attachment);
                @endphp
                @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                    <div class="max-w-md rounded-xl overflow-hidden border border-slate-200">
                        <img src="{{ $attachmentUrl }}" alt="Lampiran Tiket" class="w-full h-auto object-cover max-h-72">
                    </div>
                @else
                    <a href="{{ $attachmentUrl }}" target="_blank"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-blue-600 font-semibold text-xs hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Unduh Berkas Lampiran ({{ strtoupper($ext) }})
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Conversation Thread --}}
    <div class="space-y-4">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">
            Percakapan & Tindak Lanjut ({{ $ticket->replies->count() }})
        </h3>

        @forelse($ticket->replies as $reply)
            <div class="rounded-2xl p-5 border shadow-sm {{ $reply->is_admin_reply ? 'bg-blue-50/50 border-blue-200 mr-4 sm:mr-10' : 'bg-white border-slate-100 ml-4 sm:ml-10' }}">
                <div class="flex items-center justify-between border-b {{ $reply->is_admin_reply ? 'border-blue-100' : 'border-slate-100' }} pb-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $reply->is_admin_reply ? 'bg-blue-600 text-white' : 'bg-slate-800 text-white' }}">
                            {{ $reply->is_admin_reply ? $ticket->assigned_team_label : substr($reply->user?->name ?? 'K', 0, 1) }}
                        </div>
                        <div>
                            <span class="font-bold text-xs text-slate-900">
                                {{ $reply->is_admin_reply ? ($reply->user?->name . ' (Tim ' . $ticket->assigned_team_label . ')') : 'Saya (' . $reply->user?->name . ')' }}
                            </span>
                            <div class="text-[10px] text-slate-400">
                                {{ $reply->created_at->translatedFormat('d M Y, H:i') }} WIB
                            </div>
                        </div>
                    </div>

                    @if($reply->is_admin_reply)
                        <span class="text-[10px] font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Respon Resmi {{ $ticket->assigned_team_label }}</span>
                    @else
                        <span class="text-[10px] font-semibold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">Tanggapan Saya</span>
                    @endif
                </div>

                <div class="text-slate-800 text-xs sm:text-sm leading-relaxed whitespace-pre-line">
                    {{ $reply->message }}
                </div>

                @if($reply->attachment)
                    <div class="mt-3 pt-3 border-t {{ $reply->is_admin_reply ? 'border-blue-100' : 'border-slate-100' }}">
                        <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs text-blue-600 font-semibold hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Lihat Dokumen / Lampiran Terlampir
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-400">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs font-semibold text-slate-600">Tiket sedang dalam antrean tim {{ $ticket->assigned_team_label }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Kamu akan melihat balasan atau solusi resmi tim terkait di sini setelah tiket ditindaklanjuti.</p>
            </div>
        @endforelse
    </div>

    {{-- Reply Form --}}
    @if($ticket->status === 'closed')
        <div class="bg-slate-100 rounded-2xl p-5 text-center text-slate-500 text-xs">
            <span class="font-bold text-slate-700">Tiket ini telah ditutup (Closed).</span>
            <p class="mt-0.5">Jika kamu memiliki kendala lain, silakan buat tiket baru.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-slate-900 text-sm">Kirim Balasan / Tanggapan Tambahan</h3>

            <form method="POST" action="{{ route('karyawan.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pesan Balasan <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="3" required placeholder="Tuliskan konfirmasi kamu atau pertanyaan lanjutan..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                    @error('message')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lampirkan Bukti / Foto Tambahan (Opsional)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    <p class="text-[10px] text-slate-400 mt-1">Maks. 5MB (JPG, PNG, PDF)</p>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Balasan
                    </button>
                </div>
            </form>
        </div>
    @endif

</div>
@endsection
