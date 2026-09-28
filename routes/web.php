<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PositionController;

// ========== AUTH ==========
Route::get('/login', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'hr'
            ? redirect()->route('dashboard')
            : redirect()->route('karyawan.home');
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
    $key = 'login:' . strtolower($login) . '|' . $request->ip();

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
        $user = \App\Models\User::where('nik', $nik)->first();
        if (!$user) {
            $employee = \App\Models\Employee::where('nik', $nik)->first();
            $user = $employee?->user_id ? \App\Models\User::find($employee->user_id) : null;
        }
        $email = $user?->email;
    }

    if ($email && Auth::attempt(['email' => $email, 'password' => $request->password], $request->boolean('remember'))) {
        $employee = Auth::user()->employee;
        if ($employee && !$employee->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun kamu tidak aktif. Hubungi HR.',
            ])->onlyInput('login');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return Auth::user()->role === 'hr'
            ? redirect()->intended(route('dashboard'))
            : redirect()->intended(route('karyawan.home'));
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
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    return view('auth.profile', ['user' => Auth::user()]);
})->name('profile.edit');

Route::match(['post', 'put'], '/profile', function (Request $request) {
    if (!Auth::check()) {
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
    $request->validate([
        'email' => ['required', 'email', 'max:255'],
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

    $key = 'change-pw:' . strtolower($request->email) . '|' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->current_password, $user->password)) {
        RateLimiter::hit($key, 60);
        return back()->withErrors([
            'email' => 'Email atau password lama salah.',
        ])->withInput($request->only('email'));
    }

    RateLimiter::clear($key);

    $user->update([
        'password' => Hash::make($request->password),
    ]);

    return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
})->name('password.change.submit');

// ========== DASHBOARD HR ==========
Route::get('/', function () {
    $totalEmployees = \App\Models\Employee::active()->count();
    $totalDepartments = \App\Models\Department::where('is_active', true)->count();
    $hadirHariIni = \App\Models\Attendance::whereDate('date', today())->where('status', 'hadir')->count();
    $terlambatHariIni = \App\Models\Attendance::whereDate('date', today())->where('status', 'terlambat')->count();
    $alphaHariIni = \App\Models\Attendance::whereDate('date', today())->where('status', 'alpha')->count();
    $cutiHariIni = \App\Models\Attendance::whereDate('date', today())->where('status', 'cuti')->count();
    $cutiPending = \App\Models\Leave::where('status', 'pending')->count();
    $pendingLeaves = \App\Models\Leave::with('employee')
        ->where('status', 'pending')
        ->latest()
        ->take(5)
        ->get();

    return view('admin.dashboard', compact(
        'totalEmployees',
        'totalDepartments',
        'hadirHariIni',
        'terlambatHariIni',
        'alphaHariIni',
        'cutiHariIni',
        'cutiPending',
        'pendingLeaves'
    ));
})->name('dashboard')->middleware('role:hr');

// ========== PORTAL KARYAWAN ==========
Route::get('/karyawan', function () {
    return view('karyawan.home');
})->name('karyawan.home')->middleware('role:karyawan');

// ========== PEGAWAI (HR only) ==========
Route::get('/employees', function () {
    $employees = \App\Models\Employee::with(['department', 'position'])->latest()->get();
    return view('admin.employees.index', compact('employees'));
})->name('employees.index')->middleware('role:hr');

Route::get('/employees/create', function () {
    $departments = \App\Models\Department::where('is_active', true)->get();
    $positions = \App\Models\Position::where('is_active', true)->get();
    return view('admin.employees.create', compact('departments', 'positions'));
})->name('employees.create')->middleware('role:hr');

Route::post('/employees', function (Request $request) {
    $data = $request->validate([
        'full_name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:employees,email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'gender' => 'nullable|in:laki-laki,perempuan',
        'birth_date' => 'nullable|date',
        'join_date' => 'required|date',
        'department_id' => 'nullable|exists:departments,id',
        'position_id' => 'nullable|exists:positions,id',
        'employment_status' => 'required|in:aktif,kontrak,magang,resign,cuti',
    ]);

    if (!empty($data['position_id'])) {
        $position = \App\Models\Position::find($data['position_id']);
        if ($position->department_id != ($data['department_id'] ?? null)) {
            return back()->withErrors([
                'position_id' => 'Jabatan tidak sesuai dengan departemen yang dipilih.',
            ])->withInput();
        }
    }

    $last = \App\Models\Employee::orderByRaw("CAST(REPLACE(employee_code, 'EMP-', '') AS UNSIGNED) DESC")->first();
    $num = $last ? ((int) str_replace('EMP-', '', $last->employee_code)) + 1 : 1;
    $code = 'EMP-' . str_pad($num, 3, '0', STR_PAD_LEFT);

    $data['employee_code'] = $code;
    $data['nik'] = $code;

    DB::transaction(function () use ($data) {
        $user = \App\Models\User::create([
            'name' => $data['full_name'],
            'email' => $data['email'] ?? strtolower($data['employee_code']) . '@talenta.local',
            'password' => Hash::make(\App\Models\Employee::DEFAULT_PASSWORD),
            'role' => 'karyawan',
        ]);

        $data['user_id'] = $user->id;
        \App\Models\Employee::create($data);
    });

    return redirect()->route('employees.index')->with(
        'success',
        "Pegawai berhasil ditambahkan dengan kode {$code}. Login pakai NIK {$code} atau email, password default: " . \App\Models\Employee::DEFAULT_PASSWORD
    );
})->name('employees.store')->middleware('role:hr');

Route::get('/employees/{employee}/edit', function (\App\Models\Employee $employee) {
    $departments = \App\Models\Department::where('is_active', true)->get();
    $positions = \App\Models\Position::where('is_active', true)->get();
    return view('admin.employees.edit', compact('employee', 'departments', 'positions'));
})->name('employees.edit')->middleware('role:hr');

Route::put('/employees/{employee}', function (Request $request, \App\Models\Employee $employee) {
    $data = $request->validate([
        'full_name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:employees,email,' . $employee->id . '|unique:users,email,' . ($employee->user_id ?? 0),
        'phone' => 'nullable|string|max:20',
        'gender' => 'nullable|in:laki-laki,perempuan',
        'birth_date' => 'nullable|date',
        'join_date' => 'required|date',
        'department_id' => 'nullable|exists:departments,id',
        'position_id' => 'nullable|exists:positions,id',
        'employment_status' => 'required|in:aktif,kontrak,magang,resign,cuti',
    ]);

    if (!empty($data['position_id'])) {
        $position = \App\Models\Position::find($data['position_id']);
        if ($position && $position->department_id != ($data['department_id'] ?? null)) {
            return back()->withErrors(['position_id' => 'Jabatan tidak sesuai dengan departemen yang dipilih.'])->withInput();
        }
    }

    DB::transaction(function () use ($employee, $data) {
        $employee->update($data);
        if ($employee->user) {
            $userUpdate = ['name' => $data['full_name']];
            if (!empty($data['email'])) {
                $userUpdate['email'] = $data['email'];
            }
            $employee->user->update($userUpdate);
        }
    });

    return redirect()->route('employees.index')->with('success', 'Data pegawai berhasil diperbarui.');
})->name('employees.update')->middleware('role:hr');

Route::delete('/employees/{employee}', function (\App\Models\Employee $employee) {
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

Route::post('/employees/{employee}/reset-password', function (\App\Models\Employee $employee) {
    $default = \App\Models\Employee::DEFAULT_PASSWORD;

    if ($employee->user) {
        $employee->user->update(['password' => Hash::make($default)]);
        $message = "Password {$employee->full_name} direset ke: {$default}";
    } else {
        $user = \App\Models\User::create([
            'name' => $employee->full_name,
            'email' => $employee->email ?? strtolower($employee->employee_code) . '@talenta.local',
            'password' => Hash::make($default),
            'role' => 'karyawan',
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
Route::get('/reports', function (Request $request) {
    $period = $request->input('period', now()->format('Y-m'));

    $employees = \App\Models\Employee::active()
        ->with('department')
        ->orderBy('employee_code')
        ->get();

    $rekap = $employees->map(function ($e) {
        $hadir = 18 + ($e->id % 4);
        $terlambat = $e->id % 4;
        $izin = $e->id % 3;
        $alpha = $e->id % 5 === 0 ? 1 : 0;
        $total = $hadir + $terlambat + $izin + $alpha;

        return [
            'name' => $e->full_name,
            'code' => $e->employee_code,
            'dept' => $e->department->name ?? 'Tanpa Departemen',
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'alpha' => $alpha,
            'persen' => round(($hadir + $terlambat) / $total * 100),
        ];
    });

    $perDepartemen = $rekap->groupBy('dept')
        ->map(fn ($rows, $name) => ['name' => $name, 'percent' => round($rows->avg('persen'))])
        ->sortByDesc('percent')
        ->values();

    $stats = [
        'total_pegawai' => $employees->count(),
        'rata_kehadiran' => $rekap->count() ? round($rekap->avg('persen')) : 0,
        'total_terlambat' => $rekap->sum('terlambat'),
        'cuti_disetujui' => \App\Models\Leave::where('status', 'approved')->count(),
    ];

    return view('admin.reports.index', compact('period', 'stats', 'perDepartemen', 'rekap'));
})->name('reports.index')->middleware('role:hr');

// ========== ABSENSI ==========
Route::get('/attendances', function (Request $request) {
    $date = $request->input('date', today()->toDateString());
    $departmentId = $request->input('department_id');

    $attendances = \App\Models\Attendance::with('employee.department')
        ->whereDate('date', $date)
        ->when($departmentId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('department_id', $departmentId)))
        ->latest('check_in')
        ->get();

    $summary = [
        'hadir'     => $attendances->where('status', 'hadir')->count(),
        'terlambat' => $attendances->where('status', 'terlambat')->count(),
        'izin'      => $attendances->whereIn('status', ['izin', 'cuti', 'sakit'])->count(),
        'alpha'     => $attendances->where('status', 'alpha')->count(),
    ];

    $departments = \App\Models\Department::where('is_active', true)->get();

    return view('admin.attendances.index', compact('attendances', 'summary', 'departments', 'date', 'departmentId'));
})->name('attendances.index')->middleware('role:hr');