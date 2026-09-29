<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KaryawanPortalFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(): array
    {
        $dept = Department::create(['name' => 'Operasional', 'code' => 'OPS', 'is_active' => true]);
        $pos = Position::create(['department_id' => $dept->id, 'name' => 'Staf Operasional', 'is_active' => true]);

        $user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@talenta.local',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-101',
            'full_name' => 'BUDI SANTOSO',
            'nik' => 'EMP-101',
            'join_date' => today()->toDateString(),
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'employment_status' => 'aktif',
            'base_salary' => 5000000,
        ]);

        return [$user, $employee];
    }

    public function test_karyawan_can_access_portal_home_and_view_stats(): void
    {
        [$user, $employee] = $this->createKaryawan();

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => today(),
            'check_in' => '08:15:00',
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($user)->get(route('karyawan.home'));

        $response->assertOk()
            ->assertSee('BUDI SANTOSO')
            ->assertSee('Presensi Masuk')
            ->assertSee('Presensi Pulang')
            ->assertSee('08:15:00 WIB')
            ->assertSee('Hadir');
    }

    public function test_karyawan_can_access_leaves_page_and_view_quota(): void
    {
        [$user, $employee] = $this->createKaryawan();

        $response = $this->actingAs($user)->get(route('leaves.my'));

        $response->assertOk()
            ->assertSee('Pengajuan Cuti', false)
            ->assertSee('Hak Kuota Tahunan')
            ->assertSee('12')
            ->assertSee('Sisa Cuti Tahunan');
    }

    public function test_karyawan_can_submit_leave_request(): void
    {
        [$user, $employee] = $this->createKaryawan();

        $tomorrow = today()->addDay()->toDateString();
        $dayAfter = today()->addDays(2)->toDateString();

        $response = $this->actingAs($user)->post(route('leaves.store'), [
            'leave_type' => 'cuti_tahunan',
            'start_date' => $tomorrow,
            'end_date' => $dayAfter,
            'reason' => 'Keperluan keluarga penting',
        ]);

        $response->assertRedirect(route('leaves.my'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leaves', [
            'employee_id' => $employee->id,
            'leave_type' => 'cuti_tahunan',
            'total_days' => 2,
            'status' => 'pending',
            'reason' => 'Keperluan keluarga penting',
        ]);
    }

    public function test_karyawan_cannot_submit_overlapping_leave_request(): void
    {
        [$user, $employee] = $this->createKaryawan();

        $start = today()->addDays(3)->toDateString();
        $end = today()->addDays(5)->toDateString();

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => 'izin',
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => 3,
            'status' => 'pending',
            'reason' => 'Izin pertama',
        ]);

        $response = $this->actingAs($user)->post(route('leaves.store'), [
            'leave_type' => 'cuti_tahunan',
            'start_date' => today()->addDays(4)->toDateString(),
            'end_date' => today()->addDays(6)->toDateString(),
            'reason' => 'Izin kedua yang bentrok',
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_karyawan_can_cancel_own_pending_leave(): void
    {
        [$user, $employee] = $this->createKaryawan();

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => today()->addDays(2)->toDateString(),
            'end_date' => today()->addDays(3)->toDateString(),
            'total_days' => 2,
            'status' => 'pending',
            'reason' => 'Mau dibatalkan',
        ]);

        $response = $this->actingAs($user)->delete(route('leaves.cancel', $leave));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('leaves', [
            'id' => $leave->id,
        ]);
    }
}
