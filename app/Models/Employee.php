<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // Password default untuk akun pegawai baru / reset password
    public const DEFAULT_PASSWORD = 'Talenta123';

    protected $fillable = [
        'user_id',
        'employee_code',
        'full_name',
        'nik',
        'email',
        'phone',
        'gender',
        'birth_date',
        'join_date',
        'department_id',
        'position_id',
        'employment_status',
        'base_salary',
        'photo',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    // Pegawai aktif = semua status kecuali resign (aktif, kontrak, magang, cuti)
    public function isActive(): bool
    {
        return $this->employment_status !== 'resign';
    }

    public function scopeActive($query)
    {
        return $query->where('employment_status', '!=', 'resign');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}