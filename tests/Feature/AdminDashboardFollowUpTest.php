<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardFollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_actionable_follow_up_counts_and_excludes_approved_leave_from_missing_attendance(): void
    {
        $department = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
            'is_active' => true,
        ]);
        $position = Position::create([
            'department_id' => $department->id,
            'name' => 'Staff',
            'level' => 'staff',
            'is_active' => true,
        ]);
        $presentEmployee = $this->createEmployee('Present Employee', 'EMP-001', $department, $position);
        $pendingLeaveEmployee = $this->createEmployee('Pending Leave Employee', 'EMP-002', $department, $position);
        $approvedLeaveEmployee = $this->createEmployee('Approved Leave Employee', 'EMP-003', $department, $position);
        $incompleteEmployee = $this->createEmployee('Incomplete Employee', 'EMP-004');
        $lateEmployee = $this->createEmployee('Late Employee', 'EMP-005', $department, $position);

        Attendance::create([
            'employee_id' => $presentEmployee->id,
            'date' => today(),
            'check_in' => '08:00:00',
            'status' => 'hadir',
        ]);
        Attendance::create([
            'employee_id' => $lateEmployee->id,
            'date' => today(),
            'check_in' => '08:45:00',
            'check_out' => '17:00:00',
            'status' => 'terlambat',
        ]);
        Leave::create([
            'employee_id' => $pendingLeaveEmployee->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => today(),
            'end_date' => today(),
            'total_days' => 1,
            'reason' => 'Menunggu persetujuan',
            'status' => 'pending',
        ]);
        Leave::create([
            'employee_id' => $approvedLeaveEmployee->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => today(),
            'end_date' => today(),
            'total_days' => 1,
            'reason' => 'Cuti disetujui',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->createHrUser())->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('hadirHariIni', 2)
            ->assertViewHas('hadirTepatWaktuHariIni', 1)
            ->assertViewHas('terlambatHariIni', 1)
            ->assertViewHas('cutiPending', 1)
            ->assertViewHas('notCheckedInCount', 2)
            ->assertViewHas('incompleteEmployeeCount', 1)
            ->assertSee('Perlu ditindaklanjuti')
            ->assertSee('Pending Leave Employee')
            ->assertSee('Incomplete Employee')
            ->assertDontSee('Approved Leave Employee');
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

    private function createEmployee(
        string $name,
        string $code,
        ?Department $department = null,
        ?Position $position = null
    ): Employee {
        return Employee::create([
            'employee_code' => $code,
            'full_name' => $name,
            'nik' => $code,
            'join_date' => today()->toDateString(),
            'department_id' => $department?->id,
            'position_id' => $position?->id,
            'employment_status' => 'aktif',
        ]);
    }
}
