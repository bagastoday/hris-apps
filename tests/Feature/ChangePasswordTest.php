<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_without_email_can_change_password_using_office_code(): void
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'emp-001@talenta.local',
            'password' => Hash::make('Current123'),
            'role' => 'karyawan',
        ]);
        Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-001',
            'full_name' => 'Budi Santoso',
            'nik' => 'EMP-001',
            'join_date' => today()->toDateString(),
            'employment_status' => 'aktif',
        ]);

        $this->post(route('password.change.submit'), [
            'login' => 'EMP-001',
            'current_password' => 'Current123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }
}
