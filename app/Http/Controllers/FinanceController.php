<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FinanceController extends Controller
{
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

        return view('finance.index', compact('period', 'payroll', 'recentPayrolls'));
    }

    public function salaries(): View
    {
        $employees = Employee::active()
            ->with('department')
            ->orderBy('full_name')
            ->get();

        return view('finance.salaries', compact('employees'));
    }

    public function updateSalary(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($employee->isActive(), 404);

        $validated = $request->validate([
            'base_salary' => ['required', 'integer', 'min:0', 'max:9999999999999'],
        ]);

        $employee->update(['base_salary' => $validated['base_salary']]);

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

        DB::transaction(function () use ($employees, $validated) {
            $payroll = PayrollRun::create([
                'period' => $validated['period'],
                'status' => 'draft',
                'prepared_by' => Auth::id(),
            ]);

            foreach ($employees as $employee) {
                $payroll->items()->create([
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'employee_name' => $employee->full_name,
                    'department_name' => $employee->department?->name,
                    'base_salary' => $employee->base_salary,
                    'net_pay' => $employee->base_salary,
                ]);
            }
        });

        return redirect()->route('finance.index', ['period' => $validated['period']])
            ->with('success', 'Draft payroll berhasil dibuat. Periksa tunjangan dan potongan sebelum memprosesnya.');
    }

    public function updateItem(Request $request, PayrollRun $payroll, PayrollItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToRun($payroll, $item);
        abort_if($payroll->status !== 'draft', 403, 'Payroll yang sudah diproses tidak dapat diubah.');

        $validated = $request->validate([
            'allowance' => ['required', 'integer', 'min:0', 'max:9999999999999'],
            'deduction' => ['required', 'integer', 'min:0', 'max:9999999999999'],
        ]);

        $grossPay = (int) $item->base_salary + $validated['allowance'];
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

        return back()->with('success', "Pembayaran {$item->employee_name} berhasil dicatat.");
    }

    private function ensureItemBelongsToRun(PayrollRun $payroll, PayrollItem $item): void
    {
        abort_unless($item->payroll_run_id === $payroll->id, 404);
    }
}
