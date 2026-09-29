<?php

// app/Http/Controllers/AnnouncementController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    // HR: Halaman kelola pengumuman
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $category = $request->input('category');

        $announcements = Announcement::with('user')
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%"))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Announcement::count(),
            'active' => Announcement::where('is_active', true)->count(),
            'pinned' => Announcement::where('is_pinned', true)->count(),
        ];

        return view('admin.announcements.index', compact('announcements', 'stats', 'search', 'category'));
    }

    // HR: Simpan pengumuman baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:umum,libur,kebijakan,penting,event',
            'badge_color' => 'required|in:blue,emerald,amber,rose,purple',
            'is_pinned' => 'nullable|boolean',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'content.required' => 'Isi pengumuman wajib diisi.',
            'attachment.max' => 'Ukuran file lampiran maksimal 5MB.',
        ]);

        $data['user_id'] = Auth::id();
        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['is_active'] = true;
        $data['published_at'] = now();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('announcements', 'public');
        }

        $announcement = Announcement::create($data);

        ActivityLog::record(
            'create_announcement',
            "Menerbitkan pengumuman baru: \"{$announcement->title}\" (Kategori: {$announcement->category})",
            $announcement
        );

        return redirect()->route('announcements.index')->with('success', 'Pengumuman kantor berhasil dipublikasikan.');
    }

    // HR: Update pengumuman
    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:umum,libur,kebijakan,penting,event',
            'badge_color' => 'required|in:blue,emerald,amber,rose,purple',
            'is_pinned' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('attachment')) {
            if ($announcement->attachment && Storage::disk('public')->exists($announcement->attachment)) {
                Storage::disk('public')->delete($announcement->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('announcements', 'public');
        }

        $announcement->update($data);

        ActivityLog::record(
            'update_announcement',
            "Memperbarui pengumuman: \"{$announcement->title}\"",
            $announcement
        );

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    // HR: Hapus pengumuman
    public function destroy(Announcement $announcement)
    {
        $title = $announcement->title;

        if ($announcement->attachment && Storage::disk('public')->exists($announcement->attachment)) {
            Storage::disk('public')->delete($announcement->attachment);
        }

        $announcement->delete();

        ActivityLog::record(
            'delete_announcement',
            "Menghapus pengumuman: \"{$title}\""
        );

        return back()->with('success', "Pengumuman \"{$title}\" berhasil dihapus.");
    }

    // HR: Toggle status pin pengumuman
    public function togglePin(Announcement $announcement)
    {
        $announcement->update(['is_pinned' => !$announcement->is_pinned]);
        $status = $announcement->is_pinned ? 'disematkan di atas' : 'dilepas dari sematan';

        ActivityLog::record(
            'pin_announcement',
            "Mengubah status pin pengumuman: \"{$announcement->title}\" ({$status})",
            $announcement
        );

        return back()->with('success', "Pengumuman berhasil {$status}.");
    }

    // Karyawan: Daftar pengumuman kantor
    public function karyawanIndex(Request $request)
    {
        $category = $request->input('category');
        $search = trim((string) $request->input('search', ''));

        $announcements = Announcement::active()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%"))
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('karyawan.announcements.index', compact('announcements', 'category', 'search'));
    }
}
