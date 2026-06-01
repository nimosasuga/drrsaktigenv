<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Support/SparepartRecommendationControlService.php
|--------------------------------------------------------------------------
*/

namespace App\Support;

use App\Models\JobRecommendation;
use App\Models\SparepartRecommendationControl;
use Illuminate\Support\Facades\DB;

class SparepartRecommendationControlService
{
    public function createFromJobRecommendation(JobRecommendation $recommendation): void
    {
        $recommendation->loadMissing('job.user');
        $job = $recommendation->job;

        if (!$job) {
            return;
        }

        $department = $this->normalize($job->department ?? '');

        if ($department === '') {
            return;
        }

        $partNumber = $this->normalizeNullable($recommendation->part_number ?? null);
        $serialNumber = $this->normalizeNullable($job->serial_number ?? null);

        if ($partNumber === null && trim((string) ($recommendation->part_name ?? '')) === '') {
            return;
        }

        DB::transaction(function () use ($recommendation, $job, $department, $partNumber, $serialNumber) {
            $exists = SparepartRecommendationControl::query()
                ->where('job_recommendation_id', $recommendation->id)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return;
            }

            SparepartRecommendationControl::create([
                'department' => $department,
                'job_id' => $job->id,
                'job_recommendation_id' => $recommendation->id,
                'work_date' => $job->work_date,
                'serial_number' => $serialNumber,
                'customer' => $job->customer,
                'location' => $job->location,
                'unit_type' => $job->unit_type,
                'part_number' => $partNumber,
                'part_name' => $recommendation->part_name,
                'qty_recommended' => max(1, (int) ($recommendation->qty ?? 1)),
                'qty_supplied' => 0,
                'qty_installed' => 0,
                'recommendation_status' => SparepartRecommendationControl::STATUS_RECOMMENDED,
                'supply_status' => SparepartRecommendationControl::SUPPLY_NOT_SUPPLIED,
                'recommended_by' => $job->user_id,
                'recommended_by_name' => $job->pic ?: $job->user?->name,
                'remarks' => $recommendation->remarks,
            ]);
        });
    }

    public function cancelFromJobRecommendation(JobRecommendation $recommendation): void
    {
        DB::transaction(function () use ($recommendation) {
            $control = SparepartRecommendationControl::query()
                ->where('job_recommendation_id', $recommendation->id)
                ->lockForUpdate()
                ->first();

            if (!$control || $control->isClosed()) {
                return;
            }

            if ((int) $control->qty_supplied > 0 || (int) $control->qty_installed > 0) {
                $control->review_note = trim((string) $control->review_note . "\nRekomendasi sumber di Update Job sudah dihapus/diedit, tetapi control tidak dibatalkan karena sudah ada supply/install.");
                $control->save();
                return;
            }

            $control->recommendation_status = SparepartRecommendationControl::STATUS_CANCELLED;
            $control->supply_status = SparepartRecommendationControl::SUPPLY_NOT_REQUIRED;
            $control->closed_at = now();
            $control->review_note = trim((string) $control->review_note . "\nOtomatis dibatalkan karena rekomendasi sumber di Update Job dihapus/diedit sebelum supply/install.");
            $control->save();
        });
    }

    private function normalize(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function normalizeNullable(?string $value): ?string
    {
        $value = $this->normalize($value);

        return $value !== '' ? $value : null;
    }
}
