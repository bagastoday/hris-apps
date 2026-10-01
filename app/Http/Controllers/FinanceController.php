<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\FinanceTransaction;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FinanceController extends Controller
{
    public function reimbursementApprovals(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        ]);
        $status = $validated['status'] ?? 'pending';
        $tickets = Ticket::with(['user.employee.department', 'reimbursementReviewer', 'financeTransaction'])
            ->where('category', 'reimburse')
            ->where('reimbursement_status', $status)
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $stats = [
            'pending' => Ticket::where('category', 'reimburse')->where('reimbursement_status', 'pending')->count(),
            'approved' => Ticket::where('category', 'reimburse')->where('reimbursement_status', 'approved')->count(),
            'rejected' => Ticket::where('category', 'reimburse')->where('reimbursement_status', 'rejected')->count(),
        ];

        return view('finance.reimbursements', compact('tickets', 'status', 'stats'));
    }

    public function decideReimbursement(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => ['required_if:decision,rejected', 'nullable', 'string', 'max:1000'],
        ], [
            'review_note.required_if' => 'Alasan penolakan wajib diisi.',
        ]);

        DB::transaction(function () use ($ticket, $validated) {
            $lockedTicket = Ticket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedTicket->category === 'reimburse', 404);
            abort_unless($lockedTicket->reimbursement_status === 'pending', 409, 'Pengajuan ini sudah diputuskan.');

            if ($validated['decision'] === 'approved') {
                $lockedTicket->loadMissing('user.employee');
                abort_if(
                    (float) $lockedTicket->reimbursement_amount <= 0 || !$lockedTicket->attachment || !$lockedTicket->user?->employee,
                    422,
                    'Pengajuan reimbursement belum memiliki nominal, foto bukti, atau data karyawan yang valid.'
                );

                $transaction = FinanceTransaction::create([
                    'type' => 'expense',
                    'category' => 'reimbursement',
                    'description' => $lockedTicket->title,
                    'employee_id' => $lockedTicket->user->employee->id,
                    'amount' => $lockedTicket->reimbursement_amount,
                    'transaction_date' => today()->toDateString(),
                    'status' => 'pending',
                    'created_by' => Auth::id(),
                ]);
                $lockedTicket->finance_transaction_id = $transaction->id;
            }

            $lockedTicket->reimbursement_status = $validated['decision'];
            $lockedTicket->reimbursement_review_note = $validated['review_note'] ?? null;
            $lockedTicket->reimbursement_reviewed_by = Auth::id();
            $lockedTicket->reimbursement_reviewed_at = now();
            $lockedTicket->save();

            ActivityLog::record(
                'review_reimbursement',
                "{$validated['decision']} reimbursement tiket #{$lockedTicket->ticket_code} (Rp ".number_format((float) $lockedTicket->reimbursement_amount, 0, ',', '.').')',
                $lockedTicket
            );
        });

        return redirect()->route('finance.reimbursements', ['status' => $validated['decision']])
            ->with('success', $validated['decision'] === 'approved'
                ? 'Reimbursement disetujui dan dicatat sebagai pengeluaran yang belum dibayar.'
                : 'Pengajuan reimbursement ditolak.');
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? now()->format('Y-m');
        $payroll = PayrollRun::with(['items' => fn ($query) => $query->orderBy('employee_name')])
            ->where('period', $period)
            ->first();
        $recentPayrolls = PayrollRun::withCount('items')
            ->orderByDesc('period')
            ->take(12)
            ->get();
        $draftPayrollCount = PayrollRun::where('status', 'draft')->count();
        $unpaidPayrollCount = PayrollItem::whereHas('payrollRun', fn ($query) => $query
            ->whereIn('status', ['processed', 'paid']))
            ->where('payment_status', '!=', 'paid')
            ->count();

        return view('finance.index', compact(
            'period',
            'payroll',
            'recentPayrolls',
            'draftPayrollCount',
            'unpaidPayrollCount'
        ));
    }

    public function salaries(): View
    {
        $employees = Employee::active()
            ->with('department')
            ->orderBy('full_name')
            ->get();

        return view('finance.salaries', compact('employees'));
    }

    public function transactions(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? now()->format('Y-m');
        $startDate = Carbon::createFromFormat('!Y-m', $period)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('!Y-m', $period)->endOfMonth()->toDateString();
        $defaultTransactionDate = $period === now()->format('Y-m') ? now()->toDateString() : $startDate;
        $transactions = FinanceTransaction::with('employee')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
        $employees = Employee::active()->orderBy('full_name')->get(['id', 'employee_code', 'full_name']);
        $paidIncome = $transactions->where('type', 'income')->where('status', 'paid')->sum('amount');
        $paidExpenses = $transactions->where('type', 'expense')->where('status', 'paid')->sum('amount');
        $pendingExpenses = $transactions->where('type', 'expense')->where('status', 'pending')->sum('amount');

        return view('finance.transactions', compact(
            'period',
            'defaultTransactionDate',
            'transactions',
            'employees',
            'paidIncome',
            'paidExpenses',
            'pendingExpenses'
        ));
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category' => [
                'required',
                'string',
                'max:100',
                Rule::in($request->input('type') === 'income'
                    ? ['pendapatan', 'pengembalian', 'lainnya']
                    : ['reimbursement', 'operasional', 'lainnya']),
            ],
            'description' => ['required', 'string', 'max:255'],
            'employee_id' => ['nullable', 'required_if:category,reimbursement', 'exists:employees,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:9999999999999'],
            'transaction_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['pending', 'paid'])],
            'paid_date' => ['required_if:status,paid', 'nullable', 'date', 'before_or_equal:today'],
        ]);
        $period = $validated['period'];
        unset($validated['period']);

        FinanceTransaction::create([
            ...$validated,
            'employee_id' => $validated['category'] === 'reimbursement' ? $validated['employee_id'] : null,
            'created_by' => Auth::id(),
            'paid_by' => $validated['status'] === 'paid' ? Auth::id() : null,
        ]);

        return redirect()->route('finance.transactions', ['period' => $period])
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    public function markTransactionPaid(Request $request, FinanceTransaction $transaction): RedirectResponse
    {
        abort_if($transaction->status === 'paid', 409, 'Transaksi ini sudah dibayar.');
        $validated = $request->validate([
            'paid_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $transaction->update([
            'status' => 'paid',
            'paid_date' => $validated['paid_date'],
            'paid_by' => Auth::id(),
        ]);

        return redirect()->route('finance.transactions', ['period' => Carbon::parse($transaction->transaction_date)->format('Y-m')])
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function exportCashflow(Request $request)
    {
        $period = $request->validate(['period' => ['required', 'date_format:Y-m']])['period'];
        $startDate = Carbon::createFromFormat('!Y-m', $period)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('!Y-m', $period)->endOfMonth()->toDateString();
        $rows = collect();

        FinanceTransaction::with('employee')
            ->where('status', 'paid')
            ->whereDate('paid_date', '>=', $startDate)
            ->whereDate('paid_date', '<=', $endDate)
            ->get()
            ->each(function (FinanceTransaction $transaction) use ($rows) {
                $rows->push([
                    Carbon::parse($transaction->paid_date)->format('Y-m-d'),
                    $transaction->type === 'income' ? 'Masuk' : 'Keluar',
                    'Transaksi',
                    $transaction->category,
                    $transaction->employee?->full_name ?? '-',
                    $transaction->description,
                    $transaction->type === 'income' ? (float) $transaction->amount : 0,
                    $transaction->type === 'expense' ? (float) $transaction->amount : 0,
                ]);
            });

        PayrollItem::with('payrollRun')
            ->where('payment_status', 'paid')
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->get()
            ->each(function (PayrollItem $item) use ($rows) {
                $rows->push([
                    $item->paid_at->format('Y-m-d'),
                    'Keluar',
                    'Payroll '.$item->payrollRun->period,
                    'Gaji',
                    $item->employee_name,
                    'Pembayaran payroll '.$item->payrollRun->period,
                    0,
                    (float) $item->net_pay,
                ]);
            });

        $rows = $rows->sortBy(0)->values();
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Arus Kas');
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Laporan Arus Kas '.$period);
        $sheet->fromArray(['Tanggal Dibayar', 'Arus', 'Sumber', 'Kategori', 'Pegawai', 'Keterangan', 'Uang Masuk', 'Uang Keluar'], null, 'A3');

        foreach ($rows as $index => $row) {
            $this->writeSpreadsheetRow($sheet, $row, $index + 4, [6, 7]);
        }

        return $this->downloadSpreadsheet($spreadsheet, "arus-kas-{$period}.xlsx", 'A3:H'.max(3, $rows->count() + 3));
    }

    public function exportPayroll(Request $request)
    {
        $period = $request->validate(['period' => ['required', 'date_format:Y-m']])['period'];
        $payroll = PayrollRun::with(['items' => fn ($query) => $query->orderBy('employee_name')])
            ->where('period', $period)
            ->first();
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll');
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'Laporan Payroll '.$period);
        $sheet->fromArray(['Kode Pegawai', 'Nama Pegawai', 'Departemen', 'Gaji Pokok', 'Tunjangan', 'Hari Lembur', 'Upah Lembur', 'Potongan', 'Take Home Pay', 'Status', 'Tanggal Dibayar'], null, 'A3');

        foreach ($payroll?->items ?? [] as $index => $item) {
            $this->writeSpreadsheetRow($sheet, [
                $item->employee_code,
                $item->employee_name,
                $item->department_name ?? '-',
                (float) $item->base_salary,
                (float) $item->allowance,
                (int) $item->overtime_days,
                (float) $item->overtime_pay,
                (float) $item->deduction,
                (float) $item->net_pay,
                $item->payment_status === 'paid' ? 'Dibayar' : 'Belum dibayar',
                $item->paid_at?->format('Y-m-d') ?? '-',
            ], $index + 4, [3, 4, 6, 7, 8]);
        }

        return $this->downloadSpreadsheet($spreadsheet, "payroll-{$period}.xlsx", 'A3:K'.max(3, ($payroll?->items->count() ?? 0) + 3));
    }

    private function writeSpreadsheetRow($sheet, array $values, int $row, array $numericColumns): void
    {
        foreach ($values as $index => $value) {
            $coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).$row;
            if (in_array($index, $numericColumns, true)) {
                $sheet->setCellValue($coordinate, $value);
            } else {
                $sheet->setCellValueExplicit($coordinate, (string) $value, DataType::TYPE_STRING);
            }
        }
    }

    private function downloadSpreadsheet(Spreadsheet $spreadsheet, string $filename, string $filterRange)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle($filterRange)->getFont()->setBold(false);
        $headerRow = preg_replace('/^[A-Z]+/', '', explode(':', $filterRange)[0]);
        $sheet->getStyle('A'.$headerRow.':'.$sheet->getHighestColumn().$headerRow)->getFont()->setBold(true);
        $sheet->freezePane('A'.((int) $headerRow + 1));
        $sheet->setAutoFilter($filterRange);
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function updateSalary(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($employee->isActive(), 404);

        $validated = $request->validate([
            'base_salary' => ['required', 'integer', 'min:0', 'max:9999999999999'],
        ]);

        $employee->update(['base_salary' => $validated['base_salary']]);

        \App\Models\ActivityLog::record(
            'update_salary',
            "Memperbarui gaji pokok {$employee->full_name} ({$employee->employee_code}) menjadi Rp " . number_format((float) $validated['base_salary'], 0, ',', '.'),
            $employee
        );

        return back()->with('success', "Gaji pokok {$employee->full_name} berhasil diperbarui.");
    }

    public function createPayroll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        if (PayrollRun::where('period', $validated['period'])->exists()) {
            throw ValidationException::withMessages([
                'period' => 'Payroll untuk periode ini sudah dibuat.',
            ]);
        }

        $employees = Employee::active()->with('department')->orderBy('employee_code')->get();
        if ($employees->isEmpty()) {
            throw ValidationException::withMessages([
                'period' => 'Belum ada pegawai aktif untuk dibuatkan payroll.',
            ]);
        }

        $employeesWithoutSalary = $employees->filter(fn (Employee $employee) => (float) $employee->base_salary <= 0);
        if ($employeesWithoutSalary->isNotEmpty()) {
            throw ValidationException::withMessages([
                'period' => 'Lengkapi gaji pokok lebih dari Rp0 untuk: '.$employeesWithoutSalary->pluck('full_name')->join(', ').'.',
            ]);
        }

        $periodStart = Carbon::createFromFormat('!Y-m', $validated['period'])->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $overtimeByEmployee = Attendance::query()
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get(['employee_id', 'date'])
            ->filter(fn (Attendance $attendance) => Carbon::parse($attendance->getRawOriginal('date'))->isWeekend())
            ->groupBy('employee_id');

        DB::transaction(function () use ($employees, $validated, $overtimeByEmployee) {
            $payroll = PayrollRun::create([
                'period' => $validated['period'],
                'status' => 'draft',
                'prepared_by' => Auth::id(),
            ]);

            foreach ($employees as $employee) {
                $overtimeDays = $overtimeByEmployee->get($employee->id, collect())->count();
                $overtimePay = $overtimeDays * 200000;

                $payroll->items()->create([
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'employee_name' => $employee->full_name,
                    'department_name' => $employee->department?->name,
                    'base_salary' => $employee->base_salary,
                    'overtime_days' => $overtimeDays,
                    'overtime_pay' => $overtimePay,
                    'net_pay' => (int) $employee->base_salary + $overtimePay,
                ]);
            }
        });

        return redirect()->route('finance.index', ['period' => $validated['period']])
            ->with('success', 'Draft payroll berhasil dibuat. Lembur akhir pekan yang memiliki absen masuk dan pulang sudah ditambahkan otomatis.');
    }

    public function updateItem(Request $request, PayrollRun $payroll, PayrollItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToRun($payroll, $item);
        abort_if($payroll->status !== 'draft', 403, 'Payroll yang sudah diproses tidak dapat diubah.');

        $validated = $request->validate([
            'allowance' => ['required', 'integer', 'min:0', 'max:9999999999999'],
            'deduction' => ['required', 'integer', 'min:0', 'max:9999999999999'],
        ]);

        $grossPay = (int) $item->base_salary + (int) $item->overtime_pay + $validated['allowance'];
        if ($grossPay > 9999999999999) {
            throw ValidationException::withMessages([
                'allowance' => 'Gaji pokok dan tunjangan melebihi batas nilai yang didukung.',
            ]);
        }

        $netPay = $grossPay - $validated['deduction'];
        if ($netPay < 0) {
            throw ValidationException::withMessages([
                'deduction' => 'Total potongan tidak boleh melebihi gaji pokok dan tunjangan.',
            ]);
        }

        $item->update([
            ...$validated,
            'net_pay' => $netPay,
        ]);

        return back()->with('success', "Komponen payroll {$item->employee_name} berhasil diperbarui.");
    }

    public function finalize(PayrollRun $payroll): RedirectResponse
    {
        abort_if($payroll->status !== 'draft', 403, 'Hanya draft payroll yang dapat diproses.');
        abort_if($payroll->items()->doesntExist(), 422, 'Payroll tidak memiliki detail pembayaran.');

        $payroll->update([
            'status' => 'processed',
            'finalized_by' => Auth::id(),
            'finalized_at' => now(),
        ]);

        \App\Models\ActivityLog::record(
            'finalize_payroll',
            "Memfinalisasi payroll periode {$payroll->period}",
            $payroll
        );

        return back()->with('success', 'Payroll telah diproses. Tandai pembayaran setiap pegawai setelah transfer selesai.');
    }

    public function markPaid(PayrollRun $payroll, PayrollItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToRun($payroll, $item);
        abort_unless(in_array($payroll->status, ['processed', 'paid'], true), 403, 'Payroll harus diproses sebelum pembayaran dicatat.');
        abort_if($item->payment_status === 'paid', 409, 'Pembayaran pegawai ini sudah dicatat.');

        DB::transaction(function () use ($payroll, $item) {
            $item->update([
                'payment_status' => 'paid',
                'paid_by' => Auth::id(),
                'paid_at' => now(),
            ]);

            if ($payroll->items()->where('payment_status', '!=', 'paid')->doesntExist()) {
                $payroll->update(['status' => 'paid']);
            }
        });

        \App\Models\ActivityLog::record(
            'paid_payroll',
            "Mencatat pembayaran gaji {$item->employee_name} untuk periode {$payroll->period} (Rp " . number_format((float) $item->net_pay, 0, ',', '.') . ")",
            $item
        );

        return back()->with('success', "Pembayaran {$item->employee_name} berhasil dicatat.");
    }

    private function ensureItemBelongsToRun(PayrollRun $payroll, PayrollItem $item): void
    {
        abort_unless($item->payroll_run_id === $payroll->id, 404);
    }
}
