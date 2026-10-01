<?php

// app/Models/Ticket.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    public const CATEGORY_LABELS = [
        'fasilitas' => 'Fasilitas & Sarana Kantor',
        'reimburse' => 'Reimburse',
        'payroll' => 'Gaji & Payroll',
        'keuangan' => 'Pertanyaan Keuangan',
        'bpjs' => 'BPJS & Asuransi',
        'data_karyawan' => 'Data Karyawan',
        'akun_karyawan' => 'Akun & Akses Karyawan',
        'kebijakan' => 'Kebijakan HR & SOP',
        'it_support' => 'IT & Jaringan',
        'pengaduan' => 'Pengaduan / Whistleblowing',
        'lainnya' => 'Pertanyaan Umum / Lainnya',
    ];

    public const FINANCE_CATEGORIES = ['reimburse', 'payroll', 'keuangan'];

    protected $fillable = [
        'ticket_code',
        'user_id',
        'assigned_to',
        'team_last_read_reply_id',
        'employee_last_read_reply_id',
        'title',
        'category',
        'priority',
        'status',
        'description',
        'attachment',
        'is_anonymous',
        'resolved_at',
        'closed_at',
        'reimbursement_amount',
        'reimbursement_status',
        'reimbursement_review_note',
        'reimbursement_reviewed_by',
        'reimbursement_reviewed_at',
        'finance_transaction_id',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'reimbursement_amount' => 'decimal:2',
        'reimbursement_reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    public function reimbursementReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reimbursement_reviewed_by');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class);
    }

    public function scopeWithUnreadForTeam(Builder $query): Builder
    {
        return $query->withExists([
            'replies as has_unread_for_team' => fn (Builder $replies) => $replies
                ->where('is_admin_reply', false)
                ->whereColumn('ticket_replies.id', '>', 'tickets.team_last_read_reply_id'),
        ]);
    }

    public function scopeWithUnreadForEmployee(Builder $query): Builder
    {
        return $query->withExists([
            'replies as has_unread_for_employee' => fn (Builder $replies) => $replies
                ->where('is_admin_reply', true)
                ->whereColumn('ticket_replies.id', '>', 'tickets.employee_last_read_reply_id'),
        ]);
    }

    public function scopeUnreadForTeam(Builder $query): Builder
    {
        return $query->where(function (Builder $tickets) {
            $tickets->whereNull('team_last_read_reply_id')
                ->orWhereHas('replies', fn (Builder $replies) => $replies
                    ->where('is_admin_reply', false)
                    ->whereColumn('ticket_replies.id', '>', 'tickets.team_last_read_reply_id'));
        });
    }

    public function scopeUnreadForEmployee(Builder $query): Builder
    {
        return $query->whereHas('replies', fn (Builder $replies) => $replies
            ->where('is_admin_reply', true)
            ->whereColumn('ticket_replies.id', '>', 'tickets.employee_last_read_reply_id'));
    }

    public static function generateTicketCode(): string
    {
        $prefix = 'TKT-' . date('Ym') . '-';
        $latest = self::where('ticket_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->ticket_code, -4);
            $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? 'Lainnya';
    }

    public function getAssignedTeamAttribute(): string
    {
        return in_array($this->category, self::FINANCE_CATEGORIES, true) ? 'finance' : 'hr';
    }

    public function getAssignedTeamLabelAttribute(): string
    {
        return $this->assigned_team === 'finance' ? 'Finance' : 'HR';
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'darurat' => 'Darurat (Urgent)',
            'tinggi' => 'Tinggi',
            'sedang' => 'Sedang',
            'rendah' => 'Rendah',
            default => 'Sedang',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Menunggu Tanggapan',
            'in_progress' => 'Sedang Diproses',
            'resolved' => 'Selesai Ditangani',
            'closed' => 'Ditutup',
            default => 'Menunggu',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-300',
            'in_progress' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-300',
            'resolved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-300',
            'closed' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-300',
            default => 'bg-slate-100 text-slate-600 ring-1 ring-slate-300',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'darurat' => 'bg-red-50 text-red-700 ring-1 ring-red-300 font-bold',
            'tinggi' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
            'sedang' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
            'rendah' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
            default => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
        };
    }

    public function getDisplayAuthorAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Anonim (Pengaduan Rahasia)';
        }

        return $this->user?->name ?? 'Karyawan';
    }
}
