<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartOutController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartMovement;
use App\Models\RentalSparepartStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RentalSparepartOutController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function create(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $stocks = RentalSparepartStock::query()
            ->with(['item', 'location'])
            ->where('department', self::DEPARTMENT)
            ->whereRaw('(qty_on_hand - qty_reserved) > 0')
            ->orderByDesc('updated_at')
            ->get();

        $selectedStock = null;

        if ($request->filled('stock_id')) {
            $selectedStock = $stocks->firstWhere('id', (int) $request->input('stock_id'));
        }

        return view('rental-spareparts.out-create', compact('stocks', 'selectedStock'));
    }

    public function store(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'stock_id' => 'required|integer|exists:rental_sparepart_stocks,id',
            'qty_keluar' => 'required|integer|min:1',
            'no_job' => 'nullable|string|max:150',
            'actual_customer' => 'nullable|string|max:150',
            'actual_location' => 'nullable|string|max:150',
            'actual_type_unit' => 'nullable|string|max:150',
            'actual_sn_unit' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
        ]);

        $errorMessage = null;
        $user = Auth::user();

        DB::transaction(function () use ($validated, $user, &$errorMessage) {
            $stock = RentalSparepartStock::query()
                ->with(['item', 'location'])
                ->where('department', self::DEPARTMENT)
                ->whereKey($validated['stock_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $errorMessage = 'Stok tidak ditemukan.';
                return;
            }

            $qtyKeluar = (int) $validated['qty_keluar'];
            $qtyAvailable = max(0, (int) $stock->qty_on_hand - (int) $stock->qty_reserved);

            if ($qtyKeluar > $qtyAvailable) {
                $errorMessage = "Qty keluar melebihi stok tersedia. Sisa tersedia: {$qtyAvailable}.";
                return;
            }

            $actualSn = $this->nullableUpper($validated['actual_sn_unit'] ?? null);
            $allocationSn = $this->nullableUpper($stock->allocation_sn_unit ?: $stock->source_sn_unit);
            $isCrossAllocation = $actualSn && $allocationSn && $actualSn !== $allocationSn;

            $stock->qty_on_hand = (int) $stock->qty_on_hand - $qtyKeluar;
            $stock->save();

            RentalSparepartMovement::create([
                'department' => self::DEPARTMENT,
                'movement_type' => RentalSparepartMovement::TYPE_OUT,
                'movement_date' => $validated['tanggal'],
                'sparepart_item_id' => $stock->sparepart_item_id,
                'sparepart_stock_id' => $stock->id,
                'from_location_id' => $stock->location_id,
                'to_location_id' => null,
                'part_number_snapshot' => $stock->item?->part_number,
                'part_name_snapshot' => $stock->item?->part_name,
                'qty' => $qtyKeluar,
                'no_job' => $this->nullableUpper($validated['no_job'] ?? $stock->source_no_job),
                'source_customer' => $stock->source_customer,
                'source_location' => $stock->source_location,
                'source_type_unit' => $stock->source_type_unit,
                'source_sn_unit' => $stock->source_sn_unit,
                'allocation_customer' => $stock->allocation_customer,
                'allocation_location' => $stock->allocation_location,
                'allocation_type_unit' => $stock->allocation_type_unit,
                'allocation_sn_unit' => $stock->allocation_sn_unit,
                'actual_customer' => $this->nullableUpper($validated['actual_customer'] ?? null),
                'actual_location' => $this->nullableUpper($validated['actual_location'] ?? null),
                'actual_type_unit' => $this->nullableUpper($validated['actual_type_unit'] ?? null),
                'actual_sn_unit' => $actualSn,
                'is_cross_allocation' => $isCrossAllocation,
                'pic_user_id' => $user->id,
                'pic_name' => $user->name,
                'remarks' => $validated['remarks'] ?? null,
            ]);
        });

        if ($errorMessage) {
            return back()->withInput()->withErrors(['qty_keluar' => $errorMessage]);
        }

        return redirect()->route('rental-spareparts.index')->with('success', 'Barang keluar sparepart rental berhasil disimpan.');
    }

    private function canManage(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT);
    }

    private function nullableUpper(?string $value): ?string
    {
        $value = strtoupper(trim((string) $value));

        return $value !== '' ? $value : null;
    }
}
