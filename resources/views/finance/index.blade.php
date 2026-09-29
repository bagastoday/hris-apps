@extends('layouts.admin')

@section('title', 'Payroll')
@section('page-title', 'Payroll Karyawan')
@section('page-subtitle', 'Kelola perhitungan dan pencatatan pembayaran gaji')

@section('content')
@php
    $statusLabels = ['draft' => 'Draft', 'processed' => 'Diproses', 'paid' => 'Lunas'];
    $totalNetPay = $payroll?->items->sum('net_pay') ?? 0;
    $paidCount = $payroll?->items->where('payment_status', 'paid')->count() ?? 0;
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h2 class="text-lg font-semibold text-slate-900">Payroll Bulanan</h2>
        <p class="text-sm text-slate-500">Gaji pokok + tunjangan - potongan. Transfer dilakukan di luar aplikasi.</p>
    </div>
    <form method="GET" action="{{ route('finance.index') }}" class="flex items-center gap-2">
        <label for="period" class="text-sm font-medium text-slate-600">Periode</label>
        <input id="period" type="month" name="period" value="{{ $period }}"
               class="rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        <button class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Tampilkan</button>
    </form>
</div>

@if($errors->any())
    <div class="rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-slate-400">Status Periode</p>
        <p class="mt-2 text-xl font-bold text-slate-900">{{ $payroll ? $statusLabels[$payroll->status] : 'Belum dibuat' }}</p>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-slate-400">Total Take Home Pay</p>
        <p class="mt-2 text-xl font-bold text-emerald-700">Rp {{ number_format((float) $totalNetPay, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-slate-400">Pembayaran Tercatat</p>
        <p class="mt-2 text-xl font-bold text-slate-900">{{ $paidCount }} / {{ $payroll?->items->count() ?? 0 }} pegawai</p>
    </div>
</div>

<section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-semibold text-slate-900">Rincian {{ \Carbon\Carbon::createFromFormat('!Y-m', $period)->translatedFormat('F Y') }}</h3>
            <p class="mt-1 text-xs text-slate-500">Nilai gaji disalin saat draft dibuat; perubahan gaji pokok selanjutnya tidak mengubah payroll yang sudah ada.</p>
        </div>
        @if(!$payroll)
            <form method="POST" action="{{ route('finance.payroll.store') }}">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                <button class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
                    Buat Draft Payroll
                </button>
            </form>
        @endif
    </div>

    @if($payroll)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Pegawai</th>
                        <th class="px-4 py-3 text-right">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right">Tunjangan</th>
                        <th class="px-4 py-3 text-right">Potongan</th>
                        <th class="px-4 py-3 text-right">Take Home Pay</th>
                        <th class="px-4 py-3 text-center">Pembayaran</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payroll->items as $item)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="font-medium text-slate-900">{{ $item->employee_name }}</p>
                                <p class="text-xs text-slate-400">{{ $item->employee_code }} · {{ $item->department_name ?? 'Tanpa Departemen' }}</p>
                                @if($payroll->status === 'draft')
                                    <form id="payroll-item-{{ $item->id }}" method="POST" action="{{ route('finance.payroll.items.update', [$payroll, $item]) }}">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right whitespace-nowrap">Rp {{ number_format((float) $item->base_salary, 0, ',', '.') }}</td>
                            @if($payroll->status === 'draft')
                                <td class="px-2 py-3">
                                    <input form="payroll-item-{{ $item->id }}" type="number" name="allowance" min="0" step="1" value="{{ (int) $item->allowance }}" required
                                           class="w-32 rounded-lg border-slate-200 text-right text-sm focus:border-brand-500 focus:ring-brand-500">
                                </td>
                                <td class="px-2 py-3">
                                    <input form="payroll-item-{{ $item->id }}" type="number" name="deduction" min="0" step="1" value="{{ (int) $item->deduction }}" required
                                           class="w-32 rounded-lg border-slate-200 text-right text-sm focus:border-brand-500 focus:ring-brand-500">
                                </td>
                            @else
                                <td class="px-4 py-4 text-right whitespace-nowrap">Rp {{ number_format((float) $item->allowance, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">Rp {{ number_format((float) $item->deduction, 0, ',', '.') }}</td>
                            @endif
                            <td class="px-4 py-4 text-right font-semibold text-slate-900 whitespace-nowrap">Rp {{ number_format((float) $item->net_pay, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $item->payment_status === 'paid' ? 'Dibayar' : 'Belum dibayar' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right whitespace-nowrap">
                                @if($payroll->status === 'draft')
                                    <button form="payroll-item-{{ $item->id }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Simpan</button>
                                @elseif($item->payment_status !== 'paid')
                                    <form method="POST" action="{{ route('finance.payroll.items.paid', [$payroll, $item]) }}" onsubmit="return confirm('Catat pembayaran sudah dilakukan?')">
                                        @csrf
                                        <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Tandai dibayar</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">{{ $item->paid_at?->format('d M Y H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Belum ada rincian payroll.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payroll->status === 'draft')
            <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">Pastikan semua komponen sudah diperiksa. Payroll yang diproses tidak dapat diubah.</p>
                <form method="POST" action="{{ route('finance.payroll.finalize', $payroll) }}" onsubmit="return confirm('Proses payroll? Rincian gaji akan dikunci untuk periode ini.')">
                    @csrf
                    <button class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Proses & Kunci Payroll</button>
                </form>
            </div>
        @endif
    @else
        <p class="px-5 py-10 text-center text-sm text-slate-500">Belum ada payroll untuk periode ini. Atur gaji pokok terlebih dahulu sebelum membuat draft.</p>
    @endif
</section>

<section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h3 class="font-semibold text-slate-900">Riwayat Payroll</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-wide text-slate-400">
                <tr><th class="px-5 py-3">Periode</th><th class="px-4 py-3">Pegawai</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentPayrolls as $recent)
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ \Carbon\Carbon::createFromFormat('!Y-m', $recent->period)->translatedFormat('F Y') }}</td>
                        <td class="px-4 py-3">{{ $recent->items_count }}</td>
                        <td class="px-4 py-3">{{ $statusLabels[$recent->status] }}</td>
                        <td class="px-4 py-3 text-right"><a class="font-semibold text-brand-700 hover:underline" href="{{ route('finance.index', ['period' => $recent->period]) }}">Buka</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">Belum ada riwayat payroll.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
