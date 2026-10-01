<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\PayrollItem;
use App\Models\RosterSchedule;
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

        $today = today();
        $isScheduledWorkday = !$today->isWeekend();
        $todayAttendance = null;
        $todaySchedule = null;
        $recentAttendances = collect();
        $monthlyStats = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin_cuti' => 0,
            'total_kehadiran' => 0,
        ];

        if ($employee) {
            $todaySchedule = RosterSchedule::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();
            $monthAttendances = Attendance::where('employee_id', $employee->id)
                ->whereYear('date', $today->year)
                ->whereMonth('date', $today->month)
                ->get();

            $monthlyStats['hadir'] = $monthAttendances->where('status', 'hadir')->count();
            $monthlyStats['terlambat'] = $monthAttendances->where('status', 'terlambat')->count();
            $monthlyStats['izin_cuti'] = $monthAttendances->whereIn('status', ['izin', 'cuti', 'sakit'])->count();
            $monthlyStats['total_kehadiran'] = $monthlyStats['hadir'] + $monthlyStats['terlambat'];

            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();

            $recentAttendances = Attendance::where('employee_id', $employee->id)
                ->latest('date')
                ->take(15)
                ->get();
        }

        $pinnedAnnouncements = Announcement::active()->pinned()->latest('published_at')->take(3)->get();

        $expectedStartTime = substr($todaySchedule?->start_time ?? RosterSchedule::DEFAULT_START_TIME, 0, 5);
        $expectedEndTime = substr($todaySchedule?->end_time ?? RosterSchedule::DEFAULT_END_TIME, 0, 5);

        return view('karyawan.home', compact('employee', 'todayAttendance', 'todaySchedule', 'expectedStartTime', 'expectedEndTime', 'isScheduledWorkday', 'recentAttendances', 'today', 'monthlyStats', 'pinnedAnnouncements'));
    }

    public function payslips()
    {
        $employee = Auth::user()->employee;
        $payslips = $employee
            ? PayrollItem::with('payrollRun')
                ->where('employee_id', $employee->id)
                ->whereHas('payrollRun', fn ($query) => $query->whereIn('status', ['processed', 'paid']))
                ->latest()
                ->get()
            : collect();

        $stats = [
            'base_salary' => $employee?->base_salary ?? 0,
            'latest_net_pay' => $payslips->first()?->net_pay ?? 0,
            'total_paid_year' => $payslips->where('payment_status', 'paid')
                ->filter(fn ($item) => $item->payrollRun && str_starts_with($item->payrollRun->period, now()->format('Y')))
                ->sum('net_pay'),
            'total_slips' => $payslips->count(),
        ];

        return view('karyawan.payroll', compact('employee', 'payslips', 'stats'));
    }

    // Proses Absen Masuk dengan Foto & Jam Realtime
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

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

        $schedule = RosterSchedule::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();
        $expectedStartTime = substr($schedule?->start_time ?? RosterSchedule::DEFAULT_START_TIME, 0, 5);
        $isLate = !$today->isWeekend() && $now->format('H:i') > $expectedStartTime;
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
        if (! $photoInput) {
            return null;
        }

        $binaryData = null;

        if ($photoInput instanceof \Illuminate\Http\UploadedFile) {
            $binaryData = file_get_contents($photoInput->getRealPath());
        } elseif (is_string($photoInput) && str_starts_with($photoInput, 'data:image')) {
            @list(, $data) = explode(';', $photoInput);
            @list(, $data) = explode(',', $data);
            $binaryData = base64_decode($data);
        }

        if (! $binaryData) {
            return null;
        }

        // Simpan cadangan berkas fisik di storage lokal
        $filename = 'attendances/' . $prefix . '_' . time() . '_' . uniqid() . '.jpg';
        Storage::disk('public')->put($filename, $binaryData);

        // Kompresi ringan via GD agar ukuran Data URI kecil (~20-30KB) dan bisa dibuka di semua laptop/perangkat
        if (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($binaryData);
            if ($img !== false) {
                $origW = imagesx($img);
                $origH = imagesy($img);
                $maxDim = 480;

                if ($origW > $maxDim || $origH > $maxDim) {
                    if ($origW > $origH) {
                        $newW = $maxDim;
                        $newH = (int) round(($origH / $origW) * $maxDim);
                    } else {
                        $newH = $maxDim;
                        $newW = (int) round(($origW / $origH) * $maxDim);
                    }
                    $resized = imagecreatetruecolor($newW, $newH);
                    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                    imagedestroy($img);
                    $img = $resized;
                }

                ob_start();
                imagejpeg($img, null, 75);
                $compressed = ob_get_clean();
                imagedestroy($img);

                if ($compressed) {
                    return 'data:image/jpeg;base64,' . base64_encode($compressed);
                }
            }
        }

        // Jika ukuran binary < 200KB, simpan sebagai Data URI agar bisa dibuka teman di laptop lain
        if (strlen($binaryData) < 200000) {
            return 'data:image/jpeg;base64,' . base64_encode($binaryData);
        }

        return $filename;
    }
}
