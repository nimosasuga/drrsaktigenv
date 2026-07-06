<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Support/RentalSparepartUsageReviewService.php
|--------------------------------------------------------------------------
*/

namespace App\Support;

use App\Models\JobInstallPart;
use App\Models\RentalSparepartStock;
use App\Models\RentalSparepartUsageReview;
use Illuminate\Support\Facades\DB;

class RentalSparepartUsageReviewService
{
    private const DEPARTMENT = 'RENTAL';

    public function createFromJobInstallPart(JobInstallPart $installPart): void
    {
        $installPart->loadMissing('job');
        $job = $installPart->job;

        if (!$job || $this->normalize($job->department ?? '') !== self::DEPARTMENT) {
            return;
        }

        $partNumber = $this->normalize($installPart->part_number ?? '');

        if ($partNumber === '') {
            return;
        }

        if (RentalSparepartUsageReview::where('job_install_part_id', $installPart->id)->exists()) {
            return;
        }

        DB::transaction(function () use ($installPart, $job, $partNumber) {
            $qty = max(1, (int) ($installPart->qty ?? 1));
            $noJob = $this->normalizeNullable($installPart->no_job ?? null);
            $actualSn = $this->normalizeNullable($job->serial_number ?? null);

            $match = $this->findBestStock($partNumber, $noJob, $actualSn, $qty);
            $stock = $match['stock'];
            $matchType = $match['match_type'];
            $reviewStatus = $match['review_status'];
            $isBorrowed = $match['is_borrowed'];
            $borrowReason = $match['borrow_reason'];

            if ($stock && $reviewStatus === RentalSparepartUsageReview::STATUS_PENDING_REVIEW) {
                $stock = RentalSparepartStock::query()
                    ->where('department', self::DEPARTMENT)
                    ->where('stock_lifecycle_status', RentalSparepartStock::STATUS_ACTIVE)
                    ->whereKey($stock->id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->qty_available < $qty) {
                    $stock = null;
                    $matchType = RentalSparepartUsageReview::MATCH_PART_ONLY;
                    $reviewStatus = RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION;
                    $isBorrowed = true;
                    $borrowReason = 'PINJAM - stok cocok tidak cukup saat reservasi.';
                } else {
                    $stock->qty_reserved = (int) $stock->qty_reserved + $qty;
                    $stock->save();
                }
            }

            RentalSparepartUsageReview::create([
                'department' => self::DEPARTMENT,
                'job_id' => $job->id,
                'job_install_part_id' => $installPart->id,
                'sparepart_stock_id' => $stock?->id,
                'sparepart_item_id' => $stock?->sparepart_item_id,
                'work_date' => $job->work_date,
                'job_serial_number' => $job->serial_number,
                'job_customer' => $job->customer,
                'job_location' => $job->location,
                'no_job' => $installPart->no_job,
                'part_number' => $installPart->part_number,
                'part_name' => $installPart->part_name,
                'qty_requested' => $qty,
                'match_type' => $matchType,
                'review_status' => $reviewStatus,
                'is_borrowed' => $isBorrowed,
                'borrow_reason' => $borrowReason,
                'original_allocation_customer' => $stock ? ($stock->allocation_customer ?: $stock->source_customer) : null,
                'original_allocation_location' => $stock ? ($stock->allocation_location ?: $stock->source_location) : null,
                'original_allocation_type_unit' => $stock ? ($stock->allocation_type_unit ?: $stock->source_type_unit) : null,
                'original_allocation_sn_unit' => $stock ? ($stock->allocation_sn_unit ?: $stock->source_sn_unit) : null,
                'actual_customer' => $job->customer,
                'actual_location' => $job->location,
                'actual_type_unit' => $job->unit_type,
                'actual_sn_unit' => $job->serial_number,
                'mechanic_id' => $job->user_id,
                'mechanic_name' => $job->pic,
            ]);

        });
    }

