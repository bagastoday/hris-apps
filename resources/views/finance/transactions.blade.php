@extends('layouts.admin')

@section('title', 'Kas & Pengeluaran')
@section('page-title', 'Kas & Pengeluaran')
@section('page-subtitle', 'Catat pemasukan, pengeluaran, dan reimbursement karyawan')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <form method="GET" action="{{ route('finance.transactions') }}" class="flex items-center gap-2">
        <label for="period" class="text-sm font-medium text-slate-600">Periode transaksi</label>
        <input id="period" type="month" name="period" value="{{ $period }}" class="rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        <button class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Tampilkan</button>
    </form>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('finance.exports.cashflow', ['period' => $period]) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ekspor Arus Kas</a>
        <a href="{{ route('finance.exports.payroll', ['period' => $period]) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ekspor Payroll</a>
    </div>
</div>

@if($errors->any())
    <div class="mt-5 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
@if(session('success'))
    <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
@endif

<div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-slate-400">Pemasukan Tercatat</p>
        <p class="mt-2 text-xl font-bold text-emerald-700">Rp {{ number_format((float) $paidIncome, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-xs uppercase tracking-wide text-slate-400">Pengeluaran Dibayar</p>
        <p class="mt-2 text-xl font-bold text-slate-900">Rp {{ number_format((float) $paidExpenses, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
        <p class="text-xs uppercase tracking-wide text-amber-700">Pengeluaran Menunggu</p>
        <p class="mt-2 text-xl font-bold text-amber-900">Rp {{ number_format((float) $pendingExpenses, 0, ',', '.') }}</p>
    </div>
</div>

<section class="mt-5 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <h2 class="font-semibold text-slate-900">Catat transaksi</h2>
    <form method="POST" action="{{ route('finance.transactions.store') }}" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @csrf
        <input type="hidden" name="period" value="{{ $period }}">
        <div>
            <label for="type" class="mb-1 block text-sm font-medium text-slate-700">Arus</label>
            <select id="type" name="type" required class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="expense" @selected(old('type', 'expense') === 'expense')>Pengeluaran</option>
                <option value="income" @selected(old('type') === 'income')>Pemasukan</option>
            </select>
        </div>
        <div>
            <label for="category" class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
            <select id="category" name="category" required class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="reimbursement" @selected(old('category') === 'reimbursement')>Reimbursement karyawan</option>
                <option value="operasional" @selected(old('category') === 'operasional')>Operasional</option>
                <option value="pendapatan" @selected(old('category') === 'pendapatan')>Pendapatan</option>
                <option value="pengembalian" @selected(old('category') === 'pengembalian')>Pengembalian dana</option>
                <option value="lainnya" @selected(old('category') === 'lainnya')>Lainnya</option>
            </select>
        </div>
        <div>
            <label for="employee_id" class="mb-1 block text-sm font-medium text-slate-700">Karyawan <span class="text-slate-400">(untuk reimbursement)</span></label>
            <select id="employee_id" name="employee_id" class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">Pilih karyawan</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>{{ $employee->employee_code }} · {{ $employee->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="amount" class="mb-1 block text-sm font-medium text-slate-700">Nominal (Rp)</label>
            <input id="amount" type="number" name="amount" min="1" step="1" value="{{ old('amount') }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div class="md:col-span-2">
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
            <input id="description" type="text" name="description" maxlength="255" value="{{ old('description') }}" required placeholder="Contoh: penggantian bensin perjalanan dinas" class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="transaction_date" class="mb-1 block text-sm font-medium text-slate-700">Tanggal dicatat</label>
            <input id="transaction_date" type="date" name="transaction_date" value="{{ old('transaction_date', $defaultTransactionDate) }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status pembayaran</label>
            <select id="status" name="status" required class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="pending" @selected(old('status', 'pending') === 'pending')>Belum dibayar</option>
                <option value="paid" @selected(old('status') === 'paid')>Sudah dibayar</option>
            </select>
        </div>
        <div id="paid-date-field" class="hidden">
            <label for="paid_date" class="mb-1 block text-sm font-medium text-slate-700">Tanggal dibayar</label>
            <input id="paid_date" type="date" name="paid_date" value="{{ old('paid_date', now()->toDateString()) }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div class="flex items-end">
            <button class="w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Simpan Transaksi</button>
        </div>
    </form>
</section>

<section class="mt-5 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold text-slate-900">Transaksi {{ \Carbon\Carbon::createFromFormat('!Y-m', $period)->translatedFormat('F Y') }}</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-wide text-slate-400">
                <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Arus / Kategori</th><th class="px-4 py-3">Keterangan</th><th class="px-4 py-3">Karyawan</th><th class="px-4 py-3 text-right">Nominal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-4">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-4">
                            <span class="font-semibold {{ $transaction->type === 'income' ? 'text-emerald-700' : 'text-slate-800' }}">{{ $transaction->type === 'income' ? 'Masuk' : 'Keluar' }}</span>
                            <p class="text-xs text-slate-500">{{ ucfirst($transaction->category) }}</p>
                        </td>
                        <td class="px-4 py-4">{{ $transaction->description }}</td>
                        <td class="px-4 py-4">{{ $transaction->employee?->full_name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-right font-medium">Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-4">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $transaction->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $transaction->status === 'paid' ? 'Dibayar' : 'Belum dibayar' }}</span>
                            @if($transaction->paid_date)<p class="mt-1 text-xs text-slate-400">{{ $transaction->paid_date->format('d/m/Y') }}</p>@endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            @if($transaction->status === 'pending')
                                <form method="POST" action="{{ route('finance.transactions.paid', $transaction) }}" class="flex items-center justify-end gap-2">
                                    @csrf
                                    <input type="date" name="paid_date" value="{{ now()->toDateString() }}" required aria-label="Tanggal pembayaran" class="w-36 rounded-lg border-slate-200 text-xs">
                                    <button class="whitespace-nowrap rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Tandai dibayar</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">Tercatat</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Belum ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<script>
    const transactionStatus = document.getElementById('status');
    const paidDateField = document.getElementById('paid-date-field');
    const paidDateInput = document.getElementById('paid_date');
    const transactionType = document.getElementById('type');
    const categorySelect = document.getElementById('category');
    const employeeSelect = document.getElementById('employee_id');

    function updateTransactionFields() {
        const isPaid = transactionStatus.value === 'paid';
        paidDateField.classList.toggle('hidden', !isPaid);
        paidDateInput.required = isPaid;
        const isIncome = transactionType.value === 'income';
        categorySelect.querySelectorAll('option').forEach((option) => {
            option.hidden = isIncome
                ? ['pendapatan', 'pengembalian', 'lainnya'].indexOf(option.value) === -1
                : ['reimbursement', 'operasional', 'lainnya'].indexOf(option.value) === -1;
        });
        if ((isIncome && categorySelect.value === 'reimbursement') || (!isIncome && ['pendapatan', 'pengembalian'].indexOf(categorySelect.value) !== -1)) {
            categorySelect.value = isIncome ? 'pendapatan' : 'operasional';
        }
        employeeSelect.required = !isIncome && categorySelect.value === 'reimbursement';
        employeeSelect.disabled = isIncome || categorySelect.value !== 'reimbursement';
    }

    transactionStatus.addEventListener('change', updateTransactionFields);
    transactionType.addEventListener('change', updateTransactionFields);
    categorySelect.addEventListener('change', updateTransactionFields);
    updateTransactionFields();
</script>
@endsection