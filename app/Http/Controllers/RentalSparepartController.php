<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartItem;
use App\Models\RentalSparepartLocation;
use App\Models\RentalSparepartMovement;
use App\Models\RentalSparepartStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RentalSparepartController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    private function role(): string
    {
        $user = Auth::user();

        return strtolower((string) ($user->status_user ?? $user->role ?? ''));
    }

    private function department(): string
    {
        return strtoupper(trim((string) (Auth::user()->department ?? '')));
    }

    private function canAccessRentalSparepart(): bool
    {
        return in_array($this->role(), ['admin', 'super_admin'], true) || $this->department() === self::DEPARTMENT;
    }

    private function canManageRentalSparepart(): bool
    {
        return in_array($this->role(), ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($this->role(), ['admin', 'super_admin'], true) || $this->department() === self::DEPARTMENT);
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
            ->where('department', self::DEPARTMENT);

        $this->applyFilters($baseQuery, $filters);

        $stocks = $baseQuery
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $summary = $this->summary();
        $filterOptions = $this->filterOptions();
        $canManageSparepart = $this->canManageRentalSparepart();

        return view('rental-spareparts.index', compact('stocks', 'summary', 'filterOptions', 'filters', 'canManageSparepart'));
    }

    public function createIn()
    {
        abort_unless($this->canManageRentalSparepart(), 403, 'Hanya koordinator/sect head RENTAL, admin, dan super admin yang bisa input barang masuk.');

        $locations = RentalSparepartLocation::query()
            ->where('department', self::DEPARTMENT)
            ->orderBy('location_name')
            ->get();

        return view('rental-spareparts.in-create', compact('locations'));
    }

    public function storeIn(Request $request)
    {
        abort_unless($this->canManageRentalSparepart(), 403, 'Hanya koordinator/sect head RENTAL, admin, dan super admin yang bisa input barang masuk.');

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'no_job' => 'nullable|string|max:150',
            'part_number' => 'required|string|max:150',
            'part_name' => 'required|string|max:255',
            'qty_masuk' => 'required|integer|min:1',
            'default_type_unit' => 'nullable|string|max:150',
            'min_stock' => 'nullable|integer|min:0',
            'location_code' => 'required|string|max:100',
            'location_name' => 'nullable|string|max:150',
            'cabinet' => 'nullable|string|max:100',
            'shelf' => 'nullable|string|max:100',
            'box' => 'nullable|string|max:100',
            'source_customer' => 'nullable|string|max:150',
            'source_type_unit' => 'nullable|string|max:150',
            'source_sn_unit' => 'nullable|string|max:150',
            'allocation_customer' => 'nullable|string|max:150',
            'allocation_type_unit' => 'nullable|string|max:150',
            'allocation_sn_unit' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
        ]);

        $user = Auth::user();
        $partNumber = strtoupper(trim($validated['part_number']));
        $locationCode = strtoupper(trim($validated['location_code']));
        $locationName = trim((string) ($validated['location_name'] ?? '')) ?: $locationCode;

        DB::transaction(function () use ($validated, $user, $partNumber, $locationCode, $locationName) {
            $item = RentalSparepartItem::query()->firstOrNew([
                'department' => self::DEPARTMENT,
                'part_number' => $partNumber,
            ]);

            $item->part_name = trim($validated['part_name']);
            $item->default_type_unit = $this->nullableUpper($validated['default_type_unit'] ?? null);
            $item->min_stock = (int) ($validated['min_stock'] ?? $item->min_stock ?? 0);
            $item->save();

            $location = RentalSparepartLocation::query()->firstOrNew([
                'department' => self::DEPARTMENT,
                'location_code' => $locationCode,
            ]);

            $location->location_name = $locationName;
            $location->cabinet = $this->nullableUpper($validated['cabinet'] ?? null);
            $location->shelf = $this->nullableUpper($validated['shelf'] ?? null);
            $location->box = $this->nullableUpper($validated['box'] ?? null);
            $location->save();

            $stock = RentalSparepartStock::query()->firstOrNew([
                'department' => self::DEPARTMENT,
                'sparepart_item_id' => $item->id,
                'location_id' => $location->id,
                'source_no_job' => $this->nullableUpper($validated['no_job'] ?? null),
                'source_customer' => $this->nullableUpper($validated['source_customer'] ?? null),
                'source_type_unit' => $this->nullableUpper($validated['source_type_unit'] ?? null),
                'source_sn_unit' => $this->nullableUpper($validated['source_sn_unit'] ?? null),
                'allocation_customer' => $this->nullableUpper($validated['allocation_customer'] ?? null),
                'allocation_type_unit' => $this->nullableUpper($validated['allocation_type_unit'] ?? null),
                'allocation_sn_unit' => $this->nullableUpper($validated['allocation_sn_unit'] ?? null),
            ]);

            $stock->qty_on_hand = (int) ($stock->qty_on_hand ?? 0) + (int) $validated['qty_masuk'];
            $stock->qty_reserved = (int) ($stock->qty_reserved ?? 0);
            $stock->remarks = $validated['remarks'] ?? $stock->remarks;
            $stock->save();

            RentalSparepartMovement::create([
                'department' => self::DEPARTMENT,
                'movement_type' => RentalSparepartMovement::TYPE_IN,
                'movement_date' => $validated['tanggal'],
                'sparepart_item_id' => $item->id,
                'sparepart_stock_id' => $stock->id,
                'from_location_id' => null,
                'to_location_id' => $location->id,
                'part_number_snapshot' => $item->part_number,
                'part_name_snapshot' => $item->part_name,
                'qty' => (int) $validated['qty_masuk'],
                'no_job' => $this->nullableUpper($validated['no_job'] ?? null),
                'source_customer' => $stock->source_customer,
                'source_type_unit' => $stock->source_type_unit,
                'source_sn_unit' => $stock->source_sn_unit,
                'allocation_customer' => $stock->allocation_customer,
                'allocation_type_unit' => $stock->allocation_type_unit,
                'allocation_sn_unit' => $stock->allocation_sn_unit,
                'actual_customer' => null,
                'actual_type_unit' => null,
                'actual_sn_unit' => null,
                'is_cross_allocation' => false,
                'pic_user_id' => $user->id,
                'pic_name' => $user->name,
                'remarks' => $validated['remarks'] ?? null,
            ]);
        });

        return redirect()->route('rental-spareparts.index')->with('success', 'Barang masuk sparepart rental berhasil disimpan.');
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
            ->where('department', self::DEPARTMENT)
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
            ->where('department', self::DEPARTMENT)
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

    private function nullableUpper(?string $value): ?string
    {
        $value = strtoupper(trim((string) $value));

        return $value !== '' ? $value : null;
    }
}
