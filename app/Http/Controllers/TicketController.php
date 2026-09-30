<?php

// app/Http/Controllers/TicketController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        $query = $this->teamTickets()
            ->with(['user.employee.department', 'assignedTo'])
            ->withUnreadForTeam()
            ->latest();

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

        $statsQuery = $this->teamTickets();
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'open' => (clone $statsQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $statsQuery)->where('status', 'resolved')->count(),
        ];

        return view('admin.tickets.index', compact('tickets', 'stats', 'search', 'status', 'priority', 'category'));
    }

    /**
     * Detail tiket bantuan & percakapan untuk HR Admin.
     */
    public function show(Ticket $ticket)
    {
        $this->authorizeTicketTeam($ticket);
        $ticket->forceFill([
            'team_last_read_reply_id' => $ticket->replies()->max('id') ?? 0,
        ])->save();
        $ticket->load([
            'user.employee.department',
            'user.employee.position',
            'assignedTo',
            'replies.user',
            'reimbursementReviewer',
            'financeTransaction',
        ]);

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Perbarui status tiket bantuan oleh HR.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorizeTicketTeam($ticket);
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
        $this->authorizeTicketTeam($ticket);
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

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
            'is_admin_reply' => true,
        ]);
        $ticket->team_last_read_reply_id = $reply->id;

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

        $query = Ticket::where('user_id', $user->id)->withUnreadForEmployee()->latest();

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
        $attachmentRules = $request->input('category') === 'reimburse'
            ? ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120']
            : ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|in:' . implode(',', array_keys(Ticket::CATEGORY_LABELS)),
            'priority' => 'required|in:rendah,sedang,tinggi,darurat',
            'description' => 'required|string|min:10',
            'reimbursement_amount' => [
                Rule::requiredIf($request->input('category') === 'reimburse'),
                'nullable',
                'integer',
                'min:1',
                'max:9999999999999',
            ],
            'attachment' => $attachmentRules,
            'is_anonymous' => 'nullable|boolean',
        ], [
            'title.required' => 'Subjek atau judul tiket wajib diisi.',
            'category.required' => 'Pilih kategori pengaduan / bantuan.',
            'description.required' => 'Jelaskan kronologi atau detail kendala kamu.',
            'description.min' => 'Deskripsi minimal 10 karakter.',
            'reimbursement_amount.required' => 'Nominal reimbursement wajib diisi.',
            'reimbursement_amount.min' => 'Nominal reimbursement harus lebih dari Rp0.',
            'attachment.required' => 'Foto bukti wajib dilampirkan untuk tiket reimbursement.',
            'attachment.image' => 'Bukti reimbursement harus berupa file gambar.',
            'attachment.max' => 'Ukuran berkas lampiran maksimal 5MB.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store(
                $validated['category'] === 'reimburse' ? 'reimbursement_proofs' : 'ticket_attachments',
                $validated['category'] === 'reimburse' ? 'local' : 'public'
            );
        }

        $ticket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'attachment' => $attachmentPath,
            'is_anonymous' => $validated['category'] === 'reimburse' ? false : $request->boolean('is_anonymous'),
            'reimbursement_amount' => $validated['reimbursement_amount'] ?? null,
            'reimbursement_status' => $validated['category'] === 'reimburse' ? 'pending' : null,
            'status' => 'open',
        ]);

        ActivityLog::record(
            'create_ticket',
            "Membuka tiket bantuan baru: #{$ticket->ticket_code} - \"{$ticket->title}\"",
            $ticket
        );

        return redirect()->route('karyawan.tickets.show', $ticket)
            ->with('success', "Tiket kamu berhasil dibuat dengan nomor referensi #{$ticket->ticket_code} dan diteruskan ke tim {$ticket->assigned_team_label}.");
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
        $ticket->forceFill([
            'employee_last_read_reply_id' => $ticket->replies()->max('id') ?? 0,
        ])->save();

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

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
            'is_admin_reply' => false,
        ]);
        $ticket->employee_last_read_reply_id = $reply->id;
        $ticket->save();

        // Jika status resolved, kembalikan ke in_progress jika karyawan membalas lagi
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Tanggapan kamu berhasil dikirimkan.');
    }

    public function reimbursementProof(Ticket $ticket)
    {
        abort_unless($ticket->category === 'reimburse' && $ticket->reimbursement_status, 404);
        abort_unless(
            $ticket->user_id === Auth::id() || Auth::user()->isFinance(),
            403,
            'Bukti reimbursement hanya dapat diakses oleh pemilik tiket dan tim Finance.'
        );
        abort_unless(Storage::disk('local')->exists($ticket->attachment), 404);

        $stream = Storage::disk('local')->readStream($ticket->attachment);
        abort_unless(is_resource($stream), 404);
        $mimeType = match (strtolower(pathinfo($ticket->attachment, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function teamTickets()
    {
        $query = Ticket::query();

        if (Auth::user()->isFinance()) {
            return $query->whereIn('category', Ticket::FINANCE_CATEGORIES);
        }

        return $query->whereNotIn('category', Ticket::FINANCE_CATEGORIES);
    }

    private function authorizeTicketTeam(Ticket $ticket): void
    {
        $userTeam = Auth::user()->isFinance() ? 'finance' : 'hr';
        abort_unless($ticket->assigned_team === $userTeam, 403, 'Tiket ini ditangani oleh tim lain.');
    }
}
