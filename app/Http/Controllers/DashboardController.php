<?php

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan ringkasan data dan metrik di dashboard HR.
     */
    public function index()
    {
        $today = today()->toDateString();
        $totalEmployees = Employee::active()->count();
        $totalDepartments = Department::where('is_active', true)->count();
        $totalAttendanceHariIni = Attendance::whereDate('date', $today)->count();
        $hadirHariIni = Attendance::whereDate('date', $today)->whereNotNull('check_in')->count();
        $hadirTepatWaktuHariIni = Attendance::whereDate('date', $today)
            ->where('status', 'hadir')
            ->whereNotNull('check_in')
            ->count();
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
            'hadirTepatWaktuHariIni',
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
    }
}
