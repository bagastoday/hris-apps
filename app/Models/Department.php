<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function isFinanceDepartment(): bool
    {
        $code = Str::upper(trim((string) $this->code));
        $name = Str::lower(trim($this->name));

        return in_array($code, ['FIN', 'FINANCE', 'ACCOUNTING', 'AKUNTANSI', 'KEUANGAN'], true)
            || Str::contains($name, ['finance', 'accounting', 'akuntansi', 'keuangan']);
    }
}
