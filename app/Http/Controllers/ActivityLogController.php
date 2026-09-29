<?php

// app/Http/Controllers/ActivityLogController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $action = $request->input('action');
        $role = $request->input('role');
        $date = $request->input('date');

        $query = ActivityLog::with('user')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($role, fn ($q) => $q->where('user_role', $role))
            ->when($date, fn ($q) => $q->whereDate('created_at', $date));

        $logs = (clone $query)->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_today' => ActivityLog::whereDate('created_at', today())->count(),
            'total_leave_actions' => ActivityLog::whereIn('action', ['approve_leave', 'reject_leave'])->count(),
            'total_finance_actions' => ActivityLog::whereIn('action', ['update_salary', 'create_payroll', 'finalize_payroll', 'paid_payroll'])->count(),
            'total_employee_actions' => ActivityLog::whereIn('action', ['create_employee', 'update_employee', 'delete_employee', 'reset_password'])->count(),
        ];

        $actions = ActivityLog::select('action')->distinct()->pluck('action');

        return view('admin.activity_logs.index', compact('logs', 'stats', 'search', 'action', 'role', 'date', 'actions'));
    }
}