    public function cancelPendingForJobInstallPart(JobInstallPart $installPart): void
    {
        DB::transaction(function () use ($installPart) {
            $reviews = RentalSparepartUsageReview::query()
                ->where('department', self::DEPARTMENT)
                ->where('job_install_part_id', $installPart->id)
                ->whereIn('review_status', [
                    RentalSparepartUsageReview::STATUS_PENDING_REVIEW,
                    RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION,
                ])
                ->lockForUpdate()
                ->get();

            foreach ($reviews as $review) {
                if ($review->review_status === RentalSparepartUsageReview::STATUS_PENDING_REVIEW && $review->sparepart_stock_id) {
                    $stock = RentalSparepartStock::query()
                        ->where('department', self::DEPARTMENT)
                        ->whereKey($review->sparepart_stock_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stock) {
                        $stock->qty_reserved = max(0, (int) $stock->qty_reserved - (int) $review->qty_requested);
                        $stock->save();
                    }
                }

                $review->review_status = RentalSparepartUsageReview::STATUS_CANCELLED_BY_JOB_EDIT;
                $review->review_note = 'Otomatis dibatalkan karena install part di Update Job diedit/dihapus sebelum approval.';
                $review->save();
            }
        });
    }

    private function findBestStock(string $partNumber, ?string $noJob, ?string $actualSn, int $qty): array
    {
        $baseQuery = RentalSparepartStock::query()
            ->with(['item', 'location'])
            ->where('department', self::DEPARTMENT)
            ->where('stock_lifecycle_status', RentalSparepartStock::STATUS_ACTIVE)
            ->whereRaw('(qty_on_hand - qty_reserved) >= ?', [$qty])
            ->whereHas('item', function ($query) use ($partNumber) {
                $query->whereRaw('UPPER(TRIM(part_number)) = ?', [$partNumber]);
            });

        if ($noJob !== null) {
            $stock = (clone $baseQuery)
                ->whereRaw('UPPER(TRIM(COALESCE(source_no_job, ""))) = ?', [$noJob])
                ->orderByDesc('qty_on_hand')
                ->first();

            if ($stock) {
                $stockSn = $this->normalizeNullable($stock->allocation_sn_unit ?: $stock->source_sn_unit);
                $borrowed = $stockSn !== null && $actualSn !== null && $stockSn !== $actualSn;

                return [
                    'stock' => $stock,
                    'match_type' => RentalSparepartUsageReview::MATCH_NO_JOB_EXACT,
                    'review_status' => RentalSparepartUsageReview::STATUS_PENDING_REVIEW,
                    'is_borrowed' => $borrowed,
                    'borrow_reason' => $borrowed ? 'PINJAM - no job cocok, tetapi alokasi SN berbeda.' : null,
                ];
            }
        }

        if ($actualSn !== null) {
            $stock = (clone $baseQuery)
                ->where(function ($query) use ($actualSn) {
                    $query->whereRaw('UPPER(TRIM(COALESCE(allocation_sn_unit, ""))) = ?', [$actualSn])
                        ->orWhereRaw('UPPER(TRIM(COALESCE(source_sn_unit, ""))) = ?', [$actualSn]);
                })
                ->orderByDesc('qty_on_hand')
                ->first();

            if ($stock) {
                return [
                    'stock' => $stock,
                    'match_type' => RentalSparepartUsageReview::MATCH_SN_EXACT,
                    'review_status' => RentalSparepartUsageReview::STATUS_PENDING_REVIEW,
                    'is_borrowed' => false,
                    'borrow_reason' => null,
                ];
            }
        }

        $partOnlyStock = (clone $baseQuery)->orderByDesc('qty_on_hand')->first();

        if ($partOnlyStock) {
            return [
                'stock' => $partOnlyStock,
                'match_type' => RentalSparepartUsageReview::MATCH_PART_ONLY,
                'review_status' => RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION,
                'is_borrowed' => true,
                'borrow_reason' => 'PINJAM - part tersedia, tetapi no job/SN tidak cocok.',
            ];
        }

        return [
            'stock' => null,
            'match_type' => RentalSparepartUsageReview::MATCH_NOT_FOUND,
            'review_status' => RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION,
            'is_borrowed' => true,
            'borrow_reason' => 'PINJAM - part number tidak ditemukan di stok rental.',
        ];
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
