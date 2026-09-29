<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\KaryawanAttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ReportController;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Position;
use App\Models\User;
use App\Support\EmployeeEmailGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

// ========== AUTH ==========
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->homeRouteName());
    }

    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->merge(['login' => $request->input('login', $request->input('email'))]);

    $request->validate([
        'login' => ['required', 'string', 'max:255'],
        'password' => ['required', 'string'],
    ]);

    $login = trim($request->login);
    $key = 'login:'.strtolower($login).'|'.$request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $email = $login;
    } else {
        $nik = strtoupper($login);
        $user = User::where('nik', $nik)->first();
        if (! $user) {
            $employee = Employee::where('nik', $nik)->first();
            $user = $employee?->user_id ? User::find($employee->user_id) : null;
        }
        $email = $user?->email;
    }

    if ($email && Auth::attempt(['email' => $email, 'password' => $request->password], $request->boolean('remember'))) {
        $employee = Auth::user()->employee;
        if ($employee && ! $employee->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun kamu tidak aktif. Hubungi HR.',
            ])->onlyInput('login');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->homeRouteName()));
    }

    RateLimiter::hit($key, 60);

    return back()->withErrors([
        'email' => 'Email/NIK atau password salah.',
    ])->onlyInput('login');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

// ========== PROFIL ==========
Route::get('/profile', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return view('auth.profile', ['user' => Auth::user()]);
})->name('profile.edit');

Route::match(['post', 'put'], '/profile', function (Request $request) {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    if ($request->hasFile('avatar')) {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }

    $user->update($data);

    return back()->with('success', 'Profil berhasil diperbarui.');
})->name('profile.update');

// ========== UBAH PASSWORD ==========
Route::get('/change-password', function () {
    return view('auth.change-password');
})->name('password.change');

Route::post('/change-password', function (Request $request) {
    $validated = $request->validate([
        'login' => ['required', 'string', 'max:255'],
        'current_password' => ['required', 'string'],
        'password' => [
            'required',
            'confirmed',
            Password::min(8)->letters()->mixedCase()->numbers(),
        ],
    ], [
        'password.min' => 'Password minimal 8 karakter.',
        'password.letters' => 'Password harus mengandung huruf.',
        'password.mixed' => 'Password harus mengandung huruf besar dan kecil.',
        'password.numbers' => 'Password harus mengandung angka.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
    ]);

    $login = trim($validated['login']);
    $key = 'change-pw:'.strtolower($login).'|'.$request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'login' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $user = User::where('email', $login)->first();
    } else {
        $nik = strtoupper($login);
        $user = User::where('nik', $nik)->first();
        if (! $user) {
            $employee = Employee::where('nik', $nik)->first();
            $user = $employee?->user_id ? User::find($employee->user_id) : null;
        }
    }

    if (! $user || ! Hash::check($request->current_password, $user->password)) {
        RateLimiter::hit($key, 60);

        return back()->withErrors([
            'login' => 'Email/NIK atau password lama salah.',
        ])->withInput($request->only('login'));
    }

    RateLimiter::clear($key);

    $user->update([
        'password' => Hash::make($request->password),
    ]);

    return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
})->name('password.change.submit');

// ========== FINANCE & PAYROLL ==========
Route::middleware('role:finance')->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceController::class, 'index'])->name('index');
    Route::get('/salaries', [FinanceController::class, 'salaries'])->name('salaries');
    Route::put('/employees/{employee}/salary', [FinanceController::class, 'updateSalary'])->name('salary.update');
    Route::post('/payroll', [FinanceController::class, 'createPayroll'])->name('payroll.store');
    Route::put('/payroll/{payroll}/items/{item}', [FinanceController::class, 'updateItem'])->name('payroll.items.update');
    Route::post('/payroll/{payroll}/finalize', [FinanceController::class, 'finalize'])->name('payroll.finalize');
    Route::post('/payroll/{payroll}/items/{item}/paid', [FinanceController::class, 'markPaid'])->name('payroll.items.paid');
});

