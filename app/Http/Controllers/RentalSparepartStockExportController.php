<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartStockExportController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartStockExportController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function __invoke(Request $request)
    {
        abort_unless($this->canAccess(), 403);

        $lifecycleStatus = strtoupper(trim((string) $request->input('stock_lifecycle_status', RentalSparepartStock::STATUS_ACTIVE)));

        if (!in_array($lifecycleStatus, [RentalSparepartStock::STATUS_ACTIVE, RentalSparepartStock::STATUS_ARCHIVED], true)) {
            $lifecycleStatus = RentalSparepartStock::STATUS_ACTIVE;
        }

        $query = RentalSparepartStock::query()
            ->with(['item', 'location'])
            ->where('department', self::DEPARTMENT)
            ->where('stock_lifecycle_status', $lifecycleStatus);

        $this->applyFilters($query, $request, $lifecycleStatus);

        $rows = [];
        $rows[] = [
            'ID',
            'Department',
            'Lifecycle Status',
            'Part Number',
            'Part Name',
            'Default Type Unit',
            'Min Stock',
            'Qty On Hand',
            'Qty Reserved',
            'Qty Available',
            'Stock Status',
            'Location Code',
            'Location Name',
            'Cabinet',
            'Shelf',
            'Box',
            'No Job',
            'Source Customer',
            'Source Location',
            'Source Type Unit',
            'Source SN Unit',
            'Allocation Customer',
            'Allocation Location',
            'Allocation Type Unit',
            'Allocation SN Unit',
            'Problem Flags',
            'Remarks',
            'Updated At',
        ];

        $query->orderByDesc('updated_at')->orderByDesc('id')->chunk(500, function ($stocks) use (&$rows) {
            foreach ($stocks as $stock) {
                $rows[] = [
                    $stock->id,
                    $stock->department,
                    $stock->stock_lifecycle_status,
                    $stock->item?->part_number,
                    $stock->item?->part_name,
                    $stock->item?->default_type_unit,
                    $stock->item?->min_stock,
                    $stock->qty_on_hand,
                    $stock->qty_reserved,
                    $stock->qty_available,
                    $this->stockStatus($stock),
                    $stock->location?->location_code,
                    $stock->location?->location_name,
                    $stock->location?->cabinet,
                    $stock->location?->shelf,
                    $stock->location?->box,
                    $stock->source_no_job,
                    $stock->source_customer,
                    $stock->source_location,
                    $stock->source_type_unit,
                    $stock->source_sn_unit,
                    $stock->allocation_customer,
                    $stock->allocation_location,
                    $stock->allocation_type_unit,
                    $stock->allocation_sn_unit,
                    implode(' | ', $this->problemFlags($stock)),
                    $stock->remarks,
                    optional($stock->updated_at)->format('Y-m-d H:i:s'),
                ];
            }
        });

        $csv = "\xEF\xBB\xBF" . collect($rows)->map(function ($row) {
            return collect($row)->map(fn ($value) => $this->csvCell($value))->implode(',');
        })->implode("\n");

        $filename = 'rental_sparepart_stocks_' . strtolower($lifecycleStatus) . '_' . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function applyFilters($query, Request $request, string $lifecycleStatus): void
    {
        $search = trim((string) $request->input('search', ''));
        $locationId = $request->input('location_id');
        $customer = trim((string) $request->input('customer', ''));
        $snUnit = trim((string) $request->input('sn_unit', ''));
        $noJob = trim((string) $request->input('no_job', ''));
        $stockStatus = trim((string) $request->input('stock_status', ''));

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('source_no_job', 'like', "%{$search}%")
                    ->orWhere('source_customer', 'like', "%{$search}%")
                    ->orWhere('source_location', 'like', "%{$search}%")
                    ->orWhere('source_sn_unit', 'like', "%{$search}%")
                    ->orWhere('allocation_customer', 'like', "%{$search}%")
                    ->orWhere('allocation_location', 'like', "%{$search}%")
                    ->orWhere('allocation_sn_unit', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($itemQuery) use ($search) {
                        $itemQuery->where('part_number', 'like', "%{$search}%")
                            ->orWhere('part_name', 'like', "%{$search}%")
                            ->orWhere('default_type_unit', 'like', "%{$search}%");
                    })
                    ->orWhereHas('location', function ($locationQuery) use ($search) {
                        $locationQuery->where('location_code', 'like', "%{$search}%")
                            ->orWhere('location_name', 'like', "%{$search}%")
                            ->orWhere('cabinet', 'like', "%{$search}%")
                            ->orWhere('box', 'like', "%{$search}%");
                    });
            });
        }

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($customer !== '') {
            $query->where(function ($subQuery) use ($customer) {
                $subQuery->where('source_customer', $customer)
                    ->orWhere('allocation_customer', $customer);
            });
        }

        if ($snUnit !== '') {
            $query->where(function ($subQuery) use ($snUnit) {
                $subQuery->where('source_sn_unit', $snUnit)
                    ->orWhere('allocation_sn_unit', $snUnit);
            });
        }

        if ($noJob !== '') {
            $query->where('source_no_job', $noJob);
        }

        if ($lifecycleStatus === RentalSparepartStock::STATUS_ARCHIVED) {
            return;
        }

        if ($stockStatus !== '') {
            match ($stockStatus) {
                'HABIS' => $query->whereRaw('(qty_on_hand - qty_reserved) <= 0'),
                'RESERVED' => $query->where('qty_reserved', '>', 0),
                'MENIPIS' => $query->whereRaw('(qty_on_hand - qty_reserved) > 0')
                    ->whereHas('item', function ($itemQuery) {
                        $itemQuery->where('min_stock', '>', 0)
                            ->whereRaw('(rental_sparepart_stocks.qty_on_hand - rental_sparepart_stocks.qty_reserved) <= rental_sparepart_items.min_stock');
                    }),
                'AMAN' => $query->whereRaw('(qty_on_hand - qty_reserved) > 0'),
                default => null,
            };
        }
    }

    private function stockStatus(RentalSparepartStock $stock): string
    {
        if ($stock->stock_lifecycle_status === RentalSparepartStock::STATUS_ARCHIVED) {
            return 'ARCHIVED';
        }

        $available = (int) $stock->qty_available;
        $minStock = (int) ($stock->item?->min_stock ?? 0);

        if ($available <= 0) {
            return 'HABIS';
        }

        if ((int) $stock->qty_reserved > 0) {
            return 'RESERVED';
        }

        if ($minStock > 0 && $available <= $minStock) {
            return 'MENIPIS';
        }

        return 'AMAN';
    }

    private function problemFlags(RentalSparepartStock $stock): array
    {
        if ($stock->stock_lifecycle_status === RentalSparepartStock::STATUS_ARCHIVED) {
            return ['ARCHIVED'];
        }

        $flags = [];

        if ((int) $stock->qty_available <= 0) {
            $flags[] = 'STOK_HABIS';
        }

        $minStock = (int) ($stock->item?->min_stock ?? 0);
        if ($minStock > 0 && (int) $stock->qty_available > 0 && (int) $stock->qty_available <= $minStock) {
            $flags[] = 'STOK_MENIPIS';
        }

        if ((int) $stock->qty_reserved > 0) {
            $flags[] = 'RESERVED';
        }

        if (blank($stock->allocation_sn_unit) && blank($stock->source_sn_unit)) {
            $flags[] = 'TANPA_SN';
        }

        if (blank($stock->allocation_customer) && blank($stock->source_customer)) {
            $flags[] = 'TANPA_CUSTOMER';
        }

        if (blank($stock->allocation_location) && blank($stock->source_location)) {
            $flags[] = 'TANPA_LOKASI';
        }

        if ((int) $stock->qty_on_hand < (int) $stock->qty_reserved) {
            $flags[] = 'AVAILABLE_BELOW_RESERVED';
        }

        return $flags;
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
