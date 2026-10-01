<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RosterSchedule extends Model
{
    public const DEFAULT_START_TIME = '08:30:00';

    public const DEFAULT_END_TIME = '17:00:00';

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'reason',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}