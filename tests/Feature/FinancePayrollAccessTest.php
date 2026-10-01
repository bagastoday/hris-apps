<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FinanceTransaction;
use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class FinancePayrollAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_login_opens_payroll_and_hr_and_employee_data_remain_restricted(): void
    {
        [$finance, $employee] = $this->createFinanceEmployee();

        $this->post('/login', [
            'login' => $finance->email,
            'password' => 'password',
        ])->assertRedirect(route('finance.index'));

        $this->get(route('finance.index'))->assertOk()->assertSee('Payroll Karyawan');
        $this->get(route('finance.salaries'))->assertOk()->assertSee($employee->full_name);
        $this->get(route('dashboard'))->assertRedirect(route('finance.index'));
        $this->get(route('employees.index'))->assertRedirect(route('finance.index'));
    }

    public function test_hr_creating_an_employee_in_finance_department_grants_finance_access_automatically(): void
    {
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        $department = Department::create([
            'name' => 'Finance / Accounting',
            'code' => 'FIN',
            'is_active' => true,
        ]);

        $this->actingAs($hr)
            ->post(route('employees.store'), [
                'full_name' => 'New Finance Staff',
                'email_option' => 'no_email',
                'department_id' => $department->id,
                'join_date' => today()->toDateString(),
                'employment_status' => 'aktif',
            ])
            ->assertRedirect(route('employees.index'));

        $user = User::where('role', 'finance')->firstOrFail();
        $this->assertDatabaseHas('employees', [
            'user_id' => $user->id,
            'full_name' => 'NEW FINANCE STAFF',
            'department_id' => $department->id,
        ]);
    }

    public function test_non_finance_department_does_not_grant_finance_access_even_if_request_is_tampered(): void
    {
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        $department = Department::create([
            'name' => 'Marketing',
            'code' => 'MKT',
            'is_active' => true,
        ]);

        $this->actingAs($hr)
            ->post(route('employees.store'), [
                'full_name' => 'Marketing Staff',
                'email_option' => 'no_email',
                'account_role' => 'finance',
                'department_id' => $department->id,
                'join_date' => today()->toDateString(),
                'employment_status' => 'aktif',
            ])
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'emp-001@talenta.local',
            'role' => 'karyawan',
        ]);
    }

    public function test_changing_employee_department_updates_access_role_automatically(): void
    {
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        [, $employee] = $this->createFinanceEmployee();
        $marketing = Department::create([
            'name' => 'Marketing',
            'code' => 'MKT',
            'is_active' => true,
        ]);

        $this->actingAs($hr)
            ->put(route('employees.update', $employee), [
                'full_name' => $employee->full_name,
                'join_date' => today()->toDateString(),
                'employment_status' => 'aktif',
                'department_id' => $marketing->id,
            ])
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('users', [
            'id' => $employee->user_id,
            'role' => 'karyawan',
        ]);
    }

    public function test_resetting_login_for_finance_department_employee_creates_finance_role(): void
    {
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        $department = Department::create([
            'name' => 'Akuntansi',
            'code' => 'ACC',
            'is_active' => true,
        ]);
        $employee = Employee::create([
            'employee_code' => 'EMP-011',
            'full_name' => 'Accounting Staff',
            'nik' => 'NIK-011',
            'join_date' => today()->toDateString(),
            'department_id' => $department->id,
            'employment_status' => 'aktif',
        ]);

        $this->actingAs($hr)
            ->post(route('employees.reset-password', $employee))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $employee->fresh()->user_id,
            'role' => 'finance',
        ]);
    }

    public function test_finance_can_use_own_attendance_portal_and_check_in_and_out(): void
    {
        [$finance] = $this->createFinanceEmployee();
        Storage::fake('public');
        $this->actingAs($finance);

        $this->get(route('karyawan.home'))->assertOk()->assertSee('Portal Pegawai');
        $photo = 'data:image/jpeg;base64,'.base64_encode('attendance-photo');

        $this->post(route('karyawan.attendance.checkin'), ['photo_base64' => $photo])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->post(route('karyawan.attendance.checkout'), ['photo_base64' => $photo])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(
            Attendance::where('employee_id', $finance->employee->id)
                ->whereDate('date', today())
                ->whereNotNull('check_in')
                ->whereNotNull('check_out')
                ->exists()
        );
    }

    public function test_finance_can_process_payroll_and_employee_can_only_see_own_processed_slip(): void
    {
        [$finance, $employee] = $this->createFinanceEmployee();
        $otherUser = User::create([
            'name' => 'Other Employee',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);
        $otherEmployee = Employee::create([
            'user_id' => $otherUser->id,
            'employee_code' => 'EMP-002',
            'full_name' => 'Other Employee',
            'nik' => 'NIK-002',
            'join_date' => today()->toDateString(),
            'employment_status' => 'aktif',
            'base_salary' => 2000,
        ]);

        $this->actingAs($finance);
        $this->put(route('finance.salary.update', $employee), ['base_salary' => 1000])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->post(route('finance.payroll.store'), ['period' => '2025-02'])
            ->assertRedirect(route('finance.index', ['period' => '2025-02']));

        $run = PayrollRun::where('period', '2025-02')->firstOrFail();
        $item = $run->items()->where('employee_id', $employee->id)->firstOrFail();
        $this->put(route('finance.payroll.items.update', [$run, $item]), [
            'allowance' => 100,
            'deduction' => 50,
        ])->assertRedirect();
        $this->assertDatabaseHas('payroll_items', [
            'id' => $item->id,
            'net_pay' => 1050,
        ]);

        $this->post(route('finance.payroll.finalize', $run))->assertRedirect();
        $this->post(route('finance.payroll.items.paid', [$run, $item]))->assertRedirect();
        $this->assertDatabaseHas('payroll_runs', ['id' => $run->id, 'status' => 'processed']);
        $otherItem = $run->items()->where('employee_id', $otherEmployee->id)->firstOrFail();
        $this->post(route('finance.payroll.items.paid', [$run, $otherItem]))->assertRedirect();
        $this->assertDatabaseHas('payroll_runs', ['id' => $run->id, 'status' => 'paid']);

        $this->actingAs($finance)
            ->get(route('karyawan.payroll'))
            ->assertOk()
            ->assertSee('Rp 1.050')
            ->assertDontSee($otherEmployee->full_name);
        $this->actingAs($otherUser)
            ->get(route('karyawan.payroll'))
            ->assertOk()
            ->assertDontSee('Finance Employee');
    }

    public function test_finance_payroll_rejects_employees_without_a_base_salary(): void
    {
        [$finance] = $this->createFinanceEmployee();
        Employee::create([
            'employee_code' => 'EMP-002',
            'full_name' => 'Salary Missing',
            'nik' => 'NIK-002',
            'join_date' => today()->toDateString(),
            'employment_status' => 'aktif',
        ]);

        $this->actingAs($finance)
            ->from(route('finance.index', ['period' => '2025-02']))
            ->post(route('finance.payroll.store'), ['period' => '2025-02'])
            ->assertRedirect(route('finance.index', ['period' => '2025-02']))
            ->assertSessionHasErrors('period');

        $this->assertDatabaseMissing('payroll_runs', ['period' => '2025-02']);
    }

    public function test_weekend_attendance_is_added_as_separate_payroll_overtime(): void
    {
        [$finance, $employee] = $this->createFinanceEmployee();
        foreach ([
            ['2025-02-08', '09:00:00', '17:00:00'],
            ['2025-02-09', '09:00:00', '12:00:00'],
            ['2025-02-10', '08:00:00', '17:00:00'],
            ['2025-02-15', '09:00:00', null],
        ] as [$date, $checkIn, $checkOut]) {
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => $date,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => 'hadir',
            ]);
        }

        $this->actingAs($finance)
            ->post(route('finance.payroll.store'), ['period' => '2025-02'])
            ->assertRedirect(route('finance.index', ['period' => '2025-02']));

        $run = PayrollRun::where('period', '2025-02')->firstOrFail();
        $item = $run->items()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertSame(2, $item->overtime_days);
        $this->assertEquals(400000, $item->overtime_pay);
        $this->assertEquals(401000, $item->net_pay);

        $this->put(route('finance.payroll.items.update', [$run, $item]), [
            'allowance' => 100,
            'deduction' => 50,
        ])->assertRedirect();
        $this->assertDatabaseHas('payroll_items', [
            'id' => $item->id,
            'overtime_pay' => 400000,
            'net_pay' => 401050,
        ]);
    }

    public function test_finance_can_record_reimbursements_and_export_cashflow_and_payroll(): void
    {
        [$finance, $employee] = $this->createFinanceEmployee();
        $this->travelTo(now()->setDate(2025, 2, 28)->setTime(10, 0));
        $this->actingAs($finance);

        $this->get(route('finance.transactions', ['period' => '2025-02']))
            ->assertOk()
            ->assertSee('Kas & Pengeluaran')
            ->assertSee('Ekspor Arus Kas')
            ->assertSee('Ekspor Payroll');

        $this->post(route('finance.transactions.store'), [
            'period' => '2025-02',
            'type' => 'expense',
            'category' => 'reimbursement',
            'description' => 'Penggantian bensin perjalanan dinas',
            'employee_id' => $employee->id,
            'amount' => 150000,
            'transaction_date' => '2025-02-18',
            'status' => 'pending',
        ])->assertRedirect(route('finance.transactions', ['period' => '2025-02']));

        $reimbursement = FinanceTransaction::firstOrFail();
        $this->assertDatabaseHas('finance_transactions', [
            'id' => $reimbursement->id,
            'employee_id' => $employee->id,
            'status' => 'pending',
            'amount' => 150000,
        ]);

        $this->post(route('finance.transactions.paid', $reimbursement), [
            'paid_date' => '2025-02-28',
        ])->assertRedirect(route('finance.transactions', ['period' => '2025-02']));

        $this->post(route('finance.transactions.store'), [
            'period' => '2025-02',
            'type' => 'expense',
            'category' => 'operasional',
            'description' => 'Biaya operasional belum dibayar',
            'amount' => 75000,
            'transaction_date' => '2025-02-19',
            'status' => 'pending',
        ])->assertRedirect(route('finance.transactions', ['period' => '2025-02']));

        $this->post(route('finance.transactions.store'), [
            'period' => '2025-02',
            'type' => 'income',
            'category' => 'pendapatan',
            'description' => 'Pemasukan operasional',
            'amount' => 500000,
            'transaction_date' => '2025-02-28',
            'status' => 'paid',
            'paid_date' => '2025-02-28',
        ])->assertRedirect(route('finance.transactions', ['period' => '2025-02']));

        $this->post(route('finance.payroll.store'), ['period' => '2025-02'])->assertRedirect();
        $payroll = PayrollRun::where('period', '2025-02')->firstOrFail();
        $this->post(route('finance.payroll.finalize', $payroll))->assertRedirect();
        $item = $payroll->items()->where('employee_id', $employee->id)->firstOrFail();
        $this->post(route('finance.payroll.items.paid', [$payroll, $item]))->assertRedirect();

        $cashflow = $this->get(route('finance.exports.cashflow', ['period' => '2025-02']));
        $cashflow->assertOk()->assertDownload('arus-kas-2025-02.xlsx');
        $this->assertStringStartsWith('PK', $cashflow->streamedContent());

        $temporaryFile = tempnam(sys_get_temp_dir(), 'cashflow-test');
        file_put_contents($temporaryFile, $cashflow->streamedContent());
        try {
            $rows = IOFactory::load($temporaryFile)->getActiveSheet()->toArray();
            $descriptions = array_column(array_slice($rows, 3), 5);
            $this->assertContains('Penggantian bensin perjalanan dinas', $descriptions);
            $this->assertContains('Pemasukan operasional', $descriptions);
            $this->assertContains('Pembayaran payroll 2025-02', $descriptions);
            $this->assertNotContains('Biaya operasional belum dibayar', $descriptions);
        } finally {
            unlink($temporaryFile);
        }

        $payrollExport = $this->get(route('finance.exports.payroll', ['period' => '2025-02']));
        $payrollExport->assertOk()->assertDownload('payroll-2025-02.xlsx');
        $this->assertStringStartsWith('PK', $payrollExport->streamedContent());
    }

    public function test_regular_employee_cannot_access_finance_transactions_or_exports(): void
    {
        $user = User::create([
            'name' => 'Regular Employee',
            'email' => 'regular@example.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $this->actingAs($user)
            ->get(route('finance.transactions'))
            ->assertRedirect(route('karyawan.home'));
        $this->actingAs($user)
            ->get(route('finance.exports.cashflow', ['period' => '2025-02']))
            ->assertRedirect(route('karyawan.home'));
        $this->actingAs($user)
            ->get(route('finance.exports.payroll', ['period' => '2025-02']))
            ->assertRedirect(route('karyawan.home'));
    }

    public function test_regular_employee_cannot_open_or_change_finance_payroll(): void
    {
        $user = User::create([
            'name' => 'Regular Employee',
            'email' => 'regular@example.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $this->actingAs($user)
            ->get(route('finance.index'))
            ->assertRedirect(route('karyawan.home'));
        $this->actingAs($user)
            ->get(route('finance.salaries'))
            ->assertRedirect(route('karyawan.home'));
    }

    private function createFinanceEmployee(): array
    {
        $department = Department::create([
            'name' => 'Accounting',
            'code' => 'FIN',
            'is_active' => true,
        ]);
        $user = User::create([
            'name' => 'Finance Employee',
            'email' => 'finance@example.com',
            'password' => Hash::make('password'),
            'role' => 'finance',
        ]);
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-001',
            'full_name' => 'Finance Employee',
            'nik' => 'NIK-001',
            'join_date' => today()->toDateString(),
            'department_id' => $department->id,
            'employment_status' => 'aktif',
            'base_salary' => 1000,
        ]);

        return [$user, $employee];
    }
}
