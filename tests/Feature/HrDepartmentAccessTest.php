<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrDepartmentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_department_and_position_user_lands_on_admin_and_can_open_employee_portal(): void
    {
        $user = $this->createEmployeeUser('Human Resources', 'HRD', 'HR Staff');

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Portal Karyawan');
        $this->get(route('karyawan.home'))->assertOk();
    }

    public function test_non_hr_user_lands_on_employee_portal_and_cannot_open_admin(): void
    {
        $user = $this->createEmployeeUser('Finance', 'FIN', 'Accountant');

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('karyawan.home'));

        $this->get(route('karyawan.home'))->assertOk();
        $this->get(route('dashboard'))->assertRedirect(route('karyawan.home'));
    }

    public function test_hr_department_without_hr_position_does_not_get_admin_access(): void
    {
        $user = $this->createEmployeeUser('Human Resources', 'HRD', 'Recruiter');

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('karyawan.home'));

        $this->get(route('dashboard'))->assertRedirect(route('karyawan.home'));
    }

    public function test_existing_hr_role_still_lands_on_admin(): void
    {
        $user = User::create([
            'name' => 'Admin HR',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_hr_without_an_employee_profile_cannot_record_attendance_for_another_employee(): void
    {
        $this->createEmployeeUser('Finance', 'FIN', 'Accountant');
        $user = User::create([
            'name' => 'Admin HR',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        $this->actingAs($user);

        $this->post(route('karyawan.attendance.checkin'))
            ->assertRedirect()
            ->assertSessionHas('error', 'Data pegawai kamu tidak ditemukan. Hubungi HR.');
    }

    private function createEmployeeUser(string $departmentName, string $departmentCode, string $positionName): User
    {
        $department = Department::create([
            'name' => $departmentName,
            'code' => $departmentCode,
            'is_active' => true,
        ]);
        $position = Position::create([
            'department_id' => $department->id,
            'name' => $positionName,
            'level' => 'staff',
            'is_active' => true,
        ]);
        $user = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-001',
            'full_name' => 'Test Employee',
            'nik' => 'NIK-001',
            'email' => $user->email,
            'join_date' => today()->toDateString(),
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employment_status' => 'aktif',
        ]);

        return $user;
    }
}
