<?php
// PATH FILE: app/Http/Controllers/UpdateJobExtraFieldController.php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\UnitAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
            'nomor_lambung' => $this->assetValue($asset, 'nomor_lambung'),
            'year' => $this->assetValue($asset, 'year'),
        ]);
    }

    public function job(int $id): JsonResponse
    {
        $job = Job::findOrFail($id);
        $asset = UnitAsset::where('serial_number', $job->serial_number)->first();

        $nomorLambung = trim((string) ($job->nomor_lambung ?? ''));
        $year = trim((string) ($job->year ?? ''));

        if ($nomorLambung === '') {
            $nomorLambung = $this->assetValue($asset, 'nomor_lambung');
        }

        if ($year === '') {
            $year = $this->assetValue($asset, 'year');
        }

        return response()->json([
            'nomor_lambung' => $nomorLambung,
            'year' => $year,
        ]);
    }

    private function assetValue(?UnitAsset $asset, string $column): string
    {
        if (!$asset || !Schema::hasColumn('unit_assets', $column)) {
            return '';
        }

        return trim((string) ($asset->{$column} ?? ''));
    }
}
