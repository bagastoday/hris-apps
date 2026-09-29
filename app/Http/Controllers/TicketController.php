<?php

// app/Http/Controllers/TicketController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    // ==========================================
    // SISI HR ADMIN
    // ==========================================

    /**
     * Menampilkan daftar tiket bantuan di panel HR.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');
        $priority = $request->input('priority');
        $category = $request->input('category');

        $query = Ticket::with(['user.employee.department', 'assignedTo'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($category) {
            $query->where('category', $category);
        }

        $tickets = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
        ];

        return view('admin.tickets.index', compact('tickets', 'stats', 'search', 'status', 'priority', 'category'));
    }

    /**
     * Detail tiket bantuan & percakapan untuk HR Admin.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['user.employee.department', 'user.employee.position', 'assignedTo', 'replies.user']);

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Perbarui status tiket bantuan oleh HR.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:rendah,sedang,tinggi,darurat',
        ]);

        $oldStatus = $ticket->status;
        $ticket->status = $validated['status'];

        if (!empty($validated['priority'])) {
            $ticket->priority = $validated['priority'];
        }

        if ($validated['status'] === 'resolved' && !$ticket->resolved_at) {
            $ticket->resolved_at = now();
        } elseif ($validated['status'] === 'closed' && !$ticket->closed_at) {
            $ticket->closed_at = now();
        }

        if (!$ticket->assigned_to) {
            $ticket->assigned_to = Auth::id();
        }

        $ticket->save();

        ActivityLog::record(
            'update_ticket_status',
            "Mengubah status tiket #{$ticket->ticket_code} dari {$oldStatus} ke {$ticket->status}",
            $ticket
        );

        return back()->with('success', "Status tiket #{$ticket->ticket_code} berhasil diubah menjadi {$ticket->status_label}.");
    }

    /**
     * Kirim balasan/tanggapan dari HR pada tiket.
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:3',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'change_status' => 'nullable|in:in_progress,resolved,closed',
        ], [
            'message.required' => 'Pesan tanggapan wajib diisi.',
            'attachment.max' => 'Ukuran berkas lampiran maksimal 5MB.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('ticket_attachments', 'public');
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
            'is_admin_reply' => true,
        ]);

        // Auto update status jika ditentukan
        if (!empty($validated['change_status'])) {
            $ticket->status = $validated['change_status'];
            if ($validated['change_status'] === 'resolved' && !$ticket->resolved_at) {
                $ticket->resolved_at = now();
            }
        } elseif ($ticket->status === 'open') {
            $ticket->status = 'in_progress';
        }

        $ticket->assigned_to = Auth::id();
        $ticket->save();

        ActivityLog::record(
            'reply_ticket',
            "Memberikan tanggapan pada tiket #{$ticket->ticket_code}",
            $ticket
        );

        return back()->with('success', 'Tanggapan berhasil dikirimkan ke karyawan.');
    }

    // ==========================================
    // SISI PORTAL KARYAWAN
    // ==========================================

    /**
     * Halaman daftar tiket bantuan karyawan yang sedang login.
     */
    public function karyawanIndex(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status');
        $search = trim((string) $request->input('search', ''));

        $query = Ticket::where('user_id', $user->id)->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Ticket::where('user_id', $user->id)->count(),
            'open' => Ticket::where('user_id', $user->id)->where('status', 'open')->count(),
            'in_progress' => Ticket::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('user_id', $user->id)->where('status', 'resolved')->count(),
        ];

        return view('karyawan.tickets.index', compact('tickets', 'stats', 'status', 'search'));
    }

    /**
     * Halaman formulir pembuatan tiket bantuan baru.
     */
    public function karyawanCreate()
    {
        return view('karyawan.tickets.create');
    }

    /**
     * Simpan tiket bantuan baru oleh karyawan.
     */
    public function karyawanStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|in:fasilitas,payroll,bpjs,kebijakan,it_support,pengaduan,lainnya',
            'priority' => 'required|in:rendah,sedang,tinggi,darurat',
            'description' => 'required|string|min:10',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_anonymous' => 'nullable|boolean',
        ], [
            'title.required' => 'Subjek atau judul tiket wajib diisi.',
            'category.required' => 'Pilih kategori pengaduan / bantuan.',
            'description.required' => 'Jelaskan kronologi atau detail kendala kamu.',
            'description.min' => 'Deskripsi minimal 10 karakter.',
            'attachment.max' => 'Ukuran berkas lampiran maksimal 5MB.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('ticket_attachments', 'public');
        }

        $ticket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'attachment' => $attachmentPath,
            'is_anonymous' => $request->boolean('is_anonymous'),
            'status' => 'open',
        ]);

        ActivityLog::record(
            'create_ticket',
            "Membuka tiket bantuan baru: #{$ticket->ticket_code} - \"{$ticket->title}\"",
            $ticket
        );

        return redirect()->route('karyawan.tickets.show', $ticket)
            ->with('success', "Tiket kamu berhasil dibuat dengan nomor referensi #{$ticket->ticket_code}. Tim HR akan segera merespons.");
    }

    /**
     * Halaman detail tiket & percakapan untuk karyawan.
     */
    public function karyawanShow(Ticket $ticket)
    {
        // Pastikan tiket adalah milik user yang sedang login
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Kamu tidak memiliki akses ke tiket ini.');
        }

        $ticket->load(['assignedTo', 'replies.user']);

        return view('karyawan.tickets.show', compact('ticket'));
    }

    /**
     * Karyawan mengirim balasan pada tiket mereka.
     */
    public function karyawanReply(Request $request, Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Kamu tidak memiliki akses ke tiket ini.');
        }

        if ($ticket->status === 'closed') {
            return back()->withErrors(['message' => 'Tiket ini telah ditutup dan tidak dapat menerima tanggapan baru.']);
        }

        $validated = $request->validate([
            'message' => 'required|string|min:3',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'message.required' => 'Pesan tanggapan wajib diisi.',
            'attachment.max' => 'Ukuran berkas lampiran maksimal 5MB.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('ticket_attachments', 'public');
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
            'is_admin_reply' => false,
        ]);

        // Jika status resolved, kembalikan ke in_progress jika karyawan membalas lagi
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Tanggapan kamu berhasil dikirimkan.');
    }
}
