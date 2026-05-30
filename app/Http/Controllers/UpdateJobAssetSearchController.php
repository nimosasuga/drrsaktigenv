<?php
// PATH FILE: app/Http/Controllers/UpdateJobAssetSearchController.php

namespace App\Http\Controllers;

use App\Models\UnitAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UpdateJobAssetSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $includeWithdrawn = $request->boolean('include_withdrawn');

        if ($search === '') {
            return response()->json([]);
        }

        $columns = Schema::getColumnListing('unit_assets');

        $assets = UnitAsset::query()
            ->where(function ($query) use ($search, $columns) {
                $query->where('serial_number', 'LIKE', "%{$search}%");

                foreach (['unit_type', 'customer', 'location'] as $column) {
                    if (in_array($column, $columns, true)) {
                        $query->orWhere($column, 'LIKE', "%{$search}%");
                    }
                }
            })
            ->when(!$includeWithdrawn && in_array('status', $columns, true), function ($query) {
                $query->whereRaw("UPPER(TRIM(COALESCE(status, ''))) <> 'DITARIK'");
            })
            ->take(10)
            ->get();

        return response()->json($assets->map(function ($asset) use ($columns) {
            $status = in_array('status', $columns, true) ? ($asset->status ?? '') : '';
            $isWithdrawn = strtoupper(trim((string) $status)) === 'DITARIK';

            return [
                'serial_number' => $asset->serial_number ?? '',
                'unit_type' => $asset->unit_type ?? '',
                'customer' => $asset->customer ?? '',
                'location' => $asset->location ?? '',
                'status' => $status,
                'is_withdrawn' => $isWithdrawn,
                'blocked_reason' => $isWithdrawn ? 'Serial Number ini tidak bisa digunakan karena status unit asset sudah DITARIK.' : null,
            ];
        })->values());
    }
}
