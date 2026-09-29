<!-- resources/views/karyawan/leaves/index.blade.php -->
@extends('layouts.karyawan')

@section('title', 'Cuti & Izin Saya')

@section('content')
<div x-data="{
    openModal: false,
    leaveType: 'cuti_tahunan',
    startDate: '',
    endDate: '',
    reason: '',
    remainingAnnual: {{ $stats['remaining_annual'] }},
    get totalDays() {
        if (!this.startDate || !this.endDate) return 0;
        const start = new Date(this.startDate);
        const end = new Date(this.endDate);
        if (end < start) return 0;
        const diffTime = Math.abs(end - start);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    }
}" class="space-y-6">

    {{-- Top Hero Section --}}
    <div class="gradient-hero rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-medium backdrop-blur-md mb-3 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    LAYANAN MANDIRI CUTI & IZIN
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Pengajuan Cuti & Izin
                </h1>
                <p class="text-blue-100/80 text-sm mt-1">
                    Kelola hak cuti tahunan, pengajuan izin sakit, maupun keperluan izin pribadi secara transparan.
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <button type="button" @click="openModal = true"
                        class="px-5 py-3 rounded-2xl bg-white text-brand-900 font-bold text-sm shadow-lg hover:bg-blue-50 transition transform hover:-translate-y-0.5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajukan Permohonan Cuti
                </button>
            </div>
        </div>

        {{-- Decorative circles --}}
        <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-white/5 pointer-events-none"></div>
        <div class="absolute left-1/3 -top-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-xl"></div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Hak Kuota Tahunan</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ $stats['annual_quota'] }} <span class="text-xs font-semibold text-slate-400">Hari</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Sisa Cuti Tahunan</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600 mt-0.5">{{ $stats['remaining_annual'] }} <span class="text-xs font-semibold text-emerald-500">Hari</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Cuti Terpakai</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ $stats['annual_used'] }} <span class="text-xs font-semibold text-slate-400">Hari</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Izin / Sakit Terpakai</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ $stats['sick_permit_used'] }} <span class="text-xs font-semibold text-slate-400">Hari</span></p>
            </div>
        </div>
    </div>

    {{-- Riwayat Pengajuan Cuti --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900 text-base">Riwayat Permohonan Cuti & Izin</h2>
                <p class="text-xs text-slate-400">Daftar seluruh riwayat pengajuan izin yang pernah kamu kirimkan</p>
            </div>
            @if($stats['pending_count'] > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ $stats['pending_count'] }} Menunggu HR
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="px-5 py-3.5 font-medium">Jenis Pengajuan</th>
                        <th class="px-4 py-3.5 font-medium">Periode Tanggal</th>
                        <th class="px-4 py-3.5 font-medium">Durasi</th>
                        <th class="px-4 py-3.5 font-medium">Alasan / Catatan</th>
                        <th class="px-4 py-3.5 font-medium">Status & Persetujuan</th>
                        <th class="px-5 py-3.5 font-medium text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($leaves as $leave)
                        @php
                            $typeLabel = match($leave->leave_type) {
                                'cuti_tahunan' => 'Cuti Tahunan',
                                'sakit' => 'Izin Sakit',
                                'izin' => 'Izin Keperluan Pribadi',
                                'cuti_melahirkan' => 'Cuti Melahirkan',
                                'cuti_penting' => 'Cuti Khusus / Penting',
                                default => 'Lainnya'
                            };

                            $typeColor = match($leave->leave_type) {
                                'cuti_tahunan' => 'blue',
                                'sakit' => 'purple',
                                'izin' => 'indigo',
                                'cuti_melahirkan' => 'pink',
                                default => 'slate'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-{{ $typeColor }}-50 text-{{ $typeColor }}-700">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-medium text-slate-800">
                                {{ $leave->start_date?->format('d M Y') }} &mdash; {{ $leave->end_date?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-4 font-mono font-bold text-slate-700">
                                {{ $leave->total_days }} Hari
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-600 max-w-xs">
                                <p class="line-clamp-2">{{ $leave->reason ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @if($leave->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disetujui
                                    </span>
                                    @if($leave->approved_at)
                                        <p class="text-[10px] text-slate-400 mt-1">Oleh HR &bull; {{ $leave->approved_at->format('d M Y H:i') }}</p>
                                    @endif
                                @elseif($leave->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Ditolak
                                    </span>
                                    @if($leave->rejection_reason)
                                        <p class="text-xs text-rose-600 mt-1 font-medium bg-rose-50/80 p-2 rounded-lg border border-rose-100">
                                            Catatan HR: {{ $leave->rejection_reason }}
                                        </p>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Review HR
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($leave->status === 'pending')
                                    <form action="{{ route('leaves.cancel', $leave) }}" method="POST" onsubmit="return confirm('Apakah kamu yakin ingin membatalkan pengajuan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition">
                                            Batalkan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-300">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="font-semibold text-slate-700 text-sm">Belum Ada Pengajuan</p>
                                    <p class="text-xs text-slate-400">Kamu belum pernah mengajukan cuti atau izin. Klik tombol di atas untuk membuat pengajuan baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>

    {{-- Interactive Modal: Form Pengajuan Cuti --}}
    <div x-show="openModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="openModal = false"
             class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[92vh]">
            
            {{-- Header Modal --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Buat Permohonan Cuti / Izin</h3>
                    <p class="text-xs text-slate-400">Isi formulir dengan lengkap untuk ditinjau oleh HR</p>
                </div>
                <button type="button" @click="openModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form Content --}}
            <form action="{{ route('leaves.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Permohonan</label>
                    <select name="leave_type" x-model="leaveType" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 font-medium">
                        <option value="cuti_tahunan">Cuti Tahunan (Mengurangi Kuota)</option>
                        <option value="sakit">Izin Sakit</option>
                        <option value="izin">Izin Keperluan Pribadi</option>
                        <option value="cuti_melahirkan">Cuti Melahirkan</option>
                        <option value="cuti_penting">Cuti Khusus / Acara Keluarga Penting</option>
                        <option value="lainnya">Lainnya</option>
                    </select>

                    {{-- Notice info sisa cuti --}}
                    <div x-show="leaveType === 'cuti_tahunan'" class="mt-2 p-2.5 rounded-xl bg-blue-50 border border-blue-100 text-xs text-brand-800 flex items-center justify-between">
                        <span>Sisa Kuota Cuti Tahunan Kamu:</span>
                        <span class="font-bold text-brand-900">{{ $stats['remaining_annual'] }} Hari</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Mulai</label>
                        <input type="date" name="start_date" x-model="startDate" min="{{ now()->toDateString() }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="end_date" x-model="endDate" :min="startDate || '{{ now()->toDateString() }}'" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                    </div>
                </div>

                {{-- Realtime Duration Calculation Badge --}}
                <div x-show="totalDays > 0" class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                    <span class="text-slate-600 font-medium">Estimasi Total Durasi:</span>
                    <span class="font-mono font-bold text-brand-700 text-sm" x-text="totalDays + ' Hari Kalender'"></span>
                </div>

                {{-- Warning jika cuti tahunan melebihi kuota --}}
                <div x-show="leaveType === 'cuti_tahunan' && totalDays > remainingAnnual"
                     class="p-3 rounded-xl bg-red-50 border border-red-100 text-xs text-red-700 flex items-start gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Durasi pengajuan (<strong x-text="totalDays"></strong> hari) melebihi sisa kuota cuti tahunan kamu (<strong>{{ $stats['remaining_annual'] }}</strong> hari).</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Alasan / Keterangan Permohonan</label>
                    <textarea name="reason" x-model="reason" rows="3" required placeholder="Contoh: Mengurus keperluan keluarga mendesak di luar kota..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600"></textarea>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit"
                            :disabled="leaveType === 'cuti_tahunan' && totalDays > remainingAnnual"
                            class="flex-1 py-3 px-4 bg-brand-600 hover:bg-brand-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-600/20 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Kirim Permohonan ke HR
                    </button>
                    <button type="button" @click="openModal = false"
                            class="px-5 py-3 border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
