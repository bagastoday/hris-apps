<?php

// app/Http/Controllers/AttendanceController.php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\RosterSchedule;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Tampilkan data rekap absensi harian seluruh pegawai untuk HR.
     */
    public function index(Request $request)
    {
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

        $schedules = RosterSchedule::whereIn('employee_id', $attendances->pluck('employee_id'))
            ->where('date', $date)
            ->get()
            ->keyBy('employee_id');

        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->whereIn('status', ['izin', 'cuti', 'sakit'])->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
        ];

        $departments = Department::where('is_active', true)->get();

        return view('admin.attendances.index', compact('attendances', 'schedules', 'summary', 'departments', 'date', 'departmentId', 'search'));
    }
}
