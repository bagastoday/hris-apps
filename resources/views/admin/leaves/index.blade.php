{{-- resources/views/admin/leaves/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Cuti')
@section('page-title', 'Pengajuan Cuti')
@section('page-subtitle', 'Kelola persetujuan cuti pegawai')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-900">Daftar Pengajuan</h2>
        <p class="text-sm text-slate-500">Total {{ $leaves->count() }} pengajuan</p>
    </div>
    <div class="flex items-center gap-2">
        @foreach(['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'semua' => 'Semua'] as $key => $label)
            <a href="{{ route('leaves.index', ['status' => $key]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === $key ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

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

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                    <th class="px-5 py-3.5 font-medium">Pegawai</th>
                    <th class="px-4 py-3.5 font-medium">Jenis Cuti</th>
                    <th class="px-4 py-3.5 font-medium">Tanggal</th>
                    <th class="px-4 py-3.5 font-medium">Durasi</th>
                    <th class="px-4 py-3.5 font-medium">Status</th>
                    <th class="px-4 py-3.5 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($leaves as $leave)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ $leave->employee->full_name ?? '-' }}</p>
                            <p class="text-xs text-slate-400">{{ $leave->employee->employee_code ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ str_replace('_', ' ', ucfirst($leave->leave_type)) }}</td>
                        <td class="px-4 py-4 text-slate-600">
                            {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ $leave->total_days }} hari</td>
                        <td class="px-4 py-4">
                            @php
                                $badgeColor = match($leave->status) {
                                    'approved' => 'emerald',
                                    'rejected' => 'red',
                                    default => 'amber',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-{{ $badgeColor }}-700 bg-{{ $badgeColor }}-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-{{ $badgeColor }}-500"></span>
                                {{ ucfirst($leave->status) }}
                            </span>
                            @if($leave->status === 'rejected' && $leave->rejection_reason)
                                <p class="text-xs text-slate-400 mt-1">{{ $leave->rejection_reason }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($leave->status === 'pending')
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('leaves.approve', $leave) }}" method="POST" onsubmit="return confirm('Setujui pengajuan cuti ini?');">
                                        @csrf
                                        <button type="submit" class="group inline-flex items-center gap-1.5 rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:border-emerald-200 hover:bg-emerald-100 hover:shadow-sm">
                                            <svg class="h-3.5 w-3.5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                                            Setujui
                                        </button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('reject-modal-{{ $leave->id }}').classList.remove('hidden')" class="group inline-flex items-center gap-1.5 rounded-lg border border-rose-100 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:border-rose-200 hover:bg-rose-100 hover:shadow-sm">
                                        <svg class="h-3.5 w-3.5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg>
                                        Tolak
                                    </button>
                                </div>

                                {{-- Modal alasan penolakan --}}
                                <div id="reject-modal-{{ $leave->id }}" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                                    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
                                        <h3 class="font-semibold text-slate-900 mb-3">Alasan Penolakan</h3>
                                        <form action="{{ route('leaves.reject', $leave) }}" method="POST">
                                            @csrf
                                            <textarea name="rejection_reason" rows="3" required class="w-full rounded-xl border-slate-200 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Tuliskan alasan penolakan..."></textarea>
                                            <div class="flex items-center gap-3 mt-4">
                                                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-xl">Tolak Pengajuan</button>
                                                <button type="button" onclick="document.getElementById('reject-modal-{{ $leave->id }}').classList.add('hidden')" class="px-4 py-2 text-slate-600 text-sm">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 block text-center">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            Belum ada pengajuan cuti
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection