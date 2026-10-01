<?php

// app/Models/ActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'user_title',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper praktis untuk merekam log aktivitas dari mana saja
     */
    public static function record(string $action, string $description, ?Model $subject = null): self
    {
        $user = Auth::user();
        $isHrAdmin = $user?->hasHrAdminAccess() ?? false;

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistem',
            'user_role' => $isHrAdmin ? 'hr' : ($user?->role ?? 'system'),
            'user_title' => $isHrAdmin
                ? ($user->employee?->position?->name ?? $user->display_title)
                : null,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
