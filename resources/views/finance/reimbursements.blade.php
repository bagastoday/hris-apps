@extends('layouts.admin')

@section('title', 'Approval Reimburse')
@section('page-title', 'Approval Reimburse')
@section('page-subtitle', 'Tinjau pengajuan karyawan tanpa mengubah status penanganan tiket')

@section('content')
@if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <a href="{{ route('finance.reimbursements', ['status' => 'pending']) }}" class="rounded-xl border p-4 {{ $status === 'pending' ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }}">
        <p class="text-xs font-medium text-amber-800">Menunggu</p><p class="mt-1 text-xl font-bold text-slate-900">{{ $stats['pending'] }}</p>
    </a>
    <a href="{{ route('finance.reimbursements', ['status' => 'approved']) }}" class="rounded-xl border p-4 {{ $status === 'approved' ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-white' }}">
        <p class="text-xs font-medium text-emerald-800">Disetujui</p><p class="mt-1 text-xl font-bold text-slate-900">{{ $stats['approved'] }}</p>
    </a>
    <a href="{{ route('finance.reimbursements', ['status' => 'rejected']) }}" class="rounded-xl border p-4 {{ $status === 'rejected' ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }}">
        <p class="text-xs font-medium text-red-800">Ditolak</p><p class="mt-1 text-xl font-bold text-slate-900">{{ $stats['rejected'] }}</p>
    </a>
</div>

<section class="mt-5 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold text-slate-900">{{ ['pending' => 'Pengajuan Menunggu', 'approved' => 'Pengajuan Disetujui', 'rejected' => 'Pengajuan Ditolak'][$status] }}</h2>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($tickets as $ticket)
            <article class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_15rem]">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs font-bold text-brand-700 hover:underline">#{{ $ticket->ticket_code }}</a>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $ticket->reimbursement_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($ticket->reimbursement_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
                            {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$ticket->reimbursement_status] }}
                        </span>
                        <span class="text-xs text-slate-400">Status tiket: {{ $ticket->status_label }}</span>
                    </div>
                    <a href="{{ route('tickets.show', $ticket) }}" class="mt-2 block text-sm font-semibold text-slate-900 hover:text-brand-700">{{ $ticket->title }}</a>
                    <p class="mt-1 text-xs text-slate-500">{{ $ticket->user?->employee?->full_name ?? $ticket->user?->name ?? 'Karyawan' }} · {{ $ticket->user?->employee?->department?->name ?? 'Tanpa departemen' }} · {{ $ticket->created_at->format('d/m/Y') }}</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $ticket->description }}</p>
                    <p class="mt-3 text-lg font-bold text-slate-900">Rp {{ number_format((float) $ticket->reimbursement_amount, 0, ',', '.') }}</p>

                    @if($ticket->attachment)
                        <a href="{{ route('tickets.reimbursements.proof', $ticket) }}" target="_blank" class="mt-3 inline-flex items-center gap-3 rounded-lg border border-slate-200 p-2 hover:bg-slate-50">
                            <img src="{{ route('tickets.reimbursements.proof', $ticket) }}" alt="Bukti reimbursement {{ $ticket->title }}" class="h-16 w-20 rounded object-cover">
                            <span class="text-xs font-semibold text-brand-700">Lihat foto bukti</span>
                        </a>
                    @endif

                    @if($ticket->reimbursement_status !== 'pending')
                        <p class="mt-3 text-xs text-slate-500">Diputuskan {{ $ticket->reimbursementReviewer?->name ?? 'Finance' }} · {{ $ticket->reimbursement_reviewed_at?->format('d/m/Y H:i') }}</p>
                        @if($ticket->reimbursement_review_note)<p class="mt-2 rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ $ticket->reimbursement_review_note }}</p>@endif
                        @if($ticket->financeTransaction)
                            <a href="{{ route('finance.transactions', ['period' => $ticket->financeTransaction->transaction_date->format('Y-m')]) }}" class="mt-2 inline-flex text-xs font-semibold text-emerald-700 hover:underline">Transaksi kas: {{ $ticket->financeTransaction->status === 'paid' ? 'sudah dibayar' : 'belum dibayar' }}</a>
                        @endif
                    @endif
                </div>

                @if($ticket->reimbursement_status === 'pending')
                    <div class="flex flex-col justify-center gap-3 rounded-xl bg-slate-50 p-4">
                        <form method="POST" action="{{ route('finance.reimbursements.decision', $ticket) }}">
                            @csrf
                            <input type="hidden" name="decision" value="approved">
                            <button class="w-full rounded-lg bg-emerald-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700" onclick="return confirm('Setujui reimburse dan masukkan sebagai pengeluaran belum dibayar?')">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('finance.reimbursements.decision', $ticket) }}" class="space-y-2">
                            @csrf
                            <input type="hidden" name="decision" value="rejected">
                            <label for="review-note-{{ $ticket->id }}" class="block text-xs font-medium text-slate-600">Alasan penolakan</label>
                            <textarea id="review-note-{{ $ticket->id }}" name="review_note" rows="2" required maxlength="1000" class="w-full rounded-lg border-slate-200 text-xs" placeholder="Jelaskan alasan penolakan"></textarea>
                            <button class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('Tolak pengajuan reimbursement ini?')">Tolak</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <p class="px-5 py-12 text-center text-sm text-slate-500">Tidak ada pengajuan pada status ini.</p>
        @endforelse
    </div>
    @if($tickets->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">{{ $tickets->links() }}</div>
    @endif
</section>
@endsection