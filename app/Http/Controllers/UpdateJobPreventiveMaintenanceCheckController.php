<?php
// PATH FILE: app/Http/Controllers/UpdateJobPreventiveMaintenanceCheckController.php

namespace App\Http\Controllers;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateJobPreventiveMaintenanceCheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $serialNumber = trim((string) $request->query('serial_number', ''));
        $jobType = $this->normalizeJobType($request->query('job_type'));
        $workDateInput = trim((string) $request->query('work_date', ''));
        $exceptJobId = (int) $request->query('except_job_id', 0);

        if ($serialNumber === '' || $jobType !== 'Preventive Maintenance' || $workDateInput === '') {
            return response()->json([
                'blocked' => false,
                'message' => null,
            ]);
        }

        try {
            $workDate = Carbon::parse($workDateInput);
        } catch (\Throwable $e) {
            return response()->json([
                'blocked' => false,
                'message' => null,
            ]);
        }

        $query = Job::query()
            ->where('serial_number', $serialNumber)
            ->where('job_type', 'Preventive Maintenance')
            ->whereYear('work_date', $workDate->year)
            ->whereMonth('work_date', $workDate->month);

        if ($exceptJobId > 0) {
            $query->where('id', '!=', $exceptJobId);
        }

        $existingJob = $query
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->first();

        if (!$existingJob) {
            return response()->json([
                'blocked' => false,
                'message' => null,
            ]);
        }

        $monthLabel = $workDate->translatedFormat('F Y');
        $existingDate = $existingJob->work_date
            ? Carbon::parse($existingJob->work_date)->format('d/m/Y')
            : '-';

        return response()->json([
            'blocked' => true,
            'message' => 'Preventive Maintenance untuk S/N ' . $serialNumber . ' sudah pernah dibuat pada bulan ' . $monthLabel . '. Data sebelumnya tanggal ' . $existingDate . '. Satu unit hanya boleh 1x Preventive Maintenance dalam bulan yang sama.',
            'existing_job' => [
                'id' => $existingJob->id,
                'work_date' => $existingDate,
                'pic' => $existingJob->pic ?: '-',
            ],
        ]);
    }

    private function normalizeJobType(?string $value): ?string
    {
        $value = trim((string) $value);

        return match (strtoupper($value)) {
            'PM' => 'Preventive Maintenance',
            'BM' => 'Troubleshooting',
            'PDI' => 'Inspection',
            default => $value !== '' ? $value : null,
        };
    }
}
