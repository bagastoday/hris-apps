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

        return back()->with('success', 'Pengajuan cuti ditolak.');
    }

    // Karyawan: form pengajuan cuti
    public function create()
    {
        return view('karyawan.leaves.create');
    }

    // Karyawan: riwayat pengajuan cuti sendiri
    public function myLeaves()
    {
        $employee = Auth::user()->employee;

        $leaves = Leave::where('employee_id', $employee?->id)
            ->latest()
            ->get();

        return view('karyawan.leaves.index', compact('leaves'));
    }

    // Karyawan: simpan pengajuan cuti
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
            'reason' => 'nullable|string|max:500',
        ]);

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $totalDays = $start->diffInDays($end) + 1;

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.my')->with('success', 'Pengajuan cuti berhasil dikirim, menunggu persetujuan HR.');
    }
}