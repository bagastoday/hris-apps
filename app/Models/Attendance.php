<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
protected $fillable = [
    'employee_id',
    'date',
    'check_in',
    'check_out',
    'check_in_photo',
    'check_out_photo',
    'status',
    'notes',
];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}