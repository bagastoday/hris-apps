<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Support\EmployeeEmailGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAndAttendanceSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_employee_name_is_saved_in_uppercase_and_email_is_generated_from_first_two_names(): void
    {
        $department = Department::create([
            'name' => 'Human Resources',
            'code' => 'HRD',
            'is_active' => true,
        ]);
        $position = Position::create([
            'department_id' => $department->id,
            'name' => 'HR Staff',
            'level' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($this->createHrUser())
            ->post(route('employees.store'), [
                'full_name' => 'arasi fauzan susanto',
                'email_option' => 'email',
                'email' => 'manual@example.com',
                'join_date' => today()->toDateString(),
                'department_id' => $department->id,
                'position_id' => $position->id,
                'employment_status' => 'aktif',
            ])
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'full_name' => 'ARASI FAUZAN SUSANTO',
            'email' => 'arasi.fauzan@talenta.id',
        ]);
        $this->assertDatabaseHas('users', ['email' => 'arasi.fauzan@talenta.id']);
    }

    public function test_generated_email_gets_numeric_suffix_when_name_email_is_already_in_use(): void
    {
        User::create([
            'name' => 'Existing Employee',
            'email' => 'arasi.fauzan@talenta.id',
            'password' => 'password',
            'role' => 'karyawan',
        ]);
        $department = Department::create([
            'name' => 'Human Resources',
            'code' => 'HRD',
            'is_active' => true,
        ]);

        $this->actingAs($this->createHrUser())
            ->post(route('employees.store'), [
                'full_name' => 'Arasi Fauzan Susanto',
                'email_option' => 'email',
                'join_date' => today()->toDateString(),
                'department_id' => $department->id,
                'employment_status' => 'aktif',
            ])
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', ['email' => 'arasi.fauzan2@talenta.id']);
        $this->assertDatabaseHas('users', ['email' => 'arasi.fauzan2@talenta.id']);
    }

    public function test_employee_can_be_created_without_email_and_log_in_using_office_code(): void
    {
        $department = Department::create([
            'name' => 'Finance',
            'code' => 'FIN',
            'is_active' => true,
        ]);

        $this->actingAs($this->createHrUser())
            ->post(route('employees.store'), [
                'full_name' => 'Budi Santoso',
                'email_option' => 'no_email',
                'join_date' => today()->toDateString(),
                'department_id' => $department->id,
                'employment_status' => 'aktif',
            ])
            ->assertRedirect(route('employees.index'))
            ->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'tanpa email'));

        $this->assertDatabaseHas('employees', [
            'employee_code' => 'EMP-001',
            'email' => null,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'emp-001@talenta.local',
        ]);

        $this->post(route('login'), [
            'login' => 'EMP-001',
            'password' => Employee::DEFAULT_PASSWORD,
        ])->assertRedirect(route('finance.index'));
    }

    public function test_employee_email_generator_uses_the_first_two_names_and_normalizes_accents(): void
    {
        $this->assertSame(
            'jose.maria@talenta.id',
            EmployeeEmailGenerator::generateUnique('José María de la Cruz')
        );
    }

    public function test_attendance_search_matches_employee_name_and_code(): void
    {
        $department = Department::create([
            'name' => 'Finance',
            'code' => 'FIN',
            'is_active' => true,
        ]);
        $this->createAttendanceEmployee($department, 'Budi Santoso', 'EMP-001', 'NIK-001');
        $siti = $this->createAttendanceEmployee($department, 'Siti Aminah', 'EMP-002', 'NIK-002');
        Attendance::create([
            'employee_id' => $siti->id,
            'date' => today(),
            'check_in' => '08:00:00',
            'status' => 'hadir',
        ]);
        $budi = Employee::where('employee_code', 'EMP-001')->firstOrFail();
        Attendance::create([
            'employee_id' => $budi->id,
            'date' => today(),
            'check_in' => '08:10:00',
            'status' => 'hadir',
        ]);

        $this->actingAs($this->createHrUser())
            ->get(route('attendances.index'))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertSee('Budi Santoso');

        $this->get(route('attendances.index', ['search' => 'EMP-002']))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertDontSee('Budi Santoso');
    }

    public function test_employee_list_can_be_filtered_by_department_and_search_term(): void
    {
        $hrDepartment = Department::create([
            'name' => 'Human Resources',
            'code' => 'HRD',
            'is_active' => true,
        ]);
        $financeDepartment = Department::create([
            'name' => 'Finance',
            'code' => 'FIN',
            'is_active' => true,
        ]);
        $this->createAttendanceEmployee($hrDepartment, 'Siti Aminah', 'EMP-001', 'NIK-001');
        $this->createAttendanceEmployee($financeDepartment, 'Siti Finance', 'EMP-002', 'NIK-002');

        $this->actingAs($this->createHrUser())
            ->get(route('employees.index', [
                'search' => 'Siti',
                'department_id' => $hrDepartment->id,
            ]))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertDontSee('Siti Finance')
            ->assertSee('Semua Departemen');
    }

    public function test_attendance_search_is_displayed_after_summary_and_before_table(): void
    {
        $response = $this->actingAs($this->createHrUser())
            ->get(route('attendances.index'));

        $response->assertOk()
            ->assertSeeInOrder([
                'Alpha',
                'Cari nama, NIK, atau kode pegawai...',
                'Pegawai',
                'Aksi',
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

    private function createAttendanceEmployee(Department $department, string $name, string $code, string $nik): Employee
    {
        $position = Position::create([
            'department_id' => $department->id,
            'name' => 'Staff',
            'level' => 'staff',
            'is_active' => true,
        ]);

        return Employee::create([
            'employee_code' => $code,
            'full_name' => $name,
            'nik' => $nik,
            'join_date' => today()->toDateString(),
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employment_status' => 'aktif',
        ]);
    }
}
