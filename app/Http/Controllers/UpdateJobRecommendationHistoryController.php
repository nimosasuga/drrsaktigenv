<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/UpdateJobRecommendationHistoryController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\SparepartRecommendationControl;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UpdateJobRecommendationHistoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $serialNumber = strtoupper(trim((string) $request->get('serial_number', '')));

        if ($serialNumber === '') {
            return response()->json([]);
        }

        $controls = SparepartRecommendationControl::query()
            ->whereRaw('UPPER(TRIM(COALESCE(serial_number, ""))) = ?', [$serialNumber])
            ->orderByRaw("FIELD(recommendation_status, 'RECOMMENDED', 'REVIEWED', 'APPROVED', 'NEED_SUPPLY', 'SUPPLIED', 'PARTIAL_INSTALLED', 'INSTALLED', 'CLOSED', 'REJECTED', 'CANCELLED')")
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->take(30)
            ->get();

        if ($controls->isNotEmpty()) {
            return response()->json($controls->map(function (SparepartRecommendationControl $control) {
                return [
                    'source' => 'control',
                    'date' => $control->work_date ? Carbon::parse($control->work_date)->format('d/m/Y') : '-',
                    'part_number' => $control->part_number ?: '-',
                    'part_name' => $control->part_name ?: '-',
                    'qty' => $control->qty_recommended ?: 1,
                    'qty_supplied' => $control->qty_supplied ?: 0,
                    'qty_installed' => $control->qty_installed ?: 0,
                    'recommendation_status' => $control->recommendation_status ?: 'RECOMMENDED',
                    'supply_status' => $control->supply_status ?: 'NOT_SUPPLIED',
                    'recommended_by_name' => $control->recommended_by_name ?: '-',
                    'review_note' => $control->review_note ?: null,
                    'supply_note' => $control->supply_note ?: null,
                    'is_cross_allocation' => (bool) $control->is_cross_allocation,
                    'control_url' => route('sparepart-recommendations.index', [
                        'serial_number' => $control->serial_number,
                        'part_number' => $control->part_number,
                    ]),
                ];
            })->values());
        }

        $jobs = Job::with('recommendations')
            ->whereRaw('UPPER(TRIM(COALESCE(serial_number, ""))) = ?', [$serialNumber])
            ->whereHas('recommendations')
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->take(15)
            ->get();

        $history = $jobs
            ->flatMap(function ($job) {
                return $job->recommendations->map(function ($recommendation) use ($job) {
                    return [
                        'source' => 'legacy',
                        'date' => $job->work_date ? Carbon::parse($job->work_date)->format('d/m/Y') : '-',
                        'part_number' => $recommendation->part_number ?: '-',
                        'part_name' => $recommendation->part_name ?: '-',
                        'qty' => $recommendation->qty ?: 1,
                        'qty_supplied' => 0,
                        'qty_installed' => 0,
                        'recommendation_status' => 'LEGACY',
                        'supply_status' => 'UNKNOWN',
                        'recommended_by_name' => $job->pic ?: '-',
                        'review_note' => null,
                        'supply_note' => null,
                        'is_cross_allocation' => false,
                        'control_url' => route('sparepart-recommendations.index', [
                            'serial_number' => $job->serial_number,
                            'part_number' => $recommendation->part_number,
                        ]),
                    ];
                });
            })
            ->take(30)
            ->values();

        return response()->json($history);
    }
}
