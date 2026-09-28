<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class KaryawanAttendanceController extends Controller
{
    // Menampilkan halaman absensi & portal pegawai
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        // Jika HR mengakses portal dan belum punya data employee, gunakan data employee pertama untuk keperluan preview/uji coba
        if (!$employee && $user->role === 'hr') {
            $employee = Employee::first();
        }

        $today = today();
        $todayAttendance = null;
        $recentAttendances = collect();

        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();

            $recentAttendances = Attendance::where('employee_id', $employee->id)
                ->latest('date')
                ->take(10)
                ->get();
        }

        return view('karyawan.home', compact('employee', 'todayAttendance', 'recentAttendances', 'today'));
    }

    // Proses Absen Masuk dengan Foto & Jam Realtime
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee && $user->role === 'hr') {
            $employee = Employee::first();
        }

        if (!$employee) {
            return back()->with('error', 'Data pegawai kamu tidak ditemukan. Hubungi HR.');
        }

        $today = today();
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->check_in) {
            return back()->with('error', 'Kamu sudah melakukan absen masuk hari ini.');
        }

        $photoInput = $request->file('photo') ?? $request->input('photo_base64');
        if (!$photoInput) {
            return back()->with('error', 'Wajib menyertakan foto selfie / kamera untuk melakukan absensi.');
        }

        $photoPath = $this->storePhoto($photoInput, 'in_' . $employee->id);

        $now = now();
        $currentTime = $now->format('H:i:s');

        // Batas keterlambatan adalah jam 08:30 WIB
        $isLate = $now->format('H:i') > '08:30';
        $status = $isLate ? 'terlambat' : 'hadir';

        Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'check_in' => $currentTime,
                'check_in_photo' => $photoPath,
                'status' => $status,
                'notes' => $request->input('notes'),
            ]
        );

        $statusText = $isLate ? 'Terlambat' : 'Tepat Waktu';
        return back()->with('success', "Absen masuk berhasil pada {$currentTime} WIB (Status: {$statusText}). Selamat bekerja!");
    }

    // Proses Absen Pulang dengan Foto & Jam Realtime
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee && $user->role === 'hr') {
            $employee = Employee::first();
        }

        if (!$employee) {
            return back()->with('error', 'Data pegawai kamu tidak ditemukan. Hubungi HR.');
        }

        $today = today();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', 'Kamu belum melakukan absen masuk hari ini.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'Kamu sudah melakukan absen pulang hari ini.');
        }

        $photoInput = $request->file('photo') ?? $request->input('photo_base64');
        if (!$photoInput) {
            return back()->with('error', 'Wajib menyertakan foto selfie / kamera untuk melakukan absensi pulang.');
        }

        $photoPath = $this->storePhoto($photoInput, 'out_' . $employee->id);
        $currentTime = now()->format('H:i:s');

        $notes = $attendance->notes;
        if ($request->filled('notes')) {
            $notes = $notes ? $notes . ' | Pulang: ' . $request->notes : 'Pulang: ' . $request->notes;
        }

        $attendance->update([
            'check_out' => $currentTime,
            'check_out_photo' => $photoPath,
            'notes' => $notes,
        ]);

        return back()->with('success', "Absen pulang berhasil pada {$currentTime} WIB. Terima kasih atas dedikasi dan kerja kerasmu hari ini!");
    }

    // Helper untuk menyimpan foto baik dari webcam (base64) maupun file upload
    protected function storePhoto($photoInput, string $prefix): ?string
    {
        if (!$photoInput) {
            return null;
        }

        if ($photoInput instanceof \Illuminate\Http\UploadedFile) {
            return $photoInput->store('attendances', 'public');
        }

        if (is_string($photoInput) && str_starts_with($photoInput, 'data:image')) {
            @list(, $data) = explode(';', $photoInput);
            @list(, $data) = explode(',', $data);
            $decoded = base64_decode($data);

            if ($decoded !== false) {
                $filename = 'attendances/' . $prefix . '_' . time() . '_' . uniqid() . '.jpg';
                Storage::disk('public')->put($filename, $decoded);
                return $filename;
            }
        }

        return null;
    }
}
