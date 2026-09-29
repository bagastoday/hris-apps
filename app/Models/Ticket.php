<?php

// app/Models/Ticket.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_code',
        'user_id',
        'assigned_to',
        'title',
        'category',
        'priority',
        'status',
        'description',
        'attachment',
        'is_anonymous',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
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
        return match ($this->category) {
            'fasilitas' => 'Fasilitas & Sarana',
            'payroll' => 'Gaji & Payroll',
            'bpjs' => 'BPJS & Asuransi',
            'kebijakan' => 'Kebijakan HR & SOP',
            'it_support' => 'IT & Jaringan',
            'pengaduan' => 'Pengaduan / Whistleblowing',
            default => 'Lainnya',
        };
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
