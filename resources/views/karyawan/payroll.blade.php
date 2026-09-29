<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji Saya - TalentaCore</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <div>
                <p class="font-bold text-slate-900">Talenta<span class="text-blue-600">Core</span></p>
                <p class="text-[10px] uppercase tracking-wider text-slate-400">Portal Pegawai</p>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()?->isFinance())
                    <a href="{{ route('finance.index') }}" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">Dashboard Finance</a>
                @elseif(auth()->user()?->hasHrAdminAccess())
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">Dashboard HR</a>
                @endif
                <a href="{{ route('karyawan.home') }}" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200">Portal Absensi</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-sm font-medium text-slate-500 hover:text-red-600">Keluar</button></form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-5 px-4 py-8 sm:px-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Slip Gaji Saya</h1>
            <p class="mt-1 text-sm text-slate-500">Rincian payroll pribadi yang sudah diproses.</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-wide text-slate-400">
                        <tr><th class="px-5 py-3.5">Periode</th><th class="px-4 py-3.5 text-right">Gaji Pokok</th><th class="px-4 py-3.5 text-right">Tunjangan</th><th class="px-4 py-3.5 text-right">Potongan</th><th class="px-4 py-3.5 text-right">Diterima</th><th class="px-5 py-3.5 text-center">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payslips as $payslip)
                            <tr>
                                <td class="px-5 py-4 font-medium text-slate-900">{{ \Carbon\Carbon::createFromFormat('!Y-m', $payslip->payrollRun->period)->translatedFormat('F Y') }}</td>
                                <td class="px-4 py-4 text-right">Rp {{ number_format((float) $payslip->base_salary, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right">Rp {{ number_format((float) $payslip->allowance, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right">Rp {{ number_format((float) $payslip->deduction, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right font-semibold">Rp {{ number_format((float) $payslip->net_pay, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $payslip->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $payslip->payment_status === 'paid' ? 'Dibayar' : 'Menunggu pembayaran' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada slip gaji yang tersedia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
