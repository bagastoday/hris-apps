<!-- resources/views/karyawan/payroll.blade.php -->
@extends('layouts.karyawan')

@section('title', 'Slip Gaji Saya')

@push('styles')
<style>
    @media print {
        nav, footer, .no-print, header, .gradient-hero, .grid-stats {
            display: none !important;
        }
        body, main {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #payslip-modal-container {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            background: white !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            z-index: 9999 !important;
        }
        #payslip-voucher {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            padding: 24px !important;
            width: 100% !important;
            max-width: 100% !important;
        }
    }
</style>
@endpush

@section('content')
<div x-data="{
    detailModal: false,
    selectedSlip: null,
    viewSlip(slip) {
        this.selectedSlip = slip;
        this.detailModal = true;
    },
    printSlip() {
        window.print();
    }
}" class="space-y-6">

    {{-- Top Hero Section --}}
    <div class="gradient-hero rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden no-print">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-medium backdrop-blur-md mb-3 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    LAYANAN PAYROLL MANDIRI
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Slip Gaji Saya
                </h1>
                <p class="text-blue-100/80 text-sm mt-1">
                    Arsip rincian penerimaan gaji bulanan resmi yang diterbitkan oleh tim Finance & HR.
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <div class="px-5 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-right">
                    <p class="text-xs text-blue-200 font-medium">Gaji Pokok Terdaftar</p>
                    <p class="text-lg sm:text-xl font-mono font-bold text-white mt-0.5">
                        Rp {{ number_format((float) $stats['base_salary'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Decorative circles --}}
        <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-white/5 pointer-events-none"></div>
        <div class="absolute left-1/3 -top-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-xl"></div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 grid-stats no-print">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Gaji Bersih Terakhir</p>
                <p class="text-base sm:text-lg font-mono font-bold text-slate-900 mt-0.5">
                    Rp {{ number_format((float) $stats['latest_net_pay'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Diterima ({{ now()->year }})</p>
                <p class="text-base sm:text-lg font-mono font-bold text-emerald-600 mt-0.5">
                    Rp {{ number_format((float) $stats['total_paid_year'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Slip Gaji Tersedia</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">
                    {{ $stats['total_slips'] }} <span class="text-xs font-semibold text-slate-400">Periode</span>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Status Rekening</p>
                <p class="text-xs font-bold text-slate-800 mt-1">
                    Aktif &bull; Mandiri Transfer
                </p>
            </div>
        </div>
    </div>

    {{-- Tabel Slip Gaji --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden no-print">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900 text-base">Riwayat Dokumen Slip Gaji</h2>
                <p class="text-xs text-slate-400">Pilih salah satu slip untuk melihat rincian kalkulasi dan mencetak dokumen</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="px-5 py-3.5 font-medium">Periode Payroll</th>
                        <th class="px-4 py-3.5 font-medium text-right">Gaji Pokok</th>
                        <th class="px-4 py-3.5 font-medium text-right">Tunjangan</th>
                        <th class="px-4 py-3.5 font-medium text-right">Potongan</th>
                        <th class="px-4 py-3.5 font-medium text-right">Take Home Pay</th>
                        <th class="px-5 py-3.5 font-medium text-center">Status</th>
                        <th class="px-5 py-3.5 font-medium text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($payslips as $slip)
                        @php
                            $formattedPeriod = \Carbon\Carbon::createFromFormat('!Y-m', $slip->payrollRun->period)->translatedFormat('F Y');
                            $slipData = [
                                'id' => $slip->id,
                                'period' => $formattedPeriod,
                                'raw_period' => $slip->payrollRun->period,
                                'employee_name' => $slip->employee_name,
                                'employee_code' => $slip->employee_code,
                                'department' => $slip->department_name,
                                'position' => $employee->position->name ?? 'Pegawai',
                                'base_salary' => $slip->base_salary,
                                'allowance' => $slip->allowance,
                                'deduction' => $slip->deduction,
                                'net_pay' => $slip->net_pay,
                                'payment_status' => $slip->payment_status,
                                'paid_at' => $slip->paid_at ? $slip->paid_at->format('d M Y H:i') : null,
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900">{{ $formattedPeriod }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">REF-PR-{{ $slip->payroll_run_id }}-{{ $slip->id }}</p>
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-slate-700">
                                Rp {{ number_format((float) $slip->base_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-emerald-600">
                                + Rp {{ number_format((float) $slip->allowance, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-rose-600">
                                - Rp {{ number_format((float) $slip->deduction, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format((float) $slip->net_pay, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($slip->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Dibayar
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Diproses
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button type="button"
                                        @click="viewSlip({{ json_encode($slipData) }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-brand-200 bg-brand-50 text-brand-700 hover:bg-brand-100 text-xs font-semibold transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Rincian Slip
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="font-semibold text-slate-700 text-sm">Belum Ada Slip Gaji</p>
                                    <p class="text-xs text-slate-400">Belum ada periode payroll yang diproses dan dirilis untuk akun kamu.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Interactive Modal: Rincian & Cetak Slip Gaji --}}
    <div x-show="detailModal" x-cloak
         id="payslip-modal-container"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto">
        <div @click.away="detailModal = false"
             class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col my-auto">
            
            {{-- Top Modal Bar (hidden on print) --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between no-print bg-slate-50/50">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pratinjau Dokumen Resmi</span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="printSlip()"
                            class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-sm shadow-brand-600/30">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak / Simpan PDF
                    </button>
                    <button type="button" @click="detailModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Printable Slip Voucher Card --}}
            <div id="payslip-voucher" class="p-8 space-y-6 bg-white text-slate-800">
                
                {{-- Kop Surat Perusahaan --}}
                <div class="flex items-start justify-between border-b-2 border-slate-800 pb-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-7 h-7 rounded-lg bg-brand-600 flex items-center justify-center text-white font-bold text-xs">TC</div>
                            <span class="font-extrabold text-lg tracking-tight text-slate-900">Talenta<span class="text-brand-600">Core</span> HRIS</span>
                        </div>
                        <p class="text-xs text-slate-500">PT Talenta Digital Indonesia &bull; Divisi Manajemen SDM & Payroll</p>
                        <p class="text-[11px] text-slate-400">Gedung Perkantoran Talenta, Jakarta Selatan &bull; hris@talentacore.local</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 rounded bg-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-800 border border-slate-200">
                            SLIP GAJI RESMI
                        </span>
                        <p class="text-xs font-mono text-slate-500 mt-1.5" x-text="'Ref: SLIP-' + (selectedSlip ? selectedSlip.id : '')"></p>
                    </div>
                </div>

                {{-- Informasi Pegawai & Periode --}}
                <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="space-y-1.5">
                        <div class="flex">
                            <span class="w-24 text-slate-400 font-medium">Nama Pegawai</span>
                            <span class="font-bold text-slate-900" x-text="selectedSlip ? selectedSlip.employee_name : ''"></span>
                        </div>
                        <div class="flex">
                            <span class="w-24 text-slate-400 font-medium">NIK / Kode</span>
                            <span class="font-mono font-semibold text-slate-700" x-text="selectedSlip ? selectedSlip.employee_code : ''"></span>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex">
                            <span class="w-24 text-slate-400 font-medium">Departemen</span>
                            <span class="font-semibold text-slate-800" x-text="selectedSlip ? selectedSlip.department : ''"></span>
                        </div>
                        <div class="flex">
                            <span class="w-24 text-slate-400 font-medium">Periode Gaji</span>
                            <span class="font-bold text-brand-700" x-text="selectedSlip ? selectedSlip.period : ''"></span>
                        </div>
                    </div>
                </div>

                {{-- Tabel Rincian Penghasilan & Potongan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                    {{-- Kolom Penerimaan --}}
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-emerald-50/70 px-4 py-2 border-b border-slate-200 flex justify-between font-bold text-emerald-900">
                            <span>A. PENERIMAAN</span>
                            <span>JUMLAH</span>
                        </div>
                        <div class="p-4 space-y-2.5">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Gaji Pokok</span>
                                <span class="font-mono font-semibold text-slate-800" x-text="'Rp ' + (selectedSlip ? Number(selectedSlip.base_salary).toLocaleString('id-ID') : 0)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Tunjangan Kinerja / Jabatan</span>
                                <span class="font-mono font-semibold text-emerald-600" x-text="'Rp ' + (selectedSlip ? Number(selectedSlip.allowance).toLocaleString('id-ID') : 0)"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex justify-between font-bold text-slate-900">
                                <span>Total Penerimaan Bruto</span>
                                <span class="font-mono" x-text="'Rp ' + (selectedSlip ? (Number(selectedSlip.base_salary) + Number(selectedSlip.allowance)).toLocaleString('id-ID') : 0)"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Potongan --}}
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-rose-50/70 px-4 py-2 border-b border-slate-200 flex justify-between font-bold text-rose-900">
                            <span>B. POTONGAN</span>
                            <span>JUMLAH</span>
                        </div>
                        <div class="p-4 space-y-2.5">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Potongan Terhitung</span>
                                <span class="font-mono font-semibold text-rose-600" x-text="'Rp ' + (selectedSlip ? Number(selectedSlip.deduction).toLocaleString('id-ID') : 0)"></span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>BPJS & Pajak PPh 21</span>
                                <span>Termasuk</span>
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex justify-between font-bold text-slate-900">
                                <span>Total Potongan</span>
                                <span class="font-mono text-rose-600" x-text="'Rp ' + (selectedSlip ? Number(selectedSlip.deduction).toLocaleString('id-ID') : 0)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Take Home Pay Highlight --}}
                <div class="bg-brand-50 border border-brand-200 rounded-2xl p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-brand-900 uppercase tracking-wider">GAJI BERSIH (TAKE HOME PAY)</p>
                        <p class="text-[11px] text-brand-700 mt-0.5">Total penerimaan setelah penyesuaian potongan</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl sm:text-2xl font-mono font-black text-brand-900"
                           x-text="'Rp ' + (selectedSlip ? Number(selectedSlip.net_pay).toLocaleString('id-ID') : 0)"></p>
                        <span class="inline-block px-2 py-0.5 mt-1 rounded text-[10px] font-bold uppercase tracking-wider"
                              :class="selectedSlip && selectedSlip.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                              x-text="selectedSlip && selectedSlip.payment_status === 'paid' ? 'LUNAS / DIBAYARKAN' : 'MENUNGGU TRANSFER'"></span>
                    </div>
                </div>

                {{-- Tanda Tangan & Note Dokumen --}}
                <div class="pt-6 border-t border-slate-200 grid grid-cols-2 text-center text-xs text-slate-500">
                    <div>
                        <p class="mb-14">Diterima oleh Pegawai,</p>
                        <p class="font-bold text-slate-900" x-text="selectedSlip ? selectedSlip.employee_name : ''"></p>
                    </div>
                    <div>
                        <p class="mb-14">Bagian Keuangan & HR,</p>
                        <p class="font-bold text-slate-900">HR & Finance TalentaCore</p>
                    </div>
                </div>

                <p class="text-[10px] text-center text-slate-400 italic pt-2">
                    Dokumen ini dicetak secara sah dan otomatis melalui sistem komputerisasi TalentaCore HRIS tanpa memerlukan stempel basah.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
