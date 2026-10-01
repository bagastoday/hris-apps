<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrRosterFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_roster_shows_approved_leave_and_complete_weekend_attendance_as_overtime(): void
    {
        $this->travelTo(Carbon::parse('2025-02-03 09:00:00'));
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        $department = Department::create([
            'name' => 'Operasional',
            'code' => 'OPS',
            'is_active' => true,
        ]);
        $employee = Employee::create([
            'employee_code' => 'EMP-001',
            'full_name' => 'Roster Employee',
            'nik' => 'NIK-001',
            'join_date' => '2025-01-01',
            'department_id' => $department->id,
            'employment_status' => 'aktif',
            'base_salary' => 1000,
        ]);
        Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => '2025-02-03',
            'end_date' => '2025-02-03',
            'total_days' => 1,
            'reason' => 'Keperluan keluarga',
            'status' => 'approved',
        ]);
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2025-02-08',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
            'status' => 'hadir',
        ]);

        $this->actingAs($hr)
            ->get(route('roster.index', ['date' => '2025-02-03']))
            ->assertOk()
            ->assertSee('Roster')
            ->assertSee('Roster Employee')
            ->assertSee('Cuti')
            ->assertSee('Libur')
            ->assertSee('Lembur')
            ->assertSee('Rp200.000');
    }

    public function test_hr_can_revise_a_weekday_schedule_and_attendance_uses_the_new_start_time(): void
    {
        $this->travelTo(Carbon::parse('2025-02-03 09:45:00'));
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        $employeeUser = User::create([
            'name' => 'Dinda',
            'email' => 'dinda@example.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'employee_code' => 'EMP-002',
            'full_name' => 'Dinda',
            'nik' => 'NIK-002',
            'join_date' => '2025-01-01',
            'employment_status' => 'aktif',
            'base_salary' => 1000,
        ]);
        \Illuminate\Support\Facades\Storage::fake('public');

        $this->actingAs($hr)
            ->post(route('roster.schedule.update'), [
                'employee_id' => $employee->id,
                'date' => '2025-02-03',
                'start_time' => '10:00',
                'end_time' => '18:30',
                'reason' => 'Koordinasi dengan HR: mulai lebih siang.',
            ])
            ->assertRedirect(route('roster.index', ['date' => '2025-02-03']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roster_schedules', [
            'employee_id' => $employee->id,
            'date' => '2025-02-03 00:00:00',
            'start_time' => '10:00',
            'end_time' => '18:30',
        ]);

        $photo = 'data:image/jpeg;base64,'.base64_encode('attendance-photo');
        $this->actingAs($employeeUser)
            ->post(route('karyawan.attendance.checkin'), ['photo_base64' => $photo])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'date' => '2025-02-03 00:00:00',
            'status' => 'hadir',
        ]);

        $this->get(route('karyawan.home'))
            ->assertOk()
            ->assertSee('Jadwal masuk 10:00 WIB')
            ->assertSee('Jadwal pulang 18:30 WIB');

        $this->actingAs($hr)
            ->get(route('roster.index', ['date' => '2025-02-03']))
            ->assertOk()
            ->assertSee('Koordinasi dengan HR: mulai lebih siang.');
    }

    public function test_hr_schedule_page_loads_selected_employee_and_date(): void
    {
        $this->travelTo(Carbon::parse('2025-02-03 09:00:00'));
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        $employee = Employee::create([
            'employee_code' => 'EMP-005',
            'full_name' => 'Form Employee',
            'nik' => 'NIK-005',
            'join_date' => '2025-01-01',
            'employment_status' => 'aktif',
            'base_salary' => 1000,
        ]);

        $this->actingAs($hr)
            ->get(route('roster.schedule.edit', [
                'employee_id' => $employee->id,
                'date' => '2025-02-03',
            ]))
            ->assertOk()
            ->assertSee('Atur Jadwal')
            ->assertSee('Form Employee')
            ->assertSee('name="reason"', false)
            ->assertSee('value="08:30"', false)
            ->assertSee('value="17:00"', false);
    }

    public function test_roster_rejects_dates_outside_one_month_window_and_weekend_schedule_edits(): void
    {
        $this->travelTo(Carbon::parse('2025-02-03 09:00:00'));
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);
        $employee = Employee::create([
            'employee_code' => 'EMP-003',
            'full_name' => 'Test Employee',
            'nik' => 'NIK-003',
            'join_date' => '2025-01-01',
            'employment_status' => 'aktif',
            'base_salary' => 1000,
        ]);

        $this->actingAs($hr)
            ->get(route('roster.index', ['date' => '2025-03-04']))
            ->assertSessionHasErrors('date');

        $this->post(route('roster.schedule.update'), [
            'employee_id' => $employee->id,
            'date' => '2025-02-08',
            'start_time' => '10:00',
            'end_time' => '18:30',
            'reason' => 'Jadwal khusus akhir pekan',
        ])->assertSessionHasErrors('date');
    }

    public function test_weekend_check_in_is_not_marked_late_and_portal_identifies_day_off(): void
    {
        $this->travelTo(Carbon::parse('2025-02-08 11:00:00'));
        $user = User::create([
            'name' => 'Weekend Employee',
            'email' => 'weekend@example.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-004',
            'full_name' => 'Weekend Employee',
            'nik' => 'NIK-004',
            'join_date' => '2025-01-01',
            'employment_status' => 'aktif',
            'base_salary' => 1000,
        ]);
        \Illuminate\Support\Facades\Storage::fake('public');
        $photo = 'data:image/jpeg;base64,'.base64_encode('attendance-photo');

        $this->actingAs($user)
            ->post(route('karyawan.attendance.checkin'), ['photo_base64' => $photo])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'status' => 'hadir',
        ]);

        $this->get(route('karyawan.home'))
            ->assertOk()
            ->assertSee('Hari libur. Presensi akhir pekan dicatat sebagai lembur.');
    }

    public function test_non_hr_cannot_access_roster(): void
    {
        $employee = User::create([
            'name' => 'Regular Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $this->actingAs($employee)
            ->get(route('roster.index'))
            ->assertRedirect(route('karyawan.home'));
    }
}