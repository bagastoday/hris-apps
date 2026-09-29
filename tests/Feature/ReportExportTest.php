<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_uses_attendance_and_approved_leave_for_the_selected_month(): void
    {
        [$department, $employee] = $this->createReportData();

        $response = $this->actingAs($this->createHrUser())
            ->get(route('reports.index', ['period' => '2025-02']));

        $response->assertOk()
            ->assertSee('Test Employee')
            ->assertSee('Finance')
            ->assertSee('17%')
            ->assertSee('33%')
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['total_pegawai'] === 2
                    && $stats['rata_kehadiran'] === 17
                    && $stats['total_terlambat'] === 1
                    && $stats['cuti_disetujui'] === 1;
            })
            ->assertViewHas('rekap', function ($rekap) use ($employee): bool {
                $row = $rekap->firstWhere('code', $employee->employee_code);

                return $row['hadir'] === 1
                    && $row['terlambat'] === 1
                    && $row['izin'] === 1
                    && $row['sakit'] === 1
                    && $row['cuti'] === 1
                    && $row['alpha'] === 1
                    && $row['persen'] === 33;
            });
    }

    public function test_report_exports_download_real_xlsx_and_pdf_files(): void
    {
        $this->createReportData();
        $this->actingAs($this->createHrUser());

        $excel = $this->get(route('reports.export.excel', ['period' => '2025-02']));
        $excel->assertOk()
            ->assertDownload('laporan-kehadiran-2025-02.xlsx');
        $this->assertStringStartsWith('PK', $excel->streamedContent());

        $pdf = $this->get(route('reports.export.pdf', ['period' => '2025-02']));
        $pdf->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="laporan-kehadiran-2025-02.pdf"');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
    }

    public function test_department_filter_scopes_report_statistics_and_exports(): void
    {
        [$department, $employee] = $this->createReportData();
        $otherDepartment = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
            'is_active' => true,
        ]);
        $otherEmployee = $this->createEmployee('Other Department Employee', 'EMP-004', $otherDepartment);
        Attendance::create([
            'employee_id' => $otherEmployee->id,
            'date' => '2025-02-10',
            'status' => 'terlambat',
        ]);
        Leave::create([
            'employee_id' => $otherEmployee->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => '2025-02-10',
            'end_date' => '2025-02-10',
            'total_days' => 1,
            'status' => 'approved',
        ]);
        $this->actingAs($this->createHrUser());

        $response = $this->get(route('reports.index', [
            'period' => '2025-02',
            'department_id' => $department->id,
        ]));

        $response->assertOk()
            ->assertSee('Test Employee')
            ->assertDontSee('Other Department Employee')
            ->assertViewHas('departmentId', $department->id)
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['total_pegawai'] === 1
                    && $stats['total_terlambat'] === 1
                    && $stats['cuti_disetujui'] === 1;
            })
            ->assertViewHas('rekap', fn ($rekap) => $rekap->count() === 1
                && $rekap->first()['code'] === $employee->employee_code);

        $query = ['period' => '2025-02', 'department_id' => $department->id];
        $this->get(route('reports.export.excel', $query))
            ->assertOk()
            ->assertDownload('laporan-kehadiran-2025-02.xlsx');
        $this->get(route('reports.export.pdf', $query))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    private function createReportData(): array
    {
        $department = Department::create([
            'name' => 'Finance',
            'code' => 'FIN',
            'is_active' => true,
        ]);
        $employee = $this->createEmployee('Test Employee', 'EMP-001', $department);
        $this->createEmployee('No Attendance', 'EMP-002');
        $this->createEmployee('Resigned Employee', 'EMP-003', null, 'resign');

        foreach (['hadir', 'terlambat', 'izin', 'sakit', 'cuti', 'alpha'] as $index => $status) {
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => '2025-02-'.str_pad((string) ($index + 10), 2, '0', STR_PAD_LEFT),
                'status' => $status,
            ]);
        }
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2025-01-31',
            'status' => 'hadir',
        ]);
        Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => '2025-01-31',
            'end_date' => '2025-02-02',
            'total_days' => 3,
            'status' => 'approved',
        ]);
        Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-02',
            'total_days' => 2,
            'status' => 'approved',
        ]);

        return [$department, $employee];
    }

    private function createEmployee(
        string $name,
        string $code,
        ?Department $department = null,
        string $status = 'aktif'
    ): Employee {
        return Employee::create([
            'employee_code' => $code,
            'full_name' => $name,
            'nik' => $code,
            'join_date' => today()->toDateString(),
            'department_id' => $department?->id,
            'employment_status' => $status,
        ]);
    }

    private function createHrUser(): User
    {
        return User::create([
            'name' => 'Admin HR',
            'email' => 'hr@example.com',
            'password' => 'password',
            'role' => 'hr',
        ]);
    }
}
