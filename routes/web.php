<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

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

// ========== DASHBOARD HR ==========
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    if (Auth::user()->role !== 'hr') {
        return redirect()->route('karyawan.home');
    }

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
})->name('dashboard');

// ========== PORTAL KARYAWAN ==========
Route::get('/karyawan', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    if (Auth::user()->role !== 'karyawan') {
        return redirect()->route('dashboard');
    }
    return view('karyawan.home');
})->name('karyawan.home');

// ========== PEGAWAI (HR only) ==========
Route::get('/employees', function () {
    if (!Auth::check() || Auth::user()->role !== 'hr') {
        return redirect()->route('login');
    }
    $employees = \App\Models\Employee::with(['department', 'position'])->latest()->get();
    return view('admin.employees.index', compact('employees'));
})->name('employees.index');

Route::get('/employees/create', function () {
    if (!Auth::check() || Auth::user()->role !== 'hr') {
        return redirect()->route('login');
    }
    $departments = \App\Models\Department::where('is_active', true)->get();
    $positions = \App\Models\Position::where('is_active', true)->get();
    return view('admin.employees.create', compact('departments', 'positions'));
})->name('employees.create');

Route::post('/employees', function (Request $request) {
    if (!Auth::check() || Auth::user()->role !== 'hr') {
        return redirect()->route('login');
    }

    $data = $request->validate([
        'full_name' => 'required|string|max:255',
        'nik' => 'required|string|unique:employees,nik',
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

    $last = \App\Models\Employee::orderBy('id', 'desc')->first();
    $num = $last ? ((int) str_replace('EMP-', '', $last->employee_code)) + 1 : 1;
    $data['employee_code'] = 'EMP-' . str_pad($num, 3, '0', STR_PAD_LEFT);

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

    return redirect()->route('employees.index')->with('success', 'Pegawai berhasil ditambahkan.');
})->name('employees.store');