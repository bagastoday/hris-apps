<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function store(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|max:50',
        ]);

        $data['department_id'] = $department->id;
        $data['is_active'] = true;

        Position::create($data);

        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus jabatan yang masih punya pegawai.');
        }

        $position->delete();

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}