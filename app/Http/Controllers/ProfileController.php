<?php

// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna yang sedang login.
     */
    public function edit()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.profile', ['user' => Auth::user()]);
    }

    /**
     * Perbarui data profil dan avatar pengguna.
     */
    public function update(Request $request)
    {
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
    }

    /**
     * Tampilkan formulir ubah password mandiri.
     */
    public function changePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Proses ubah password dengan verifikasi password lama.
     */
    public function updatePassword(Request $request)
    {
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
    }
}