// ========== DASHBOARD HR ==========
Route::get('/', function () {
    $today = today()->toDateString();
    $totalEmployees = Employee::active()->count();
    $totalDepartments = Department::where('is_active', true)->count();
    $totalAttendanceHariIni = Attendance::whereDate('date', $today)->count();
    $hadirHariIni = Attendance::whereDate('date', $today)->where('status', 'hadir')->count();
    $terlambatHariIni = Attendance::whereDate('date', $today)->where('status', 'terlambat')->count();
    $alphaHariIni = Attendance::whereDate('date', $today)->where('status', 'alpha')->count();
    $cutiHariIni = Attendance::whereDate('date', $today)->where('status', 'cuti')->count();
    $cutiPending = Leave::where('status', 'pending')->count();
    $pendingLeaves = Leave::with('employee')
        ->where('status', 'pending')
        ->latest()
        ->take(5)
        ->get();
    $employeesNotCheckedInQuery = Employee::active()
        ->whereDoesntHave('attendances', fn ($query) => $query->whereDate('date', $today))
        ->whereDoesntHave('leaves', fn ($query) => $query
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today));
    $notCheckedInCount = (clone $employeesNotCheckedInQuery)->count();
    $notCheckedInEmployees = (clone $employeesNotCheckedInQuery)
        ->with('department')
        ->orderBy('full_name')
        ->take(5)
        ->get();
    $employeesWithIncompleteDataQuery = Employee::active()
        ->where(fn ($query) => $query->whereNull('department_id')->orWhereNull('position_id'));
    $incompleteEmployeeCount = (clone $employeesWithIncompleteDataQuery)->count();
    $incompleteEmployees = (clone $employeesWithIncompleteDataQuery)
        ->orderBy('full_name')
        ->take(5)
        ->get();

    return view('admin.dashboard', compact(
        'today',
        'totalEmployees',
        'totalDepartments',
        'totalAttendanceHariIni',
        'hadirHariIni',
        'terlambatHariIni',
        'alphaHariIni',
        'cutiHariIni',
        'cutiPending',
        'pendingLeaves',
        'notCheckedInCount',
        'notCheckedInEmployees',
        'incompleteEmployeeCount',
        'incompleteEmployees'
    ));
})->name('dashboard')->middleware('role:hr');

// ========== PORTAL KARYAWAN & ABSENSI REALTIME ==========
Route::get('/karyawan', [KaryawanAttendanceController::class, 'index'])
    ->name('karyawan.home')
    ->middleware('role:karyawan,hr,finance');

Route::get('/karyawan/absensi', [KaryawanAttendanceController::class, 'index'])
    ->name('karyawan.attendance')
    ->middleware('role:karyawan,hr,finance');

Route::post('/karyawan/absensi/check-in', [KaryawanAttendanceController::class, 'checkIn'])
    ->name('karyawan.attendance.checkin')
    ->middleware('role:karyawan,hr,finance');

Route::post('/karyawan/absensi/check-out', [KaryawanAttendanceController::class, 'checkOut'])
    ->name('karyawan.attendance.checkout')
    ->middleware('role:karyawan,hr,finance');

Route::get('/karyawan/payroll', [KaryawanAttendanceController::class, 'payslips'])
    ->name('karyawan.payroll')
    ->middleware('role:karyawan,hr,finance');

// ========== PEGAWAI (HR only) ==========
Route::get('/employees', function (Request $request) {
    $filters = $request->validate([
        'search' => ['nullable', 'string', 'max:100'],
        'department_id' => ['nullable', 'integer', 'exists:departments,id'],
    ]);
    $search = trim($filters['search'] ?? '');
    $departmentId = $filters['department_id'] ?? '';

    $employees = Employee::with(['department', 'position'])
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
})->name('employees.index')->middleware('role:hr');

