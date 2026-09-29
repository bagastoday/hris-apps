<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === USERS ===
        $hr = User::create([
            'name' => 'Admin HR',
            'email' => 'hr@talentacore.id',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);

        $userBudi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@talentacore.id',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $userSiti = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@talentacore.id',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
        ]);

        $userAndi = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@talentacore.id',
            'password' => Hash::make('password'),
            'role' => 'finance',
        ]);

        // === DEPARTMENTS ===
        $it = Department::create(['name' => 'IT & Engineering', 'code' => 'IT', 'is_active' => true]);
        $hrd = Department::create(['name' => 'Human Resources', 'code' => 'HRD', 'is_active' => true]);
        $finance = Department::create(['name' => 'Finance', 'code' => 'FIN', 'is_active' => true]);
        $marketing = Department::create(['name' => 'Marketing', 'code' => 'MKT', 'is_active' => true]);

        // === POSITIONS ===
        $dev = Position::create(['department_id' => $it->id, 'name' => 'Senior Developer', 'level' => 'senior', 'is_active' => true]);
        $staffHr = Position::create(['department_id' => $hrd->id, 'name' => 'HR Staff', 'level' => 'staff', 'is_active' => true]);
        $acc = Position::create(['department_id' => $finance->id, 'name' => 'Accountant', 'level' => 'staff', 'is_active' => true]);
        $mkt = Position::create(['department_id' => $marketing->id, 'name' => 'Marketing Specialist', 'level' => 'staff', 'is_active' => true]);

        // === EMPLOYEES ===
        $empBudi = Employee::create([
            'user_id' => $userBudi->id,
            'employee_code' => 'EMP-001',
            'full_name' => 'Budi Santoso',
            'nik' => '3201010101010001',
            'email' => 'budi@talentacore.id',
            'phone' => '081234567890',
            'gender' => 'laki-laki',
            'birth_date' => '1995-03-15',
            'join_date' => '2023-01-12',
            'department_id' => $it->id,
            'position_id' => $dev->id,
            'employment_status' => 'aktif',
        ]);

        $empSiti = Employee::create([
            'user_id' => $userSiti->id,
            'employee_code' => 'EMP-002',
            'full_name' => 'Siti Aminah',
            'nik' => '3201010101010002',
            'email' => 'siti@talentacore.id',
            'phone' => '081234567891',
            'gender' => 'perempuan',
            'birth_date' => '1998-07-22',
            'join_date' => '2023-06-01',
            'department_id' => $hrd->id,
            'position_id' => $staffHr->id,
            'employment_status' => 'aktif',
        ]);

        $empAndi = Employee::create([
            'user_id' => $userAndi->id,
            'employee_code' => 'EMP-003',
            'full_name' => 'Andi Wijaya',
            'nik' => '3201010101010003',
            'email' => 'andi@talentacore.id',
            'phone' => '081234567892',
            'gender' => 'laki-laki',
            'birth_date' => '1993-11-08',
            'join_date' => '2022-09-15',
            'department_id' => $finance->id,
            'position_id' => $acc->id,
            'employment_status' => 'aktif',
        ]);

        // === ATTENDANCE hari ini ===
        Attendance::create([
            'employee_id' => $empBudi->id,
            'date' => Carbon::today(),
            'check_in' => '08:05:00',
            'check_out' => null,
            'status' => 'hadir',
        ]);

        Attendance::create([
            'employee_id' => $empSiti->id,
            'date' => Carbon::today(),
            'check_in' => '08:32:00',
            'check_out' => null,
            'status' => 'terlambat',
        ]);

        // === LEAVE pending ===
        Leave::create([
            'employee_id' => $empAndi->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => Carbon::today()->addDays(3),
            'end_date' => Carbon::today()->addDays(5),
            'total_days' => 3,
            'reason' => 'Liburan keluarga',
            'status' => 'pending',
        ]);
    }
}
