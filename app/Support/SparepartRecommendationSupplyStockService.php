<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Support/SparepartRecommendationSupplyStockService.php
|--------------------------------------------------------------------------
*/

namespace App\Support;

use App\Models\RentalSparepartItem;
use App\Models\RentalSparepartLocation;
use App\Models\RentalSparepartMovement;
use App\Models\RentalSparepartStock;
use App\Models\SparepartRecommendationControl;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SparepartRecommendationSupplyStockService
{
    public function createStockInFromRecommendation(SparepartRecommendationControl $control, array $payload, User $user): RentalSparepartStock
    {
        return DB::transaction(function () use ($control, $payload, $user) {
            $department = $this->upper($control->department ?: 'RENTAL');
            $partNumber = $this->upper($control->part_number);
            $partName = trim((string) ($control->part_name ?: $partNumber));
            $qty = max(1, (int) ($payload['qty_supplied'] ?? 1));
            $locationCode = $this->upper($payload['location_code'] ?? 'RECOMMENDATION-SUPPLY');
            $locationName = trim((string) ($payload['location_name'] ?? '')) ?: $locationCode;
            $noJob = $this->nullableUpper($payload['supply_no_job'] ?? null);
            $movementDate = $payload['supply_date'] ?? now()->toDateString();
            $note = trim((string) ($payload['note'] ?? ''));

            $item = RentalSparepartItem::query()->firstOrNew([
                'department' => $department,
                'part_number' => $partNumber,
            ]);
            $item->part_name = $partName;
            $item->default_type_unit = $this->nullableUpper($control->unit_type) ?: $item->default_type_unit;
            $item->min_stock = (int) ($item->min_stock ?? 0);
            $item->save();

            $location = RentalSparepartLocation::query()->firstOrNew([
                'department' => $department,
                'location_code' => $locationCode,
            ]);
            $location->location_name = $locationName;
            $location->cabinet = $this->nullableUpper($payload['cabinet'] ?? null);
            $location->shelf = $this->nullableUpper($payload['shelf'] ?? null);
            $location->box = $this->nullableUpper($payload['box'] ?? null);
            $location->save();

            $sourceCustomer = $this->nullableUpper($control->customer);
            $sourceLocation = $this->nullableUpper($control->location);
            $sourceTypeUnit = $this->nullableUpper($control->unit_type);
            $sourceSnUnit = $this->nullableUpper($control->serial_number);

            $stock = RentalSparepartStock::query()->firstOrNew([
                'department' => $department,
                'stock_lifecycle_status' => RentalSparepartStock::STATUS_ACTIVE,
                'sparepart_item_id' => $item->id,
                'location_id' => $location->id,
                'source_no_job' => $noJob,
                'source_customer' => $sourceCustomer,
                'source_location' => $sourceLocation,
                'source_type_unit' => $sourceTypeUnit,
                'source_sn_unit' => $sourceSnUnit,
                'allocation_customer' => $sourceCustomer,
                'allocation_location' => $sourceLocation,
                'allocation_type_unit' => $sourceTypeUnit,
                'allocation_sn_unit' => $sourceSnUnit,
            ]);

            $stock->stock_lifecycle_status = RentalSparepartStock::STATUS_ACTIVE;
            $stock->qty_on_hand = (int) ($stock->qty_on_hand ?? 0) + $qty;
            $stock->qty_reserved = (int) ($stock->qty_reserved ?? 0);
            $stock->remarks = trim('SUPPLY FROM RECOMMENDATION CONTROL #' . $control->id . '. ' . $note);
            $stock->save();

            RentalSparepartMovement::create([
                'department' => $department,
                'movement_type' => RentalSparepartMovement::TYPE_IN,
                'movement_date' => $movementDate,
                'sparepart_item_id' => $item->id,
                'sparepart_stock_id' => $stock->id,
                'from_location_id' => null,
                'to_location_id' => $location->id,
                'part_number_snapshot' => $item->part_number,
                'part_name_snapshot' => $item->part_name,
                'qty' => $qty,
                'no_job' => $noJob,
                'source_customer' => $sourceCustomer,
                'source_location' => $sourceLocation,
                'source_type_unit' => $sourceTypeUnit,
                'source_sn_unit' => $sourceSnUnit,
                'allocation_customer' => $sourceCustomer,
                'allocation_location' => $sourceLocation,
                'allocation_type_unit' => $sourceTypeUnit,
                'allocation_sn_unit' => $sourceSnUnit,
                'actual_customer' => $sourceCustomer,
                'actual_location' => $sourceLocation,
                'actual_type_unit' => $sourceTypeUnit,
                'actual_sn_unit' => $sourceSnUnit,
                'is_cross_allocation' => false,
                'pic_user_id' => $user->id,
                'pic_name' => $user->name,
                'remarks' => trim('SUPPLY FROM RECOMMENDATION CONTROL #' . $control->id . '. ' . $note),
            ]);

            return $stock;
        });
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
