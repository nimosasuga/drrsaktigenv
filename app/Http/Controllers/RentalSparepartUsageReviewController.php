<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartUsageReviewController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartMovement;
use App\Models\RentalSparepartStock;
use App\Models\RentalSparepartUsageReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $sourceOptions = $this->sourceOptions($reviews->getCollection()->pluck('part_number')->filter()->unique()->values()->all());

        return view('rental-spareparts.reviews.index', compact('reviews', 'filters', 'summary', 'sourceOptions'));
    }

    public function approve(Request $request, RentalSparepartUsageReview $review)
    {
        abort_unless($this->canReview(), 403);

        $validated = $request->validate([
            'stock_id' => ['nullable', 'integer', 'exists:rental_sparepart_stocks,id'],
            'review_note' => ['nullable', 'string'],
        ]);

        $error = null;

        DB::transaction(function () use ($review, $validated, &$error) {
            $review = RentalSparepartUsageReview::query()
                ->where('department', self::DEPARTMENT)
                ->whereKey($review->id)
                ->lockForUpdate()
                ->first();

            if (!$review) {
                $error = 'Review tidak ditemukan.';
                return;
            }

            if (!in_array($review->review_status, [RentalSparepartUsageReview::STATUS_PENDING_REVIEW, RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION], true)) {
                $error = 'Review ini sudah tidak bisa di-approve.';
                return;
            }

            $stockId = $validated['stock_id'] ?? $review->sparepart_stock_id;

            if (!$stockId) {
                $error = 'Pilih stok sumber terlebih dahulu sebelum approve.';
                return;
            }

            $stock = RentalSparepartStock::query()
                ->with(['item', 'location'])
                ->where('department', self::DEPARTMENT)
                ->whereKey($stockId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $error = 'Stok sumber tidak ditemukan.';
                return;
            }

            if ($this->normalize($stock->item?->part_number) !== $this->normalize($review->part_number)) {
                $error = 'Part number stok sumber tidak sama dengan part number review.';
                return;
            }

            $qty = max(1, (int) $review->qty_requested);
            $available = max(0, (int) $stock->qty_on_hand - (int) $stock->qty_reserved);

            if ($review->review_status === RentalSparepartUsageReview::STATUS_PENDING_REVIEW) {
                if ((int) $stock->qty_on_hand < $qty) {
                    $error = 'Qty on hand tidak cukup untuk approve.';
                    return;
                }

                $stock->qty_reserved = max(0, (int) $stock->qty_reserved - $qty);
                $stock->qty_on_hand = (int) $stock->qty_on_hand - $qty;
            } else {
                if ($available < $qty) {
                    $error = 'Qty available stok sumber tidak cukup.';
                    return;
                }

                $stock->qty_on_hand = (int) $stock->qty_on_hand - $qty;
                $review->sparepart_stock_id = $stock->id;
                $review->sparepart_item_id = $stock->sparepart_item_id;
                $review->original_allocation_customer = $stock->allocation_customer ?: $stock->source_customer;
                $review->original_allocation_location = $stock->allocation_location ?: $stock->source_location;
                $review->original_allocation_type_unit = $stock->allocation_type_unit ?: $stock->source_type_unit;
                $review->original_allocation_sn_unit = $stock->allocation_sn_unit ?: $stock->source_sn_unit;
            }

            $stock->save();

            $movement = RentalSparepartMovement::create([
                'department' => self::DEPARTMENT,
                'movement_type' => RentalSparepartMovement::TYPE_OUT,
                'movement_date' => $review->work_date ?: now()->toDateString(),
                'sparepart_item_id' => $stock->sparepart_item_id,
                'sparepart_stock_id' => $stock->id,
                'from_location_id' => $stock->location_id,
                'to_location_id' => null,
                'part_number_snapshot' => $stock->item?->part_number ?: $review->part_number,
                'part_name_snapshot' => $stock->item?->part_name ?: $review->part_name,
                'qty' => $qty,
                'no_job' => $review->no_job,
                'source_customer' => $stock->source_customer,
                'source_location' => $stock->source_location,
                'source_type_unit' => $stock->source_type_unit,
                'source_sn_unit' => $stock->source_sn_unit,
                'allocation_customer' => $stock->allocation_customer,
                'allocation_location' => $stock->allocation_location,
                'allocation_type_unit' => $stock->allocation_type_unit,
                'allocation_sn_unit' => $stock->allocation_sn_unit,
                'actual_customer' => $review->actual_customer,
                'actual_location' => $review->actual_location,
                'actual_type_unit' => $review->actual_type_unit,
                'actual_sn_unit' => $review->actual_sn_unit,
                'is_cross_allocation' => $this->isCrossAllocation($stock, $review),
                'pic_user_id' => $review->mechanic_id,
                'pic_name' => $review->mechanic_name,
                'remarks' => $validated['review_note'] ?? $review->borrow_reason,
            ]);

            $review->movement_id = $movement->id;
            $review->review_status = RentalSparepartUsageReview::STATUS_APPROVED;
            $review->reviewed_by = Auth::id();
            $review->reviewed_at = now();
            $review->review_note = $validated['review_note'] ?? 'Approved oleh koordinator rental.';
            $review->save();
        });

        if ($error) {
            return back()->withErrors(['review' => $error]);
        }

        return back()->with('success', 'Usage review berhasil di-approve dan movement OUT sudah dibuat.');
    }

    public function reject(Request $request, RentalSparepartUsageReview $review)
    {
        abort_unless($this->canReview(), 403);

        $validated = $request->validate([
            'review_note' => ['nullable', 'string'],
        ]);

        $error = null;

        DB::transaction(function () use ($review, $validated, &$error) {
            $review = RentalSparepartUsageReview::query()
                ->where('department', self::DEPARTMENT)
                ->whereKey($review->id)
                ->lockForUpdate()
                ->first();

            if (!$review) {
                $error = 'Review tidak ditemukan.';
                return;
            }

            if (!in_array($review->review_status, [RentalSparepartUsageReview::STATUS_PENDING_REVIEW, RentalSparepartUsageReview::STATUS_NEED_SOURCE_SELECTION], true)) {
                $error = 'Review ini sudah tidak bisa di-reject.';
                return;
            }

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

            $review->review_status = RentalSparepartUsageReview::STATUS_REJECTED;
            $review->reviewed_by = Auth::id();
            $review->reviewed_at = now();
            $review->review_note = $validated['review_note'] ?? 'Rejected oleh koordinator rental.';
            $review->save();
        });

        if ($error) {
            return back()->withErrors(['review' => $error]);
        }

        return back()->with('success', 'Usage review berhasil di-reject. Reserved stock sudah dikembalikan jika ada.');
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

    private function sourceOptions(array $partNumbers): array
    {
        if (empty($partNumbers)) {
            return [];
        }

        $stocks = RentalSparepartStock::query()
            ->with(['item', 'location'])
            ->where('department', self::DEPARTMENT)
            ->whereRaw('(qty_on_hand - qty_reserved) > 0')
            ->whereHas('item', function ($query) use ($partNumbers) {
                $query->whereIn('part_number', $partNumbers);
            })
            ->orderByDesc('qty_on_hand')
            ->get();

        return $stocks
            ->groupBy(fn ($stock) => $this->normalize($stock->item?->part_number))
            ->map(function ($items) {
                return $items->map(function ($stock) {
                    return [
                        'id' => $stock->id,
                        'label' => ($stock->item?->part_number ?: '-')
                            . ' | ' . ($stock->item?->part_name ?: '-')
                            . ' | Sisa: ' . max(0, (int) $stock->qty_on_hand - (int) $stock->qty_reserved)
                            . ' | ' . ($stock->location?->location_name ?: 'Tanpa Lokasi')
                            . ' | SN: ' . ($stock->allocation_sn_unit ?: $stock->source_sn_unit ?: '-'),
                    ];
                })->values();
            })
            ->toArray();
    }

    private function isCrossAllocation(RentalSparepartStock $stock, RentalSparepartUsageReview $review): bool
    {
        $sourceSn = $this->normalize($stock->allocation_sn_unit ?: $stock->source_sn_unit);
        $actualSn = $this->normalize($review->actual_sn_unit);

        return $sourceSn !== '' && $actualSn !== '' && $sourceSn !== $actualSn;
    }

    private function canReview(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT);
    }

    private function normalize(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }
}
