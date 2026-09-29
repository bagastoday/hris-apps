<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Str;

class EmployeeEmailGenerator
{
    private const DOMAIN = '@talenta.id';

    public static function generateUnique(string $fullName): string
    {
        $asciiName = strtolower(Str::ascii(trim($fullName)));
        $nameParts = preg_split('/\s+/', $asciiName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $nameParts = array_values(array_filter(array_map(
            fn (string $part): string => preg_replace('/[^a-z0-9]/', '', $part) ?? '',
            $nameParts
        )));
        $localPart = implode('.', array_slice($nameParts, 0, 2));
        $localPart = $localPart !== '' ? $localPart : 'pegawai';
        $localPart = substr($localPart, 0, 244);

        for ($suffix = 1; ; $suffix++) {
            $suffixText = $suffix === 1 ? '' : (string) $suffix;
            $candidate = substr($localPart, 0, 244 - strlen($suffixText)).$suffixText.self::DOMAIN;
            $emailInUse = User::where('email', $candidate)->exists()
                || Employee::where('email', $candidate)->exists();

            if (! $emailInUse) {
                return $candidate;
            }
        }
    }
}
