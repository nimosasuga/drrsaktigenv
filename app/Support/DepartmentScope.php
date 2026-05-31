<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Support/DepartmentScope.php
|--------------------------------------------------------------------------
*/

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DepartmentScope
{
    public static function userCanSeeAllDepartments(?User $user = null): bool
    {
        $user = $user ?: Auth::user();

        if (!$user) {
            return false;
        }

        return in_array($user->status_user, [
            'sect_head',
            'admin',
            'super_admin',
        ], true);
    }

    public static function currentDepartment(?User $user = null): ?string
    {
        $user = $user ?: Auth::user();
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return $department !== '' ? $department : null;
    }

    public static function apply(EloquentBuilder|QueryBuilder $query, string $tableName, ?User $user = null): void
    {
        $user = $user ?: Auth::user();

        if (self::userCanSeeAllDepartments($user)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'department')) {
            $query->whereRaw('1 = 0');
            return;
        }

        $department = self::currentDepartment($user);

        if (!$department) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where($tableName . '.department', $department);
    }

    public static function valueForCreate(?User $user = null): ?string
    {
        return self::currentDepartment($user);
    }
}
