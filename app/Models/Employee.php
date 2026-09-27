<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
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
        'photo',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
    ];

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