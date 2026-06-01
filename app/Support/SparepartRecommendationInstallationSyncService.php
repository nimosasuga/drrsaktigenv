<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Support/SparepartRecommendationInstallationSyncService.php
|--------------------------------------------------------------------------
*/

namespace App\Support;

use App\Models\RentalSparepartUsageReview;
use App\Models\SparepartRecommendationControl;
use Illuminate\Support\Facades\DB;

class SparepartRecommendationInstallationSyncService
{
    public function syncFromApprovedUsageReview(RentalSparepartUsageReview $review): void
    {
        $review->loadMissing(['job', 'installPart']);

        $serialNumber = $this->normalizeNullable($review->actual_sn_unit ?: $review->job_serial_number ?: $review->job?->serial_number);
        $partNumber = $this->normalizeNullable($review->part_number ?: $review->installPart?->part_number);
        $department = $this->normalizeNullable($review->department);

        if (!$serialNumber || !$partNumber || !$department) {
            return;
        }

        $qtyInstalled = max(1, (int) ($review->qty_requested ?? 1));

        DB::transaction(function () use ($review, $serialNumber, $partNumber, $department, $qtyInstalled) {
            $controls = SparepartRecommendationControl::query()
                ->where('department', $department)
                ->whereRaw('UPPER(TRIM(COALESCE(serial_number, ""))) = ?', [$serialNumber])
                ->whereRaw('UPPER(TRIM(COALESCE(part_number, ""))) = ?', [$partNumber])
                ->whereNotIn('recommendation_status', [
                    SparepartRecommendationControl::STATUS_CLOSED,
                    SparepartRecommendationControl::STATUS_CANCELLED,
                    SparepartRecommendationControl::STATUS_REJECTED,
                ])
                ->orderByRaw("FIELD(recommendation_status, 'SUPPLIED', 'NEED_SUPPLY', 'APPROVED', 'REVIEWED', 'RECOMMENDED', 'PARTIAL_INSTALLED', 'INSTALLED')")
                ->orderBy('work_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($controls->isEmpty()) {
                return;
            }

            $remainingQty = $qtyInstalled;

            foreach ($controls as $control) {
                if ($remainingQty <= 0) {
                    break;
                }

                $targetQty = max(1, (int) $control->qty_recommended);
                $alreadyInstalled = max(0, (int) $control->qty_installed);
                $openQty = max(0, $targetQty - $alreadyInstalled);

                if ($openQty <= 0) {
                    continue;
                }

                $appliedQty = min($remainingQty, $openQty);
                $control->qty_installed = $alreadyInstalled + $appliedQty;
                $control->installed_job_id = $review->job_id;
                $control->installed_at = now();

                if ((int) $control->qty_supplied < (int) $control->qty_installed) {
                    $control->qty_supplied = (int) $control->qty_installed;
                }

                if ((int) $control->qty_installed >= $targetQty) {
                    $control->recommendation_status = SparepartRecommendationControl::STATUS_CLOSED;
                    $control->supply_status = SparepartRecommendationControl::SUPPLY_SUPPLIED;
                    $control->closed_at = now();
                } else {
                    $control->recommendation_status = SparepartRecommendationControl::STATUS_PARTIAL_INSTALLED;
                    $control->supply_status = (int) $control->qty_supplied >= $targetQty
                        ? SparepartRecommendationControl::SUPPLY_SUPPLIED
                        : SparepartRecommendationControl::SUPPLY_PARTIAL_SUPPLIED;
                }

                $note = 'Auto sync dari approved Usage Review #' . $review->id . ' / Job #' . $review->job_id . '. Qty installed +' . $appliedQty . '.';
                $control->review_note = trim((string) $control->review_note . "\n" . $note);
                $control->save();

                $remainingQty -= $appliedQty;
            }
        });
    }

    private function normalizeNullable(?string $value): ?string
    {
        $value = strtoupper(trim((string) $value));

        return $value !== '' ? $value : null;
    }
}
