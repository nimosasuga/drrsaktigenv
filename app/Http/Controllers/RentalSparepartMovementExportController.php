<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartMovementExportController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartMovementExportController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function __invoke(Request $request)
    {
        abort_unless($this->canAccess(), 403);

        $query = RentalSparepartMovement::query()
            ->with(['stock.location'])
            ->where('department', self::DEPARTMENT);

        $this->applyFilters($query, $request);

        $rows = [];
        $rows[] = [
            'ID',
            'Tanggal',
            'Movement Type',
            'Part Number',
            'Part Name',
            'Qty',
            'No Job',
            'Source Customer',
            'Source Location',
            'Source Type Unit',
            'Source SN Unit',
            'Allocation Customer',
            'Allocation Location',
            'Allocation Type Unit',
            'Allocation SN Unit',
            'Actual Customer',
            'Actual Location',
            'Actual Type Unit',
            'Actual SN Unit',
            'Cross Allocation',
            'PIC',
            'Lokasi Penyimpanan',
            'Remarks',
            'Created At',
        ];

        $query->orderByDesc('movement_date')->orderByDesc('id')->chunk(500, function ($movements) use (&$rows) {
            foreach ($movements as $movement) {
                $rows[] = [
                    $movement->id,
                    optional($movement->movement_date)->format('Y-m-d'),
                    $movement->movement_type,
                    $movement->part_number_snapshot,
                    $movement->part_name_snapshot,
                    $movement->qty,
                    $movement->no_job,
                    $movement->source_customer,
                    $movement->source_location,
                    $movement->source_type_unit,
                    $movement->source_sn_unit,
                    $movement->allocation_customer,
                    $movement->allocation_location,
                    $movement->allocation_type_unit,
                    $movement->allocation_sn_unit,
                    $movement->actual_customer,
                    $movement->actual_location,
                    $movement->actual_type_unit,
                    $movement->actual_sn_unit,
                    $movement->is_cross_allocation ? 'YES' : 'NO',
                    $movement->pic_name,
                    $movement->stock?->location?->location_name,
                    $movement->remarks,
                    optional($movement->created_at)->format('Y-m-d H:i:s'),
                ];
            }
        });

        $csv = "\xEF\xBB\xBF" . collect($rows)->map(function ($row) {
            return collect($row)->map(fn ($value) => $this->csvCell($value))->implode(',');
        })->implode("\n");

        $filename = 'rental_sparepart_movements_' . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        $movementType = strtoupper(trim((string) $request->input('movement_type', '')));
        $partNumber = strtoupper(trim((string) $request->input('part_number', '')));
        $snUnit = strtoupper(trim((string) $request->input('sn_unit', '')));
        $noJob = strtoupper(trim((string) $request->input('no_job', '')));
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));
        $crossAllocation = trim((string) $request->input('cross_allocation', ''));

        if ($movementType !== '') {
            $query->where('movement_type', $movementType);
        }

        if ($partNumber !== '') {
            $query->where('part_number_snapshot', 'like', '%' . $partNumber . '%');
        }

        if ($snUnit !== '') {
            $query->where(function ($subQuery) use ($snUnit) {
                $subQuery->where('source_sn_unit', 'like', '%' . $snUnit . '%')
                    ->orWhere('allocation_sn_unit', 'like', '%' . $snUnit . '%')
                    ->orWhere('actual_sn_unit', 'like', '%' . $snUnit . '%');
            });
        }

        if ($noJob !== '') {
            $query->where('no_job', 'like', '%' . $noJob . '%');
        }

        if ($dateFrom !== '') {
            $query->whereDate('movement_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('movement_date', '<=', $dateTo);
        }

        if ($crossAllocation === 'yes') {
            $query->where('is_cross_allocation', true);
        }

        if ($crossAllocation === 'no') {
            $query->where('is_cross_allocation', false);
        }
    }

    private function csvCell($value): string
    {
        $value = (string) $value;
        $value = str_replace('"', '""', $value);

        return '"' . $value . '"';
    }

    private function canAccess(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT;
    }
}
