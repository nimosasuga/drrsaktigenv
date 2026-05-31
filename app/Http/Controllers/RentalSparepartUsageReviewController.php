<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartUsageReviewController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartUsageReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartUsageReviewController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function index(Request $request)
    {
        abort_unless($this->canReview(), 403, 'Review sparepart rental hanya untuk koordinator/sect head RENTAL, admin, dan super admin.');

        $filters = [
            'review_status' => strtoupper(trim((string) $request->input('review_status', ''))),
            'match_type' => strtoupper(trim((string) $request->input('match_type', ''))),
            'part_number' => strtoupper(trim((string) $request->input('part_number', ''))),
            'sn_unit' => strtoupper(trim((string) $request->input('sn_unit', ''))),
            'no_job' => strtoupper(trim((string) $request->input('no_job', ''))),
            'borrowed' => trim((string) $request->input('borrowed', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $query = RentalSparepartUsageReview::query()
            ->with(['job', 'installPart', 'stock.item', 'stock.location', 'mechanic', 'reviewer'])
            ->where('department', self::DEPARTMENT);

        if ($filters['review_status'] !== '') {
            $query->where('review_status', $filters['review_status']);
        }

        if ($filters['match_type'] !== '') {
            $query->where('match_type', $filters['match_type']);
        }

        if ($filters['part_number'] !== '') {
            $query->where('part_number', 'like', '%' . $filters['part_number'] . '%');
        }

        if ($filters['sn_unit'] !== '') {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('job_serial_number', 'like', '%' . $filters['sn_unit'] . '%')
                    ->orWhere('original_allocation_sn_unit', 'like', '%' . $filters['sn_unit'] . '%')
                    ->orWhere('actual_sn_unit', 'like', '%' . $filters['sn_unit'] . '%');
            });
        }

        if ($filters['no_job'] !== '') {
            $query->where('no_job', 'like', '%' . $filters['no_job'] . '%');
        }

        if ($filters['borrowed'] === 'yes') {
            $query->where('is_borrowed', true);
        }

        if ($filters['borrowed'] === 'no') {
            $query->where('is_borrowed', false);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('work_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('work_date', '<=', $filters['date_to']);
        }

        $reviews = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $summary = $this->summary();

        return view('rental-spareparts.reviews.index', compact('reviews', 'filters', 'summary'));
    }

    private function summary(): array
    {
        $base = RentalSparepartUsageReview::query()->where('department', self::DEPARTMENT);

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('review_status', RentalSparepartUsageReview::STATUS_PENDING_REVIEW)->count(),
            'need_source' => (clone $base)->where('review_status', RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION)->count(),
            'borrowed' => (clone $base)->where('is_borrowed', true)->count(),
            'approved' => (clone $base)->where('review_status', RentalSparepartUsageReview::STATUS_APPROVED)->count(),
            'rejected' => (clone $base)->where('review_status', RentalSparepartUsageReview::STATUS_REJECTED)->count(),
        ];
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
