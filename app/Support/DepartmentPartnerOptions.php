<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Support/DepartmentPartnerOptions.php
|--------------------------------------------------------------------------
*/

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DepartmentPartnerOptions
{
    public static function forUser(User $user, ?string $branch = null): Collection
    {
        $branch = $branch ?: ($user->branch ?? 'HO / Pusat');
        $department = self::normalizeDepartment($user->department ?? null);

        return User::query()
            ->where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->when($department !== '', function ($query) use ($department) {
                $query->whereRaw('UPPER(TRIM(COALESCE(department, ""))) = ?', [$department]);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'department']);
    }

    public static function normalizePartner(?string $partner, User $user, ?string $branch = null): ?string
    {
        $partner = trim((string) $partner);

        if ($partner === '') {
            return null;
        }

        $allowedNames = self::forUser($user, $branch)
            ->pluck('name')
            ->map(fn ($name) => self::normalizeName($name))
            ->values();

        if (!$allowedNames->contains(self::normalizeName($partner))) {
            throw ValidationException::withMessages([
                'partner' => 'Partner harus berasal dari branch dan department yang sama.',
            ]);
        }

        return $partner;
    }

    private static function normalizeDepartment(?string $department): string
    {
        return strtoupper(trim((string) $department));
    }

    private static function normalizeName(?string $name): string
    {
        return strtoupper(trim((string) $name));
    }
}
