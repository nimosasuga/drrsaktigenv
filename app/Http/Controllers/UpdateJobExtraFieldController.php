<?php
// PATH FILE: app/Http/Controllers/UpdateJobExtraFieldController.php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\UnitAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateJobExtraFieldController extends Controller
{
    public function asset(Request $request): JsonResponse
    {
        $serialNumber = trim((string) $request->query('serial_number', ''));

        if ($serialNumber === '') {
            return response()->json([
                'nomor_lambung' => '',
                'year' => '',
            ]);
        }

        $asset = UnitAsset::where('serial_number', $serialNumber)->first();

        return response()->json([
            'nomor_lambung' => $asset->nomor_lambung ?? '',
            'year' => $asset->year ?? '',
        ]);
    }

    public function job(int $id): JsonResponse
    {
        $job = Job::findOrFail($id);

        return response()->json([
            'nomor_lambung' => $job->nomor_lambung ?? '',
            'year' => $job->year ?? '',
        ]);
    }
}
