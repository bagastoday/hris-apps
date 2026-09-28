<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount([
            // Hanya pegawai aktif (aktif, kontrak, magang, cuti)
            'employees as active_employees_count' => fn ($q) => $q->active(),
            // Pegawai nonaktif (resign), ditampilkan terpisah
            'employees as inactive_employees_count' => fn ($q) => $q->where('employment_status', 'resign'),
        ])->latest()->get();

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:departments,code',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Department::create($data);

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function edit(Department $department)
    {
        $department->load('positions');
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $department->update($data);

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        // Semua pegawai (termasuk yang nonaktif) tetap dihitung agar riwayat datanya tidak hilang
        if ($department->employees()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus departemen yang masih punya data pegawai (termasuk yang nonaktif). Nonaktifkan departemennya saja lewat menu Edit.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil dihapus.');
    }
}