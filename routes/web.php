<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
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
    $credentials = $request->validate([
        'email' => ['required', 'email', 'max:255'],
        'password' => ['required', 'string'],
    ]);

    $key = 'login:' . strtolower($request->email) . '|' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return Auth::user()->role === 'hr'
            ? redirect()->intended(route('dashboard'))
            : redirect()->intended(route('karyawan.home'));
    }

    RateLimiter::hit($key, 60);

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ])->onlyInput('email');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

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



// ========== CUTI (HR) ==========
Route::get('/leaves', [LeaveController::class, 'index'])
    ->name('leaves.index')
    ->middleware('role:hr');

Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])
    ->name('leaves.approve')
    ->middleware('role:hr');

Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])
    ->name('leaves.reject')
    ->middleware('role:hr');


// ========== DASHBOARD HR ==========
Route::get('/', function () {
    $totalEmployees = \App\Models\Employee::where('employment_status', 'aktif')->count();
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
        'create_account' => 'nullable|boolean',
        'password' => [
            'nullable',
            'required_if:create_account,1',
            Password::min(8)->letters()->mixedCase()->numbers(),
        ],
    ], [
        'password.min' => 'Password minimal 8 karakter.',
        'password.mixed' => 'Password harus huruf besar + kecil.',
        'password.numbers' => 'Password harus mengandung angka.',
    ]);

    // Generate EMP-XXX (tidak reuse nomor yang sudah dihapus)
    $last = \App\Models\Employee::orderByRaw("CAST(REPLACE(employee_code, 'EMP-', '') AS UNSIGNED) DESC")->first();
    $num = $last ? ((int) str_replace('EMP-', '', $last->employee_code)) + 1 : 1;
    $code = 'EMP-' . str_pad($num, 3, '0', STR_PAD_LEFT);

    $data['employee_code'] = $code;
    $data['nik'] = $code; // NIK = kode pegawai otomatis

    if ($request->boolean('create_account') && $request->filled('email') && $request->filled('password')) {
        $user = \App\Models\User::create([
            'name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($request->password),
            'role' => 'karyawan',
        ]);
        $data['user_id'] = $user->id;
    }

    unset($data['create_account'], $data['password']);

    \App\Models\Employee::create($data);

    return redirect()->route('employees.index')->with('success', "Pegawai berhasil ditambahkan dengan kode {$code}.");
})->name('employees.store')->middleware('role:hr');

// ========== DEPARTEMEN (HR only) ==========
Route::resource('departments', DepartmentController::class)
    ->except(['show'])
    ->middleware('role:hr');

// ========== JABATAN (nested di Departemen, HR only) ==========
Route::post('/departments/{department}/positions', [PositionController::class, 'store'])
    ->name('positions.store')
    ->middleware('role:hr');

Route::delete('/positions/{position}', [PositionController::class, 'destroy'])
    ->name('positions.destroy')
    ->middleware('role:hr');