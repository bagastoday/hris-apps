<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
public function handle(Request $request, Closure $next, string $role): Response
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    // Pegawai nonaktif langsung dikeluarkan
    $employee = $user->employee;
    if ($employee && !$employee->isActive()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'Akun kamu tidak aktif. Hubungi HR.',
        ]);
    }

    if ($user->role !== $role) {
        return $user->role === 'hr'
            ? redirect()->route('dashboard')
            : redirect()->route('karyawan.home');
    }

    return $next($request);
}

    
}