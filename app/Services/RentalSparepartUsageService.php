<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Services/RentalSparepartUsageService.php
|--------------------------------------------------------------------------
*/

namespace App\Services;

use App\Models\Job;
use App\Models\JobInstallPart;
use App\Models\RentalSparepartStock;
use App\Models\RentalSparepartUsageReview;
use Illuminate\Support\Facades\DB;

class RentalSparepartUsageService
{
    private const DEPARTMENT = 'RENTAL';

    public function processJobInstallParts(Job $job): void
    {
        if (strtoupper((string) $job->department) !== self::DEPARTMENT) {
            return;
        }

        DB::transaction(function () use ($job) {
            $this->releasePendingUsage($job);

            $job->loadMissing(['installParts', 'user']);

            foreach ($job->installParts as $installPart) {
                $this->processInstallPart($job, $installPart);
            }
        });
    }

    public function releasePendingUsage(Job $job): void
    {
        $reviews = RentalSparepartUsageReview::query()
            ->where('department', self::DEPARTMENT)
            ->where('job_id', $job->id)
            ->whereIn('review_status', [
                RentalSparepartUsageReview::STATUS_PENDING_REVIEW,
                RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION,
            ])
            ->get();

        foreach ($reviews as $review) {
            if ($review->sparepart_stock_id && $review->review_status === RentalSparepartUsageReview::STATUS_PENDING_REVIEW) {
                $stock = RentalSparepartStock::query()
                    ->whereKey($review->sparepart_stock_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->qty_reserved = max(0, (int) $stock->qty_reserved - (int) $review->qty_requested);
                    $stock->save();
                }
            }

            $review->review_status = RentalSparepartUsageReview::STATUS_CANCELLED_BY_JOB_EDIT;
            $review->review_note = 'Dibatalkan otomatis karena Update Job diproses ulang.';
            $review->save();
        }
    }

    private function processInstallPart(Job $job, JobInstallPart $installPart): void
    {
        $partNumber = $this->normalize($installPart->part_number);
        $qty = max(1, (int) $installPart->qty);

        if ($partNumber === '') {
            return;
        }

        $stock = $this->findExactStock($job, $installPart, $qty);

        if ($stock) {
            $stock->qty_reserved = (int) $stock->qty_reserved + $qty;
            $stock->save();

            $this->createReview($job, $installPart, $stock, $qty, $this->matchType($job, $installPart), RentalSparepartUsageReview::STATUS_PENDING_REVIEW, false, null);
            return;
        }

        $alternativeStock = $this->findAlternativeStock($installPart);
        $this->applyBorrowedRemark($installPart);

        $this->createReview($job, $installPart, $alternativeStock, $qty, $alternativeStock ? RentalSparepartUsageReview::MATCH_PART_ONLY : RentalSparepartUsageReview::MATCH_NOT_FOUND, RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION, true, 'PINJAM');
    }

    private function findExactStock(Job $job, JobInstallPart $installPart, int $qty): ?RentalSparepartStock
    {
        $serialNumber = $this->normalize($job->serial_number);
        $partNumber = $this->normalize($installPart->part_number);
        $noJob = $this->normalize($installPart->no_job);

        if ($serialNumber === '' || $partNumber === '') {
            return null;
        }

        $baseQuery = RentalSparepartStock::query()
            ->with('item')
            ->where('department', self::DEPARTMENT)
            ->whereHas('item', function ($query) use ($partNumber) {
                $query->where('part_number', $partNumber);
            })
            ->whereRaw('(qty_on_hand - qty_reserved) >= ?', [$qty])
            ->where(function ($query) use ($serialNumber) {
                $query->where('allocation_sn_unit', $serialNumber)
                    ->orWhere('source_sn_unit', $serialNumber);
            });

        if ($noJob !== '') {
            $stock = (clone $baseQuery)
                ->where('source_no_job', $noJob)
                ->lockForUpdate()
                ->first();

            return $stock;
        }

        return $baseQuery
            ->lockForUpdate()
            ->first();
    }

    private function findAlternativeStock(JobInstallPart $installPart): ?RentalSparepartStock
    {
        $partNumber = $this->normalize($installPart->part_number);

        if ($partNumber === '') {
            return null;
        }

        return RentalSparepartStock::query()
            ->with('item')
            ->where('department', self::DEPARTMENT)
            ->whereHas('item', function ($query) use ($partNumber) {
                $query->where('part_number', $partNumber);
            })
            ->whereRaw('(qty_on_hand - qty_reserved) > 0')
            ->orderByDesc('qty_on_hand')
            ->first();
    }

    private function createReview(Job $job, JobInstallPart $installPart, ?RentalSparepartStock $stock, int $qty, string $matchType, string $status, bool $borrowed, ?string $borrowReason): void
    {
        RentalSparepartUsageReview::create([
            'department' => self::DEPARTMENT,
            'job_id' => $job->id,
            'job_install_part_id' => $installPart->id,
            'sparepart_stock_id' => $stock?->id,
            'sparepart_item_id' => $stock?->sparepart_item_id,
            'work_date' => $job->work_date,
            'job_serial_number' => $this->normalize($job->serial_number),
            'job_customer' => $this->normalize($job->customer),
            'job_location' => $this->normalize($job->location),
            'no_job' => $this->normalize($installPart->no_job),
            'part_number' => $this->normalize($installPart->part_number),
            'part_name' => trim((string) $installPart->part_name),
            'qty_requested' => $qty,
            'match_type' => $matchType,
            'review_status' => $status,
            'is_borrowed' => $borrowed,
            'borrow_reason' => $borrowReason,
            'original_allocation_customer' => $stock?->allocation_customer ?: $stock?->source_customer,
            'original_allocation_location' => $stock?->allocation_location ?: $stock?->source_location,
            'original_allocation_type_unit' => $stock?->allocation_type_unit ?: $stock?->source_type_unit,
            'original_allocation_sn_unit' => $stock?->allocation_sn_unit ?: $stock?->source_sn_unit,
            'actual_customer' => $this->normalize($job->customer),
            'actual_location' => $this->normalize($job->location),
            'actual_type_unit' => $this->normalize($job->unit_type),
            'actual_sn_unit' => $this->normalize($job->serial_number),
            'mechanic_id' => $job->user_id,
            'mechanic_name' => $job->pic ?: $job->user?->name,
        ]);
    }

    private function applyBorrowedRemark(JobInstallPart $installPart): void
    {
        $remarks = trim((string) $installPart->remarks);

        if ($remarks === '') {
            $installPart->remarks = 'PINJAM';
            $installPart->save();
            return;
        }

        if (!str_contains(strtoupper($remarks), 'PINJAM')) {
            $installPart->remarks = 'PINJAM - ' . $remarks;
            $installPart->save();
        }
    }

    private function matchType(Job $job, JobInstallPart $installPart): string
    {
        return $this->normalize($installPart->no_job) !== ''
            ? RentalSparepartUsageReview::MATCH_NO_JOB_EXACT
            : RentalSparepartUsageReview::MATCH_SN_EXACT;
    }

    private function normalize(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }
}
