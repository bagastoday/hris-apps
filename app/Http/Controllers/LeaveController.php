<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    // HR: daftar semua pengajuan cuti
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $leaves = Leave::with('employee')
            ->when($status !== 'semua', fn($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('admin.leaves.index', compact('leaves', 'status'));
    }

    // HR: approve pengajuan
    public function approve(Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        \App\Models\ActivityLog::record(
            'approve_leave',
            "Menyetujui permohonan {$leave->leave_type} pegawai {$leave->employee?->full_name} ({$leave->total_days} hari)",
            $leave
        );

        return back()->with('success', 'Pengajuan cuti disetujui.');
    }

    // HR: reject pengajuan
    public function reject(Request $request, Leave $leave)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        \App\Models\ActivityLog::record(
            'reject_leave',
            "Menolak permohonan {$leave->leave_type} pegawai {$leave->employee?->full_name}. Alasan: {$request->rejection_reason}",
            $leave
        );

        return back()->with('success', 'Pengajuan cuti ditolak.');
    }

    // Karyawan: form pengajuan cuti
    public function create()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return redirect()->route('karyawan.home')->with('error', 'Data pegawai kamu belum terhubung dengan akun.');
        }

        $currentYear = now()->year;
        $annualUsed = Leave::where('employee_id', $employee->id)
            ->where('leave_type', 'cuti_tahunan')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');
        $remainingAnnual = max(0, 12 - $annualUsed);

        return view('karyawan.leaves.create', compact('employee', 'remainingAnnual'));
    }

    // Karyawan: riwayat pengajuan cuti sendiri dengan statistik kuota
    public function myLeaves()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return redirect()->route('karyawan.home')->with('error', 'Data pegawai kamu belum terhubung dengan akun. Hubungi HR.');
        }

        $leaves = Leave::where('employee_id', $employee->id)
            ->latest()
            ->paginate(10);

        $currentYear = now()->year;
        $annualQuota = 12; // Hak kuota cuti tahunan reguler
        $annualUsed = Leave::where('employee_id', $employee->id)
            ->where('leave_type', 'cuti_tahunan')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        $remainingAnnual = max(0, $annualQuota - $annualUsed);

        $sickAndPermitDays = Leave::where('employee_id', $employee->id)
            ->whereIn('leave_type', ['sakit', 'izin'])
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        $pendingCount = Leave::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        $stats = [
            'annual_quota' => $annualQuota,
            'annual_used' => $annualUsed,
            'remaining_annual' => $remainingAnnual,
            'sick_permit_used' => $sickAndPermitDays,
            'pending_count' => $pendingCount,
        ];

        return view('karyawan.leaves.index', compact('employee', 'leaves', 'stats'));
    }

    // Karyawan: simpan pengajuan cuti dengan validasi kuota & bentrok tanggal
    public function store(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'Data pegawai kamu belum terhubung, hubungi HR.');
        }

        $data = $request->validate([
            'leave_type' => 'required|in:cuti_tahunan,sakit,izin,cuti_melahirkan,cuti_penting,lainnya',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ], [
            'leave_type.required' => 'Pilih jenis izin / cuti.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'reason.required' => 'Alasan atau keterangan permohonan wajib diisi.',
        ]);

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $totalDays = $start->diffInDays($end) + 1;

        // Validasi kuota jika cuti tahunan
        if ($data['leave_type'] === 'cuti_tahunan') {
            $currentYear = now()->year;
            $annualUsed = Leave::where('employee_id', $employee->id)
                ->where('leave_type', 'cuti_tahunan')
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->sum('total_days');
            $remainingAnnual = max(0, 12 - $annualUsed);

            if ($totalDays > $remainingAnnual) {
                return back()->withErrors([
                    'total_days' => "Sisa cuti tahunan kamu hanya {$remainingAnnual} hari. Pengajuan {$totalDays} hari melebihi sisa kuota.",
                ])->withInput();
            }
        }

        // Cek overlap tanggal pengajuan aktif
        $hasOverlap = Leave::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                  ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                  ->orWhere(function ($sub) use ($data) {
                      $sub->where('start_date', '<=', $data['start_date'])
                          ->where('end_date', '>=', $data['end_date']);
                  });
            })->exists();

        if ($hasOverlap) {
            return back()->withErrors([
                'start_date' => 'Kamu sudah memiliki permohonan cuti/izin aktif pada rentang tanggal tersebut.',
            ])->withInput();
        }

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.my')->with('success', 'Pengajuan cuti/izin berhasil dikirim dan menunggu persetujuan HR.');
    }

    // Karyawan: batalkan pengajuan jika masih berstatus pending
    public function cancel(Leave $leave)
    {
        $employee = Auth::user()->employee;
        if (!$employee || $leave->employee_id !== $employee->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan berstatus menunggu (pending) yang dapat dibatalkan.');
        }

        $leave->delete();
        return back()->with('success', 'Pengajuan cuti/izin berhasil dibatalkan.');
    }
}