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

    /**
     * URL atau Data URI foto absen masuk yang kompatibel lintas perangkat.
     */
    public function getCheckInPhotoUrlAttribute(): ?string
    {
        if (! $this->check_in_photo) {
            return null;
        }

        if (str_starts_with($this->check_in_photo, 'data:image') || str_starts_with($this->check_in_photo, 'http')) {
            return $this->check_in_photo;
        }

        return asset('storage/' . $this->check_in_photo);
    }

    /**
     * URL atau Data URI foto absen pulang yang kompatibel lintas perangkat.
     */
    public function getCheckOutPhotoUrlAttribute(): ?string
    {
        if (! $this->check_out_photo) {
            return null;
        }

        if (str_starts_with($this->check_out_photo, 'data:image') || str_starts_with($this->check_out_photo, 'http')) {
            return $this->check_out_photo;
        }

        return asset('storage/' . $this->check_out_photo);
    }
}