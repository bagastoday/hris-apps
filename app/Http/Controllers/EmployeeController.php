<?php

// app/Http/Controllers/EmployeeController.php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Support\EmployeeEmailGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Tampilkan daftar seluruh pegawai dengan filter & pencarian.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);
        $search = trim($filters['search'] ?? '');
        $departmentId = $filters['department_id'] ?? '';

        $employees = Employee::with(['department', 'position', 'user'])
            ->when($departmentId !== '', fn ($query) => $query->where('department_id', $departmentId))
            ->when($search !== '', fn ($query) => $query->where(fn ($terms) => $terms
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('employee_code', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'departments', 'search', 'departmentId'));
    }

    /**
     * Formulir penambahan pegawai baru.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();

        return view('admin.employees.create', compact('departments', 'positions'));
    }

    /**
     * Simpan data pegawai baru beserta akun user login.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email_option' => ['required', 'in:email,no_email'],
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'birth_date' => 'nullable|date',
            'join_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employment_status' => 'required|in:aktif,kontrak,magang,resign,cuti',
        ]);

        $data['full_name'] = mb_strtoupper(trim($data['full_name']), 'UTF-8');
        $data['email'] = $data['email_option'] === 'email'
            ? EmployeeEmailGenerator::generateUnique($data['full_name'])
            : null;
        unset($data['email_option']);

        $department = isset($data['department_id']) ? Department::find($data['department_id']) : null;
        $accountRole = $department?->isFinanceDepartment() ? 'finance' : 'karyawan';

        if (! empty($data['position_id'])) {
            $position = Position::find($data['position_id']);
            if ($position && $position->department_id != ($data['department_id'] ?? null)) {
                return back()->withErrors([
                    'position_id' => 'Jabatan tidak sesuai dengan departemen yang dipilih.',
                ])->withInput();
            }
        }

        $last = Employee::orderByRaw("CAST(REPLACE(employee_code, 'EMP-', '') AS UNSIGNED) DESC")->first();
        $num = $last ? ((int) str_replace('EMP-', '', $last->employee_code)) + 1 : 1;
        $code = 'EMP-'.str_pad($num, 3, '0', STR_PAD_LEFT);

        $data['employee_code'] = $code;
        $data['nik'] = $code;

        $accountEmail = $data['email'] ?? strtolower($code).'@talenta.local';

        DB::transaction(function () use ($data, $accountEmail, $accountRole) {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $accountEmail,
                'password' => Hash::make(Employee::DEFAULT_PASSWORD),
                'role' => $accountRole,
            ]);

            $data['user_id'] = $user->id;
            Employee::create($data);
        });

        return redirect()->route('employees.index')->with(
            'success',
            $data['email']
                ? "Pegawai berhasil ditambahkan dengan kode {$code} dan email {$data['email']}. Login pakai NIK {$code} atau email tersebut, password default: ".Employee::DEFAULT_PASSWORD
                : "Pegawai berhasil ditambahkan tanpa email dengan kode kantor {$code}. Login pakai NIK/kode kantor tersebut, password default: ".Employee::DEFAULT_PASSWORD
        );
    }

    /**
     * Formulir edit data pegawai.
     */
    public function edit(Employee $employee)
    {
        $departments = Department::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();

        return view('admin.employees.edit', compact('employee', 'departments', 'positions'));
    }

    /**
     * Perbarui data pegawai.
     */
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees,email,'.$employee->id.'|unique:users,email,'.($employee->user_id ?? 0),
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'birth_date' => 'nullable|date',
            'join_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employment_status' => 'required|in:aktif,kontrak,magang,resign,cuti',
        ]);

        if (! empty($data['position_id'])) {
            $position = Position::find($data['position_id']);
            if ($position && $position->department_id != ($data['department_id'] ?? null)) {
                return back()->withErrors(['position_id' => 'Jabatan tidak sesuai dengan departemen yang dipilih.'])->withInput();
            }
        }

        DB::transaction(function () use ($employee, $data) {
            $department = isset($data['department_id']) ? Department::find($data['department_id']) : null;
            $employee->update($data);
            if ($employee->user && $employee->user->role !== 'hr') {
                $userUpdate = ['name' => $data['full_name']];
                if (! empty($data['email'])) {
                    $userUpdate['email'] = $data['email'];
                }
                $userUpdate['role'] = $department?->isFinanceDepartment() ? 'finance' : 'karyawan';
                $employee->user->update($userUpdate);
            }
        });

        return redirect()->route('employees.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Hapus data pegawai beserta akun user terkait.
     */
    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        DB::transaction(function () use ($employee) {
            $user = $employee->user;
            $employee->delete();
            if ($user && $user->role !== 'hr') {
                $user->delete();
            }
        });

        return redirect()->route('employees.index')->with('success', "Data pegawai {$name} berhasil dihapus.");
    }

    /**
     * Reset password akun pegawai ke default password kantor.
     */
    public function resetPassword(Employee $employee)
    {
        $default = Employee::DEFAULT_PASSWORD;

        if ($employee->user) {
            $employee->user->update(['password' => Hash::make($default)]);
            $message = "Password {$employee->full_name} direset ke: {$default}";
        } else {
            $user = User::create([
                'name' => $employee->full_name,
                'email' => $employee->email ?? strtolower($employee->employee_code).'@talenta.local',
                'password' => Hash::make($default),
                'role' => $employee->department?->isFinanceDepartment() ? 'finance' : 'karyawan',
            ]);
            $employee->update(['user_id' => $user->id]);
            $message = "Akun {$employee->full_name} dibuat. Login pakai NIK {$employee->employee_code}, password: {$default}";
        }

        return back()->with('success', $message);
    }
}