Route::get('/employees/create', function () {
    $departments = Department::where('is_active', true)->get();
    $positions = Position::where('is_active', true)->get();

    return view('admin.employees.create', compact('departments', 'positions'));
})->name('employees.create')->middleware('role:hr');

Route::post('/employees', function (Request $request) {
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
        if ($position->department_id != ($data['department_id'] ?? null)) {
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
})->name('employees.store')->middleware('role:hr');

Route::get('/employees/{employee}/edit', function (Employee $employee) {
    $departments = Department::where('is_active', true)->get();
    $positions = Position::where('is_active', true)->get();

    return view('admin.employees.edit', compact('employee', 'departments', 'positions'));
})->name('employees.edit')->middleware('role:hr');

Route::put('/employees/{employee}', function (Request $request, Employee $employee) {
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
})->name('employees.update')->middleware('role:hr');

Route::delete('/employees/{employee}', function (Employee $employee) {
    DB::transaction(function () use ($employee) {
        $user = $employee->user;
        $name = $employee->full_name;
        $employee->delete();
        if ($user && $user->role !== 'hr') {
            $user->delete();
        }
    });

    return redirect()->route('employees.index')->with('success', "Data pegawai {$employee->full_name} berhasil dihapus.");
})->name('employees.destroy')->middleware('role:hr');

Route::post('/employees/{employee}/reset-password', function (Employee $employee) {
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
})->name('employees.reset-password')->middleware('role:hr');

// ========== DEPARTEMEN ==========
Route::resource('departments', DepartmentController::class)
    ->except(['show'])
    ->middleware('role:hr');

Route::post('/departments/{department}/positions', [PositionController::class, 'store'])
    ->name('positions.store')
    ->middleware('role:hr');

Route::delete('/positions/{position}', [PositionController::class, 'destroy'])
    ->name('positions.destroy')
    ->middleware('role:hr');

// ========== CUTI ==========
Route::get('/leaves', [LeaveController::class, 'index'])
    ->name('leaves.index')
    ->middleware('role:hr');

Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])
    ->name('leaves.approve')
    ->middleware('role:hr');

Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])
    ->name('leaves.reject')
    ->middleware('role:hr');

// ========== LAPORAN ==========
Route::get('/reports', [ReportController::class, 'index'])
    ->name('reports.index')
    ->middleware('role:hr');

Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])
    ->name('reports.export.excel')
    ->middleware('role:hr');

Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])
    ->name('reports.export.pdf')
    ->middleware('role:hr');

// ========== ABSENSI ==========
Route::get('/attendances', function (Request $request) {
    $date = $request->input('date', today()->toDateString());
    $departmentId = $request->input('department_id');
    $request->validate([
        'search' => ['nullable', 'string', 'max:100'],
    ]);
    $search = trim((string) $request->input('search', ''));

    $attendances = Attendance::with('employee.department')
        ->whereDate('date', $date)
        ->when($departmentId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('department_id', $departmentId)))
        ->when($search !== '', fn ($q) => $q->whereHas('employee', fn ($e) => $e->where(fn ($terms) => $terms
            ->where('full_name', 'like', "%{$search}%")
            ->orWhere('employee_code', 'like', "%{$search}%")
            ->orWhere('nik', 'like', "%{$search}%"))))
        ->latest('check_in')
        ->get();

    $summary = [
        'hadir' => $attendances->where('status', 'hadir')->count(),
        'terlambat' => $attendances->where('status', 'terlambat')->count(),
        'izin' => $attendances->whereIn('status', ['izin', 'cuti', 'sakit'])->count(),
        'alpha' => $attendances->where('status', 'alpha')->count(),
    ];

    $departments = Department::where('is_active', true)->get();

    return view('admin.attendances.index', compact('attendances', 'summary', 'departments', 'date', 'departmentId', 'search'));
})->name('attendances.index')->middleware('role:hr');
