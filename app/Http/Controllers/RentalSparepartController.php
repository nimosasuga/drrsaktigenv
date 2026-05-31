<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartController extends Controller
{
    private function canAccessRentalSparepart(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['admin', 'super_admin'], true) || $department === 'RENTAL';
    }

    public function index(Request $request)
    {
        abort_unless($this->canAccessRentalSparepart(), 403, 'Modul sparepart rental hanya untuk department RENTAL, admin, dan super admin.');

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'location_id' => $request->input('location_id'),
            'customer' => trim((string) $request->input('customer', '')),
            'sn_unit' => trim((string) $request->input('sn_unit', '')),
            'no_job' => trim((string) $request->input('no_job', '')),
            'stock_status' => trim((string) $request->input('stock_status', '')),
        ];

        $baseQuery = RentalSparepartStock::query()
            ->with(['item', 'location'])
            ->where('department', 'RENTAL');

        $this->applyFilters($baseQuery, $filters);

        $stocks = $baseQuery
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $summary = $this->summary();
        $filterOptions = $this->filterOptions();

        return view('rental-spareparts.index', compact('stocks', 'summary', 'filterOptions', 'filters'));
    }

    private function applyFilters($query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('source_no_job', 'like', "%{$search}%")
                    ->orWhere('source_customer', 'like', "%{$search}%")
                    ->orWhere('source_sn_unit', 'like', "%{$search}%")
                    ->orWhere('allocation_customer', 'like', "%{$search}%")
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

        if ($filters['location_id']) {
            $query->where('location_id', $filters['location_id']);
        }

        if ($filters['customer'] !== '') {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('source_customer', $filters['customer'])
                    ->orWhere('allocation_customer', $filters['customer']);
            });
        }

        if ($filters['sn_unit'] !== '') {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('source_sn_unit', $filters['sn_unit'])
                    ->orWhere('allocation_sn_unit', $filters['sn_unit']);
            });
        }

        if ($filters['no_job'] !== '') {
            $query->where('source_no_job', $filters['no_job']);
        }

        if ($filters['stock_status'] !== '') {
            match ($filters['stock_status']) {
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

    private function summary(): array
    {
        $stocks = RentalSparepartStock::with('item')
            ->where('department', 'RENTAL')
            ->get();

        return [
            'total_part' => $stocks->pluck('sparepart_item_id')->filter()->unique()->count(),
            'total_stock_row' => $stocks->count(),
            'qty_on_hand' => $stocks->sum('qty_on_hand'),
            'qty_reserved' => $stocks->sum('qty_reserved'),
            'qty_available' => $stocks->sum(fn ($stock) => $stock->qty_available),
            'qty_empty' => $stocks->filter(fn ($stock) => $stock->qty_available <= 0)->count(),
            'qty_low' => $stocks->filter(function ($stock) {
                $minStock = (int) ($stock->item->min_stock ?? 0);
                return $minStock > 0 && $stock->qty_available > 0 && $stock->qty_available <= $minStock;
            })->count(),
        ];
    }

    private function filterOptions(): array
    {
        $stocks = RentalSparepartStock::with('location')
            ->where('department', 'RENTAL')
            ->get();

        $customers = $stocks
            ->flatMap(fn ($stock) => [$stock->source_customer, $stock->allocation_customer])
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $snUnits = $stocks
            ->flatMap(fn ($stock) => [$stock->source_sn_unit, $stock->allocation_sn_unit])
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $noJobs = $stocks
            ->pluck('source_no_job')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $locations = $stocks
            ->pluck('location')
            ->filter()
            ->unique('id')
            ->sortBy('location_name')
            ->values();

        return compact('customers', 'snUnits', 'noJobs', 'locations');
    }
}
