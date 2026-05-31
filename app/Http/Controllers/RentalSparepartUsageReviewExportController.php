<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartUsageReviewExportController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartUsageReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartUsageReviewExportController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function __invoke(Request $request)
    {
        abort_unless($this->canReview(), 403);

        $query = RentalSparepartUsageReview::query()
            ->with(['job', 'installPart', 'stock.item', 'stock.location', 'mechanic', 'reviewer', 'movement'])
            ->where('department', self::DEPARTMENT);

        $this->applyFilters($query, $request);

        $rows = [];
        $rows[] = [
            'ID',
            'Department',
            'Work Date',
            'Review Status',
            'Match Type',
            'Borrowed',
            'Borrow Reason',
            'Job ID',
            'Install Part ID',
            'Movement ID',
            'Mechanic Name',
            'Part Number',
            'Part Name',
            'Qty Requested',
            'No Job',
            'Job Serial Number',
            'Job Customer',
            'Job Location',
            'Original Allocation Customer',
            'Original Allocation Location',
            'Original Allocation Type Unit',
            'Original Allocation SN Unit',
            'Actual Customer',
            'Actual Location',
            'Actual Type Unit',
            'Actual SN Unit',
            'Stock ID',
            'Stock Part Number',
            'Stock Part Name',
            'Stock Location',
            'Movement Type',
            'Review Note',
            'Reviewed By',
            'Reviewed At',
            'Created At',
        ];

        $query->orderByDesc('created_at')->orderByDesc('id')->chunk(500, function ($reviews) use (&$rows) {
            foreach ($reviews as $review) {
                $rows[] = [
                    $review->id,
                    $review->department,
                    optional($review->work_date)->format('Y-m-d'),
                    $review->review_status,
                    $review->match_type,
                    $review->is_borrowed ? 'YES' : 'NO',
                    $review->borrow_reason,
                    $review->job_id,
                    $review->job_install_part_id,
                    $review->movement_id,
                    $review->mechanic_name ?: $review->mechanic?->name,
                    $review->part_number,
                    $review->part_name,
                    $review->qty_requested,
                    $review->no_job,
                    $review->job_serial_number,
                    $review->job_customer,
                    $review->job_location,
                    $review->original_allocation_customer,
                    $review->original_allocation_location,
                    $review->original_allocation_type_unit,
                    $review->original_allocation_sn_unit,
                    $review->actual_customer,
                    $review->actual_location,
                    $review->actual_type_unit,
                    $review->actual_sn_unit,
                    $review->sparepart_stock_id,
                    $review->stock?->item?->part_number,
                    $review->stock?->item?->part_name,
                    $review->stock?->location?->location_name,
                    $review->movement?->movement_type,
                    $review->review_note,
                    $review->reviewer?->name,
                    optional($review->reviewed_at)->format('Y-m-d H:i:s'),
                    optional($review->created_at)->format('Y-m-d H:i:s'),
                ];
            }
        });

        $csv = "\xEF\xBB\xBF" . collect($rows)->map(function ($row) {
            return collect($row)->map(fn ($value) => $this->csvCell($value))->implode(',');
        })->implode("\n");

        $filename = 'rental_sparepart_usage_reviews_' . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        $reviewStatus = strtoupper(trim((string) $request->input('review_status', '')));
        $matchType = strtoupper(trim((string) $request->input('match_type', '')));
        $partNumber = strtoupper(trim((string) $request->input('part_number', '')));
        $snUnit = strtoupper(trim((string) $request->input('sn_unit', '')));
        $noJob = strtoupper(trim((string) $request->input('no_job', '')));
        $borrowed = trim((string) $request->input('borrowed', ''));
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        if ($reviewStatus !== '') {
            $query->where('review_status', $reviewStatus);
        }

        if ($matchType !== '') {
            $query->where('match_type', $matchType);
        }

        if ($partNumber !== '') {
            $query->where('part_number', 'like', '%' . $partNumber . '%');
        }

        if ($snUnit !== '') {
            $query->where(function ($subQuery) use ($snUnit) {
                $subQuery->where('job_serial_number', 'like', '%' . $snUnit . '%')
                    ->orWhere('original_allocation_sn_unit', 'like', '%' . $snUnit . '%')
                    ->orWhere('actual_sn_unit', 'like', '%' . $snUnit . '%');
            });
        }

        if ($noJob !== '') {
            $query->where('no_job', 'like', '%' . $noJob . '%');
        }

        if ($borrowed === 'yes') {
            $query->where('is_borrowed', true);
        }

        if ($borrowed === 'no') {
            $query->where('is_borrowed', false);
        }

        if ($dateFrom !== '') {
            $query->whereDate('work_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('work_date', '<=', $dateTo);
        }
    }

    private function csvCell($value): string
    {
        $value = (string) $value;
        $value = str_replace('"', '""', $value);

        return '"' . $value . '"';
    }

    private function canReview(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT);
    }
}
