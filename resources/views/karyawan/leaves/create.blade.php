<!-- resources/views/karyawan/leaves/create.blade.php -->
@extends('layouts.karyawan')

@section('title', 'Buat Pengajuan Cuti / Izin')

@section('content')
<div x-data="{
    leaveType: 'cuti_tahunan',
    startDate: '',
    endDate: '',
    remainingAnnual: {{ $remainingAnnual }},
    get totalDays() {
        if (!this.startDate || !this.endDate) return 0;
        const start = new Date(this.startDate);
        const end = new Date(this.endDate);
        if (end < start) return 0;
        const diffTime = Math.abs(end - start);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    }
}" class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Formulir Cuti & Izin</h1>
            <p class="text-xs text-slate-500 mt-1">Ajukan permohonan ketidakhadiran kerja kepada tim HR</p>
        </div>
        <a href="{{ route('leaves.my') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
        <form action="{{ route('leaves.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Permohonan</label>
                <select name="leave_type" x-model="leaveType" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 font-medium">
                    <option value="cuti_tahunan">Cuti Tahunan (Mengurangi Kuota)</option>
                    <option value="sakit">Izin Sakit</option>
                    <option value="izin">Izin Keperluan Pribadi</option>
                    <option value="cuti_melahirkan">Cuti Melahirkan</option>
                    <option value="cuti_penting">Cuti Khusus / Acara Keluarga Penting</option>
                    <option value="lainnya">Lainnya</option>
                </select>

                <div x-show="leaveType === 'cuti_tahunan'" class="mt-2 p-3 rounded-xl bg-blue-50 border border-blue-100 text-xs text-brand-800 flex items-center justify-between">
                    <span>Sisa Kuota Cuti Tahunan:</span>
                    <span class="font-bold text-brand-900">{{ $remainingAnnual }} Hari</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" x-model="startDate" min="{{ now()->toDateString() }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="end_date" x-model="endDate" :min="startDate || '{{ now()->toDateString() }}'" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                </div>
            </div>

            <div x-show="totalDays > 0" class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                <span class="text-slate-600 font-medium">Estimasi Total Durasi:</span>
                <span class="font-mono font-bold text-brand-700 text-sm" x-text="totalDays + ' Hari Kalender'"></span>
            </div>

            <div x-show="leaveType === 'cuti_tahunan' && totalDays > remainingAnnual"
                 class="p-3.5 rounded-xl bg-red-50 border border-red-100 text-xs text-red-700 flex items-start gap-2">
                <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Durasi pengajuan (<strong x-text="totalDays"></strong> hari) melebihi sisa kuota cuti tahunan kamu (<strong>{{ $remainingAnnual }}</strong> hari).</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Alasan / Catatan Pengajuan</label>
                <textarea name="reason" rows="4" required placeholder="Tuliskan alasan permohonan izin atau cuti secara jelas..."
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600"></textarea>
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="submit"
                        :disabled="leaveType === 'cuti_tahunan' && totalDays > remainingAnnual"
                        class="flex-1 py-3 px-4 bg-brand-600 hover:bg-brand-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-600/20 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Kirim Permohonan ke HR
                </button>
                <a href="{{ route('leaves.my') }}" class="px-5 py-3 border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
