<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartStockController.php
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

class RentalSparepartStockController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function edit(RentalSparepartStock $stock)
    {
        abort_unless($this->canManage(), 403);
        abort_if($stock->department !== self::DEPARTMENT, 404);

        $stock->load(['item', 'location']);

        return view('rental-spareparts.stocks.edit', compact('stock'));
    }

    public function update(Request $request, RentalSparepartStock $stock)
    {
        abort_unless($this->canManage(), 403);
        abort_if($stock->department !== self::DEPARTMENT, 404);

        $validated = $request->validate([
            'part_number' => ['required', 'string', 'max:150'],
            'part_name' => ['required', 'string', 'max:255'],
            'default_type_unit' => ['nullable', 'string', 'max:150'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'qty_on_hand' => ['required', 'integer', 'min:0'],
            'location_code' => ['required', 'string', 'max:100'],
            'location_name' => ['nullable', 'string', 'max:150'],
            'cabinet' => ['nullable', 'string', 'max:100'],
            'shelf' => ['nullable', 'string', 'max:100'],
            'box' => ['nullable', 'string', 'max:100'],
            'source_no_job' => ['nullable', 'string', 'max:150'],
            'source_customer' => ['nullable', 'string', 'max:150'],
            'source_location' => ['nullable', 'string', 'max:150'],
            'source_type_unit' => ['nullable', 'string', 'max:150'],
            'source_sn_unit' => ['nullable', 'string', 'max:150'],
            'allocation_customer' => ['nullable', 'string', 'max:150'],
            'allocation_location' => ['nullable', 'string', 'max:150'],
            'allocation_type_unit' => ['nullable', 'string', 'max:150'],
            'allocation_sn_unit' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ((int) $validated['qty_on_hand'] < (int) $stock->qty_reserved) {
            return back()
                ->withInput()
                ->withErrors(['qty_on_hand' => 'Qty on hand tidak boleh lebih kecil dari qty reserved.']);
        }

        DB::transaction(function () use ($validated, $stock) {
            $user = Auth::user();
            $oldQty = (int) $stock->qty_on_hand;
            $newQty = (int) $validated['qty_on_hand'];

            $item = RentalSparepartItem::query()->firstOrNew([
                'department' => self::DEPARTMENT,
                'part_number' => $this->upper($validated['part_number']),
            ]);
            $item->part_name = trim($validated['part_name']);
            $item->default_type_unit = $this->nullableUpper($validated['default_type_unit'] ?? null);
            $item->min_stock = (int) ($validated['min_stock'] ?? 0);
            $item->save();

            $locationCode = $this->upper($validated['location_code']);
            $location = RentalSparepartLocation::query()->firstOrNew([
                'department' => self::DEPARTMENT,
                'location_code' => $locationCode,
            ]);
            $location->location_name = trim((string) ($validated['location_name'] ?? '')) ?: $locationCode;
            $location->cabinet = $this->nullableUpper($validated['cabinet'] ?? null);
            $location->shelf = $this->nullableUpper($validated['shelf'] ?? null);
            $location->box = $this->nullableUpper($validated['box'] ?? null);
            $location->save();

            $sourceNoJob = $this->nullableUpper($validated['source_no_job'] ?? null);
            $sourceCustomer = $this->nullableUpper($validated['source_customer'] ?? null);
            $sourceLocation = $this->nullableUpper($validated['source_location'] ?? null);
            $sourceTypeUnit = $this->nullableUpper($validated['source_type_unit'] ?? null);
            $sourceSnUnit = $this->nullableUpper($validated['source_sn_unit'] ?? null);
            $allocationCustomer = $this->nullableUpper($validated['allocation_customer'] ?? null);
            $allocationLocation = $this->nullableUpper($validated['allocation_location'] ?? null);
            $allocationTypeUnit = $this->nullableUpper($validated['allocation_type_unit'] ?? null);
            $allocationSnUnit = $this->nullableUpper($validated['allocation_sn_unit'] ?? null);

            $duplicateExists = RentalSparepartStock::query()
                ->where('department', self::DEPARTMENT)
                ->where('sparepart_item_id', $item->id)
                ->where('location_id', $location->id)
                ->where('source_no_job', $sourceNoJob)
                ->where('source_customer', $sourceCustomer)
                ->where('source_location', $sourceLocation)
                ->where('source_type_unit', $sourceTypeUnit)
                ->where('source_sn_unit', $sourceSnUnit)
                ->where('allocation_customer', $allocationCustomer)
                ->where('allocation_location', $allocationLocation)
                ->where('allocation_type_unit', $allocationTypeUnit)
                ->where('allocation_sn_unit', $allocationSnUnit)
                ->where('id', '!=', $stock->id)
                ->exists();

            if ($duplicateExists) {
                abort(422, 'Edit dibatalkan: kombinasi part, lokasi, source, dan alokasi sudah dimiliki baris stok lain.');
            }

            $stock->sparepart_item_id = $item->id;
            $stock->location_id = $location->id;
            $stock->qty_on_hand = $newQty;
            $stock->source_no_job = $sourceNoJob;
            $stock->source_customer = $sourceCustomer;
            $stock->source_location = $sourceLocation;
            $stock->source_type_unit = $sourceTypeUnit;
            $stock->source_sn_unit = $sourceSnUnit;
            $stock->allocation_customer = $allocationCustomer;
            $stock->allocation_location = $allocationLocation;
            $stock->allocation_type_unit = $allocationTypeUnit;
            $stock->allocation_sn_unit = $allocationSnUnit;
            $stock->remarks = $validated['remarks'] ?? null;
            $stock->save();

            if ($newQty !== $oldQty) {
                RentalSparepartMovement::create([
                    'department' => self::DEPARTMENT,
                    'movement_type' => RentalSparepartMovement::TYPE_ADJUSTMENT,
                    'movement_date' => now()->toDateString(),
                    'sparepart_item_id' => $item->id,
                    'sparepart_stock_id' => $stock->id,
                    'from_location_id' => $newQty < $oldQty ? $location->id : null,
                    'to_location_id' => $newQty > $oldQty ? $location->id : null,
                    'part_number_snapshot' => $item->part_number,
                    'part_name_snapshot' => $item->part_name,
                    'qty' => abs($newQty - $oldQty),
                    'no_job' => $sourceNoJob,
                    'source_customer' => $sourceCustomer,
                    'source_location' => $sourceLocation,
                    'source_type_unit' => $sourceTypeUnit,
                    'source_sn_unit' => $sourceSnUnit,
                    'allocation_customer' => $allocationCustomer,
                    'allocation_location' => $allocationLocation,
                    'allocation_type_unit' => $allocationTypeUnit,
                    'allocation_sn_unit' => $allocationSnUnit,
                    'pic_user_id' => $user->id,
                    'pic_name' => $user->name,
                    'remarks' => 'EDIT STOCK ADJUSTMENT: ' . $oldQty . ' -> ' . $newQty,
                ]);
            }
        });

        return redirect()->route('rental-spareparts.index')->with('success', 'Stok sparepart berhasil diperbarui.');
    }

    public function destroy(RentalSparepartStock $stock)
    {
        abort_unless($this->canManage(), 403);
        abort_if($stock->department !== self::DEPARTMENT, 404);

        if ((int) $stock->qty_reserved > 0) {
            return back()->withErrors(['delete' => 'Stok tidak bisa dihapus karena masih memiliki qty reserved.']);
        }

        DB::transaction(function () use ($stock) {
            $user = Auth::user();
            $stock->load(['item', 'location']);

            if ((int) $stock->qty_on_hand > 0) {
                RentalSparepartMovement::create([
                    'department' => self::DEPARTMENT,
                    'movement_type' => RentalSparepartMovement::TYPE_ADJUSTMENT,
                    'movement_date' => now()->toDateString(),
                    'sparepart_item_id' => $stock->sparepart_item_id,
                    'sparepart_stock_id' => $stock->id,
                    'from_location_id' => $stock->location_id,
                    'to_location_id' => null,
                    'part_number_snapshot' => $stock->item?->part_number,
                    'part_name_snapshot' => $stock->item?->part_name,
                    'qty' => (int) $stock->qty_on_hand,
                    'no_job' => $stock->source_no_job,
                    'source_customer' => $stock->source_customer,
                    'source_location' => $stock->source_location,
                    'source_type_unit' => $stock->source_type_unit,
                    'source_sn_unit' => $stock->source_sn_unit,
                    'allocation_customer' => $stock->allocation_customer,
                    'allocation_location' => $stock->allocation_location,
                    'allocation_type_unit' => $stock->allocation_type_unit,
                    'allocation_sn_unit' => $stock->allocation_sn_unit,
                    'pic_user_id' => $user->id,
                    'pic_name' => $user->name,
                    'remarks' => 'DELETE STOCK ADJUSTMENT: stock row deleted from Management Sparepart.',
                ]);
            }

            $stock->delete();
        });

        return redirect()->route('rental-spareparts.index')->with('success', 'Stok sparepart berhasil dihapus.');
    }

    private function canManage(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT);
    }

    private function upper(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function nullableUpper(?string $value): ?string
    {
        $value = $this->upper($value);

        return $value !== '' ? $value : null;
    }
}
