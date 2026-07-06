<?php
// PATH FILE: app/Http/Controllers/UpdateJobAssetSearchController.php

namespace App\Http\Controllers;

use App\Models\Job;
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
                $query->whereRaw(UnitAsset::activeStatusSql());
            })
            ->take(10)
            ->get();

        return response()->json($assets->map(function ($asset) use ($columns) {
            $status = in_array('status', $columns, true) ? ($asset->status ?? '') : '';
            $isWithdrawn = in_array(strtoupper(trim((string) $status)), UnitAsset::inactiveStatusValues(), true);
            $openProblemJob = $this->latestOpenProblemJob((string) ($asset->serial_number ?? ''));

            return [
                'serial_number' => $asset->serial_number ?? '',
                'unit_type' => $asset->unit_type ?? '',
                'customer' => $asset->customer ?? '',
                'location' => $asset->location ?? '',
                'status' => $status,
                'is_withdrawn' => $isWithdrawn,
                'blocked_reason' => $isWithdrawn ? 'Serial Number ini tidak bisa digunakan karena status unit asset tidak aktif.' : null,
                'open_problem_date' => $openProblemJob?->problem_date?->format('Y-m-d'),
                'open_status_unit' => $openProblemJob?->status_unit,
            ];
        })->values());
    }

    private function latestOpenProblemJob(string $serialNumber): ?Job
    {
        if ($serialNumber === '') {
            return null;
        }

        return Job::where('serial_number', $serialNumber)
            ->whereIn('status_unit', ['Breakdown', 'BREAKDOWN', 'B/D', 'BD', 'Monitoring', 'MONITORING', 'Standby', 'STANDBY'])
            ->whereNotNull('problem_date')
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->first();
    }
}
