<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nik',
        'avatar',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function hasHrAdminAccess(): bool
    {
        if ($this->isHr()) {
            return true;
        }
        if ($this->isFinance()) {
            return false;
        }

        $employee = $this->employee;
        if (!$employee || !$employee->department_id || !$employee->position_id) {
            return false;
        }

        $employee->loadMissing(['department', 'position']);
        $department = $employee->department;
        $position = $employee->position;

        if (!$department || !$position || $position->department_id !== $employee->department_id) {
            return false;
        }

        $departmentName = strtolower(trim($department->name));
        $departmentCode = strtoupper(trim((string) $department->code));
        $positionName = strtolower(trim($position->name));

        $isHrDepartment = in_array($departmentCode, ['HR', 'HRD'], true)
            || preg_match('/\bhr\b/i', $departmentName) === 1
            || str_contains($departmentName, 'human resource');
        $isHrPosition = preg_match('/\bhr\b/i', $positionName) === 1
            || str_contains($positionName, 'human resource');

        return $isHrDepartment && $isHrPosition;
    }

public function isKaryawan(): bool
{
    return $this->role === 'karyawan';
}

public function isFinance(): bool
{
    return $this->role === 'finance';
}

public function getDisplayTitleAttribute(): string
{
    if ($this->hasHrAdminAccess()) {
        return 'HR Admin';
    }

    if ($this->isFinance()) {
        return 'Finance Admin';
    }

    $positionName = trim((string) $this->employee?->position?->name);

    return $positionName !== '' ? $positionName : 'Karyawan';
}

public function homeRouteName(): string
{
    if ($this->hasHrAdminAccess()) {
        return 'dashboard';
    }

    return $this->isFinance() ? 'finance.index' : 'karyawan.home';
}

public function avatarUrl(): string
{
    if ($this->avatar) {
        return asset('storage/' . $this->avatar);
    }
    return '';
}
public function employee()
{
    return $this->hasOne(Employee::class);
}

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
