<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartMovementController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartMovementController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function index(Request $request)
    {
        abort_unless($this->canAccess(), 403, 'Histori sparepart rental hanya untuk department RENTAL, admin, dan super admin.');

        $filters = [
            'movement_type' => strtoupper(trim((string) $request->input('movement_type', ''))),
            'part_number' => strtoupper(trim((string) $request->input('part_number', ''))),
            'sn_unit' => strtoupper(trim((string) $request->input('sn_unit', ''))),
            'no_job' => strtoupper(trim((string) $request->input('no_job', ''))),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
            'cross_allocation' => trim((string) $request->input('cross_allocation', '')),
        ];

        $query = RentalSparepartMovement::query()
            ->with(['item', 'stock.location', 'pic'])
            ->where('department', self::DEPARTMENT);

        if ($filters['movement_type'] !== '') {
            $query->where('movement_type', $filters['movement_type']);
        }

        if ($filters['part_number'] !== '') {
            $query->where('part_number_snapshot', 'like', '%' . $filters['part_number'] . '%');
        }

        if ($filters['sn_unit'] !== '') {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('source_sn_unit', 'like', '%' . $filters['sn_unit'] . '%')
                    ->orWhere('allocation_sn_unit', 'like', '%' . $filters['sn_unit'] . '%')
                    ->orWhere('actual_sn_unit', 'like', '%' . $filters['sn_unit'] . '%');
            });
        }

        if ($filters['no_job'] !== '') {
            $query->where('no_job', 'like', '%' . $filters['no_job'] . '%');
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('movement_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('movement_date', '<=', $filters['date_to']);
        }

        if ($filters['cross_allocation'] === 'yes') {
            $query->where('is_cross_allocation', true);
        }

        if ($filters['cross_allocation'] === 'no') {
            $query->where('is_cross_allocation', false);
        }

        $movements = $query
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $summary = $this->summary();

        return view('rental-spareparts.movements.index', compact('movements', 'filters', 'summary'));
    }

    private function summary(): array
    {
        $base = RentalSparepartMovement::query()->where('department', self::DEPARTMENT);

        return [
            'total' => (clone $base)->count(),
            'in' => (clone $base)->where('movement_type', RentalSparepartMovement::TYPE_IN)->count(),
            'out' => (clone $base)->where('movement_type', RentalSparepartMovement::TYPE_OUT)->count(),
            'cross' => (clone $base)->where('is_cross_allocation', true)->count(),
        ];
    }

    private function canAccess(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT;
    }
}
