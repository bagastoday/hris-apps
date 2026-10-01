<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\RosterSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RosterController extends Controller
{
    public function index(Request $request): View
    {
        $minimumDate = today()->subMonthNoOverflow();
        $maximumDate = today()->addMonthNoOverflow();
        $validated = $request->validate([
            'date' => ['nullable', 'date', 'after_or_equal:'.$minimumDate->toDateString(), 'before_or_equal:'.$maximumDate->toDateString()],
            'week_start' => ['nullable', 'date', 'after_or_equal:'.$minimumDate->toDateString(), 'before_or_equal:'.$maximumDate->toDateString()],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $selectedDate = Carbon::parse($validated['date'] ?? $validated['week_start'] ?? today());
        $weekStart = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);
        $departmentId = $validated['department_id'] ?? null;
        $employees = Employee::active()
            ->with('department')
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->orderBy('full_name')
            ->get();

        $employeeIds = $employees->pluck('id');
        $attendances = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->employee_id.'|'.Carbon::parse($attendance->getRawOriginal('date'))->toDateString());
        $schedules = RosterSchedule::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->keyBy(fn (RosterSchedule $schedule) => $schedule->employee_id.'|'.Carbon::parse($schedule->getRawOriginal('date'))->toDateString());
        $approvedLeaves = Leave::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $weekEnd->toDateString())
            ->whereDate('end_date', '>=', $weekStart->toDateString())
            ->get();

        $leaveByEmployeeAndDate = [];
        foreach ($approvedLeaves as $leave) {
            $leaveStartDate = Carbon::parse($leave->getRawOriginal('start_date'));
            $leaveEndDate = Carbon::parse($leave->getRawOriginal('end_date'));
            $leaveStart = $leaveStartDate->greaterThan($weekStart) ? $leaveStartDate : $weekStart->copy();
            $leaveEnd = $leaveEndDate->lessThan($weekEnd) ? $leaveEndDate : $weekEnd->copy();
            for ($date = $leaveStart; $date->lte($leaveEnd); $date->addDay()) {
                $leaveByEmployeeAndDate[$leave->employee_id][$date->toDateString()] = $leave;
            }
        }

        $days = collect(range(0, 6))->map(fn (int $offset) => $weekStart->copy()->addDays($offset));
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.roster.index', compact(
            'employees',
            'attendances',
            'schedules',
            'leaveByEmployeeAndDate',
            'days',
            'selectedDate',
            'minimumDate',
            'maximumDate',
            'weekStart',
            'weekEnd',
            'departments',
            'departmentId'
        ));
    }

    public function editSchedule(Request $request): View
    {
        $minimumDate = today()->subMonthNoOverflow();
        $maximumDate = today()->addMonthNoOverflow();
        $validated = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'date' => ['nullable', 'date', 'after_or_equal:'.$minimumDate->toDateString(), 'before_or_equal:'.$maximumDate->toDateString()],
        ]);

        $selectedDate = Carbon::parse($validated['date'] ?? today());
        $employees = Employee::active()->orderBy('full_name')->get(['id', 'full_name', 'employee_code']);
        $selectedEmployee = isset($validated['employee_id'])
            ? Employee::active()->find($validated['employee_id'])
            : null;
        $schedule = null;
        $leave = false;

        if ($selectedEmployee && !$selectedDate->isWeekend()) {
            $schedule = RosterSchedule::where('employee_id', $selectedEmployee->id)
                ->where('date', $selectedDate->toDateString())
                ->first();
            $leave = Leave::where('employee_id', $selectedEmployee->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $selectedDate->toDateString())
                ->whereDate('end_date', '>=', $selectedDate->toDateString())
                ->exists();
        }

        return view('admin.roster.schedule', compact(
            'employees',
            'selectedEmployee',
            'selectedDate',
            'minimumDate',
            'maximumDate',
            'schedule',
            'leave'
        ));
    }

    public function updateSchedule(Request $request)
    {
        $minimumDate = today()->subMonthNoOverflow()->toDateString();
        $maximumDate = today()->addMonthNoOverflow()->toDateString();
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'date' => ['required', 'date', 'after_or_equal:'.$minimumDate, 'before_or_equal:'.$maximumDate],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'end_time.after' => 'Jam pulang harus setelah jam masuk.',
            'reason.required' => 'Alasan perubahan jadwal wajib diisi.',
        ]);

        $date = Carbon::parse($validated['date']);
        if ($date->isWeekend()) {
            throw ValidationException::withMessages(['date' => 'Jadwal kerja hanya dapat direvisi untuk hari Senin-Jumat.']);
        }

        $hasApprovedLeave = Leave::where('employee_id', $validated['employee_id'])
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->exists();

        if ($hasApprovedLeave) {
            throw ValidationException::withMessages(['date' => 'Jadwal tidak dapat direvisi pada tanggal cuti yang sudah disetujui.']);
        }

        DB::transaction(function () use ($validated, $date) {
            RosterSchedule::updateOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'date' => $date->toDateString(),
                ],
                [
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'reason' => trim($validated['reason']),
                    'updated_by' => Auth::id(),
                ]
            );
        });

        return redirect()->route('roster.index', ['date' => $date->toDateString()])
            ->with('success', 'Jadwal kerja pegawai berhasil direvisi. Jam baru akan menjadi acuan absensi.');
    }
}