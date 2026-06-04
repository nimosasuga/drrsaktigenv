<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/SparepartRecommendationControlController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartStock;
use App\Models\SparepartRecommendationControl;
use App\Support\SparepartRecommendationSupplyStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SparepartRecommendationControlController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($this->canAccess(), 403);

        $department = $this->departmentFilter($request);
        $summary = $this->summary($department);

        return view('sparepart-recommendations.index', compact(
            'department',
            'summary'
        ));
    }

    public function parts(Request $request)
    {
        abort_unless($this->canAccess(), 403);

        $department = $this->departmentFilter($request);

        $filters = [
            'department' => $department,
            'search' => trim((string) $request->input('search', '')),
            'serial_number' => strtoupper(trim((string) $request->input('serial_number', ''))),
            'customer' => strtoupper(trim((string) $request->input('customer', ''))),
            'part_number' => strtoupper(trim((string) $request->input('part_number', ''))),
            'recommendation_status' => strtoupper(trim((string) $request->input('recommendation_status', ''))),
            'supply_status' => strtoupper(trim((string) $request->input('supply_status', ''))),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $query = SparepartRecommendationControl::query()
            ->with(['job', 'recommendation', 'sourceStock.item', 'sourceStock.location', 'recommendedBy', 'reviewedBy', 'suppliedBy'])
            ->where('department', $department);

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%")
                    ->orWhere('recommended_by_name', 'like', "%{$search}%");
            });
        }

        if ($filters['serial_number'] !== '') {
            $query->where('serial_number', 'like', '%' . $filters['serial_number'] . '%');
        }

        if ($filters['customer'] !== '') {
            $query->where('customer', 'like', '%' . $filters['customer'] . '%');
        }

        if ($filters['part_number'] !== '') {
            $query->where('part_number', 'like', '%' . $filters['part_number'] . '%');
        }

        if ($filters['recommendation_status'] !== '') {
            $query->where('recommendation_status', $filters['recommendation_status']);
        }

        if ($filters['supply_status'] !== '') {
            $query->where('supply_status', $filters['supply_status']);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('work_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('work_date', '<=', $filters['date_to']);
        }

        $controls = $query
            ->orderByRaw("FIELD(recommendation_status, 'RECOMMENDED', 'REVIEWED', 'APPROVED', 'NEED_SUPPLY', 'SUPPLIED', 'PARTIAL_INSTALLED', 'INSTALLED', 'CLOSED', 'REJECTED', 'CANCELLED')")
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $summary = $this->summary($department);
        $sourceStocks = $this->sourceStocks($department);
        $statusOptions = $this->statusOptions();
        $supplyStatusOptions = $this->supplyStatusOptions();
        $canManage = $this->canManage($department);

        return view('sparepart-recommendations.parts', compact(
            'controls',
            'filters',
            'summary',
            'sourceStocks',
            'statusOptions',
            'supplyStatusOptions',
            'canManage'
        ));
    }

    public function units(Request $request)
    {
        abort_unless($this->canAccess(), 403);

        $department = $this->departmentFilter($request);

        $filters = [
            'department' => $department,
            'search' => trim((string) $request->input('search', '')),
            'serial_number' => strtoupper(trim((string) $request->input('serial_number', ''))),
            'customer' => strtoupper(trim((string) $request->input('customer', ''))),
            'part_number' => strtoupper(trim((string) $request->input('part_number', ''))),
            'recommendation_status' => strtoupper(trim((string) $request->input('recommendation_status', ''))),
            'supply_status' => strtoupper(trim((string) $request->input('supply_status', ''))),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $query = SparepartRecommendationControl::query()
            ->where('department', $department)
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '');

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%")
                    ->orWhere('recommended_by_name', 'like', "%{$search}%");
            });
        }

        if ($filters['serial_number'] !== '') {
            $query->where('serial_number', 'like', '%' . $filters['serial_number'] . '%');
        }

        if ($filters['customer'] !== '') {
            $query->where('customer', 'like', '%' . $filters['customer'] . '%');
        }

        if ($filters['part_number'] !== '') {
            $query->where('part_number', 'like', '%' . $filters['part_number'] . '%');
        }

        if ($filters['recommendation_status'] !== '') {
            $query->where('recommendation_status', $filters['recommendation_status']);
        }

        if ($filters['supply_status'] !== '') {
            $query->where('supply_status', $filters['supply_status']);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('work_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('work_date', '<=', $filters['date_to']);
        }

        $units = $query
            ->selectRaw("
                serial_number,
                MAX(customer) as customer,
                MAX(location) as location,
                MAX(unit_type) as unit_type,
                COUNT(*) as total_items,
                SUM(qty_recommended) as qty_recommended,
                SUM(qty_supplied) as qty_supplied,
                SUM(qty_installed) as qty_installed,
                SUM(CASE WHEN supply_status = 'NEED_SUPPLY' THEN 1 ELSE 0 END) as need_supply_count,
                SUM(CASE WHEN supply_status = 'SUPPLIED' THEN 1 ELSE 0 END) as supplied_count,
                SUM(CASE WHEN recommendation_status IN ('INSTALLED', 'PARTIAL_INSTALLED') THEN 1 ELSE 0 END) as installed_count,
                SUM(CASE WHEN recommendation_status IN ('CLOSED', 'REJECTED', 'CANCELLED') THEN 1 ELSE 0 END) as closed_count,
                MAX(work_date) as latest_work_date
            ")
            ->groupBy('serial_number')
            ->orderByDesc('need_supply_count')
            ->orderByDesc('latest_work_date')
            ->paginate(10)
            ->withQueryString();

        $summary = $this->summary($department);
        $statusOptions = $this->statusOptions();
        $supplyStatusOptions = $this->supplyStatusOptions();

        return view('sparepart-recommendations.units', compact(
            'units',
            'filters',
            'summary',
            'statusOptions',
            'supplyStatusOptions'
        ));
    }

    public function unitShow(Request $request, string $serialNumber)
    {
        abort_unless($this->canAccess(), 403);

        $department = $this->departmentFilter($request);
        $serialNumber = strtoupper(trim((string) $serialNumber));

        abort_if($serialNumber === '', 404);

        $controls = SparepartRecommendationControl::query()
            ->with(['job', 'recommendation', 'sourceStock.item', 'sourceStock.location', 'recommendedBy', 'reviewedBy', 'suppliedBy'])
            ->where('department', $department)
            ->where('serial_number', $serialNumber)
            ->orderByRaw("FIELD(recommendation_status, 'RECOMMENDED', 'REVIEWED', 'APPROVED', 'NEED_SUPPLY', 'SUPPLIED', 'PARTIAL_INSTALLED', 'INSTALLED', 'CLOSED', 'REJECTED', 'CANCELLED')")
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->get();

        abort_if($controls->isEmpty(), 404);

        $unit = (object) [
            'serial_number' => $serialNumber,
            'customer' => $controls->pluck('customer')->filter()->first() ?: '-',
            'location' => $controls->pluck('location')->filter()->first() ?: '-',
            'unit_type' => $controls->pluck('unit_type')->filter()->first() ?: '-',
            'latest_work_date' => $controls->pluck('work_date')->filter()->max(),
            'total_items' => $controls->count(),
            'qty_recommended' => $controls->sum('qty_recommended'),
            'qty_supplied' => $controls->sum('qty_supplied'),
            'qty_installed' => $controls->sum('qty_installed'),
            'need_supply_count' => $controls->where('supply_status', SparepartRecommendationControl::SUPPLY_NEED_SUPPLY)->count(),
            'supplied_count' => $controls->where('supply_status', SparepartRecommendationControl::SUPPLY_SUPPLIED)->count(),
            'installed_count' => $controls->whereIn('recommendation_status', [
                SparepartRecommendationControl::STATUS_INSTALLED,
                SparepartRecommendationControl::STATUS_PARTIAL_INSTALLED,
            ])->count(),
            'closed_count' => $controls->whereIn('recommendation_status', [
                SparepartRecommendationControl::STATUS_CLOSED,
                SparepartRecommendationControl::STATUS_REJECTED,
                SparepartRecommendationControl::STATUS_CANCELLED,
            ])->count(),
        ];

        $summary = $this->summary($department);
        $canManage = $this->canManage($department);

        return view('sparepart-recommendations.unit-show', compact(
            'department',
            'serialNumber',
            'unit',
            'controls',
            'summary',
            'canManage'
        ));
    }

    public function updateStatus(Request $request, SparepartRecommendationControl $control)
    {
        abort_unless($this->canManage($control->department), 403);

        $validated = $request->validate([
            'action_type' => ['required', 'string', 'max:50'],
            'qty_supplied' => ['nullable', 'integer', 'min:0'],
            'source_stock_id' => ['nullable', 'integer', 'exists:rental_sparepart_stocks,id'],
            'source_type' => ['nullable', 'string', 'max:80'],
            'supply_no_job' => ['nullable', 'string', 'max:150'],
            'supply_date' => ['nullable', 'date'],
            'location_code' => ['nullable', 'string', 'max:100'],
            'location_name' => ['nullable', 'string', 'max:150'],
            'cabinet' => ['nullable', 'string', 'max:100'],
            'shelf' => ['nullable', 'string', 'max:100'],
            'box' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = strtoupper(trim((string) $validated['action_type']));
        $user = Auth::user();
        $note = trim((string) ($validated['note'] ?? ''));

        if ($control->isClosed() && !in_array($action, ['CLOSE'], true)) {
            return back()->withErrors(['control' => 'Rekomendasi ini sudah closed/cancelled/rejected.']);
        }

        match ($action) {
            'REVIEWED' => $this->markReviewed($control, $user, $note),
            'APPROVED' => $this->markApproved($control, $user, $note),
            'REJECTED' => $this->markRejected($control, $user, $note),
            'NEED_SUPPLY' => $this->markNeedSupply($control, $user, $note),
            'SUPPLIED' => $this->markSupplied($control, $validated, $user, $note),
            'CLOSED' => $this->markClosed($control, $note),
            'CANCELLED' => $this->markCancelled($control, $note),
            default => abort(422, 'Action recommendation tidak valid.'),
        };

        return back()->with('success', 'Recommendation Control berhasil diperbarui.');
    }

    private function markReviewed(SparepartRecommendationControl $control, $user, string $note): void
    {
        $control->recommendation_status = SparepartRecommendationControl::STATUS_REVIEWED;
        $control->reviewed_by = $user->id;
        $control->reviewed_by_name = $user->name;
        $control->reviewed_at = now();
        $control->review_note = $note ?: $control->review_note;
        $control->save();
    }

    private function markApproved(SparepartRecommendationControl $control, $user, string $note): void
    {
        $control->recommendation_status = SparepartRecommendationControl::STATUS_APPROVED;
        $control->supply_status = (int) $control->qty_supplied >= (int) $control->qty_recommended
            ? SparepartRecommendationControl::SUPPLY_SUPPLIED
            : SparepartRecommendationControl::SUPPLY_NEED_SUPPLY;
        $control->reviewed_by = $user->id;
        $control->reviewed_by_name = $user->name;
        $control->reviewed_at = now();
        $control->review_note = $note ?: $control->review_note;
        $control->save();
    }

    private function markRejected(SparepartRecommendationControl $control, $user, string $note): void
    {
        $control->recommendation_status = SparepartRecommendationControl::STATUS_REJECTED;
        $control->supply_status = SparepartRecommendationControl::SUPPLY_NOT_REQUIRED;
        $control->reviewed_by = $user->id;
        $control->reviewed_by_name = $user->name;
        $control->reviewed_at = now();
        $control->review_note = $note ?: $control->review_note;
        $control->closed_at = now();
        $control->save();
    }

    private function markNeedSupply(SparepartRecommendationControl $control, $user, string $note): void
    {
        $control->recommendation_status = SparepartRecommendationControl::STATUS_NEED_SUPPLY;
        $control->supply_status = SparepartRecommendationControl::SUPPLY_NEED_SUPPLY;
        $control->reviewed_by = $control->reviewed_by ?: $user->id;
        $control->reviewed_by_name = $control->reviewed_by_name ?: $user->name;
        $control->reviewed_at = $control->reviewed_at ?: now();
        $control->supply_note = $note ?: $control->supply_note;
        $control->save();
    }

    private function markSupplied(SparepartRecommendationControl $control, array $validated, $user, string $note): void
    {
        $qty = array_key_exists('qty_supplied', $validated) && $validated['qty_supplied'] !== null
            ? max(1, (int) $validated['qty_supplied'])
            : max(1, (int) $control->qty_recommended - (int) $control->qty_supplied);

        if (!empty($validated['source_stock_id'])) {
            $this->markSuppliedFromExistingStock($control, $validated, $user, $note, $qty);
            return;
        }

        $stock = app(SparepartRecommendationSupplyStockService::class)
            ->createStockInFromRecommendation($control, array_merge($validated, ['qty_supplied' => $qty]), $user);

        $control->qty_supplied = min((int) $control->qty_recommended, (int) $control->qty_supplied + $qty);
        $control->recommendation_status = (int) $control->qty_supplied >= (int) $control->qty_recommended
            ? SparepartRecommendationControl::STATUS_SUPPLIED
            : SparepartRecommendationControl::STATUS_NEED_SUPPLY;
        $control->supply_status = (int) $control->qty_supplied >= (int) $control->qty_recommended
            ? SparepartRecommendationControl::SUPPLY_SUPPLIED
            : SparepartRecommendationControl::SUPPLY_PARTIAL_SUPPLIED;
        $control->source_stock_id = $stock->id;
        $control->source_type = SparepartRecommendationControl::SOURCE_STOCK;
        $control->is_cross_allocation = false;
        $control->supplied_by = $user->id;
        $control->supplied_by_name = $user->name;
        $control->supplied_at = now();
        $control->supply_note = $note ?: 'Supply dibuat menjadi stok sparepart dari Recommendation Control.';
        $control->save();
    }

    private function markSuppliedFromExistingStock(SparepartRecommendationControl $control, array $validated, $user, string $note, int $qty): void
    {
        $stock = RentalSparepartStock::query()
            ->where('department', $control->department)
            ->where('stock_lifecycle_status', RentalSparepartStock::STATUS_ACTIVE)
            ->whereKey((int) $validated['source_stock_id'])
            ->first();

        if (!$stock) {
            abort(422, 'Source stock tidak ditemukan atau sudah archived.');
        }

        $control->qty_supplied = min((int) $control->qty_recommended, (int) $control->qty_supplied + $qty);
        $control->recommendation_status = (int) $control->qty_supplied >= (int) $control->qty_recommended
            ? SparepartRecommendationControl::STATUS_SUPPLIED
            : SparepartRecommendationControl::STATUS_NEED_SUPPLY;
        $control->supply_status = (int) $control->qty_supplied >= (int) $control->qty_recommended
            ? SparepartRecommendationControl::SUPPLY_SUPPLIED
            : SparepartRecommendationControl::SUPPLY_PARTIAL_SUPPLIED;
        $control->source_stock_id = $stock->id;
        $control->source_type = SparepartRecommendationControl::SOURCE_STOCK;
        $stockSn = strtoupper(trim((string) ($stock->allocation_sn_unit ?: $stock->source_sn_unit)));
        $controlSn = strtoupper(trim((string) $control->serial_number));
        $control->is_cross_allocation = $stockSn !== '' && $controlSn !== '' && $stockSn !== $controlSn;
        $control->supplied_by = $user->id;
        $control->supplied_by_name = $user->name;
        $control->supplied_at = now();
        $control->supply_note = $note ?: 'Supply ditandai dari stok sparepart existing.';
        $control->save();
    }

    private function markClosed(SparepartRecommendationControl $control, string $note): void
    {
        $control->recommendation_status = SparepartRecommendationControl::STATUS_CLOSED;
        $control->closed_at = now();
        $control->review_note = $note ?: $control->review_note;
        $control->save();
    }

    private function markCancelled(SparepartRecommendationControl $control, string $note): void
    {
        $control->recommendation_status = SparepartRecommendationControl::STATUS_CANCELLED;
        $control->supply_status = SparepartRecommendationControl::SUPPLY_NOT_REQUIRED;
        $control->closed_at = now();
        $control->review_note = $note ?: $control->review_note;
        $control->save();
    }

    private function summary(string $department): array
    {
        $base = SparepartRecommendationControl::query()->where('department', $department);

        return [
            'total' => (clone $base)->count(),
            'recommended' => (clone $base)->where('recommendation_status', SparepartRecommendationControl::STATUS_RECOMMENDED)->count(),
            'need_supply' => (clone $base)->where('supply_status', SparepartRecommendationControl::SUPPLY_NEED_SUPPLY)->count(),
            'supplied' => (clone $base)->where('supply_status', SparepartRecommendationControl::SUPPLY_SUPPLIED)->count(),
            'installed' => (clone $base)->whereIn('recommendation_status', [SparepartRecommendationControl::STATUS_INSTALLED, SparepartRecommendationControl::STATUS_PARTIAL_INSTALLED])->count(),
            'closed' => (clone $base)->whereIn('recommendation_status', [SparepartRecommendationControl::STATUS_CLOSED, SparepartRecommendationControl::STATUS_REJECTED, SparepartRecommendationControl::STATUS_CANCELLED])->count(),
        ];
    }

    private function sourceStocks(string $department)
    {
        return RentalSparepartStock::query()
            ->with(['item', 'location'])
            ->where('department', $department)
            ->where('stock_lifecycle_status', RentalSparepartStock::STATUS_ACTIVE)
            ->whereRaw('(qty_on_hand - qty_reserved) > 0')
            ->orderByDesc('qty_on_hand')
            ->limit(300)
            ->get();
    }

    private function statusOptions(): array
    {
        return [
            SparepartRecommendationControl::STATUS_RECOMMENDED,
            SparepartRecommendationControl::STATUS_REVIEWED,
            SparepartRecommendationControl::STATUS_APPROVED,
            SparepartRecommendationControl::STATUS_NEED_SUPPLY,
            SparepartRecommendationControl::STATUS_SUPPLIED,
            SparepartRecommendationControl::STATUS_PARTIAL_INSTALLED,
            SparepartRecommendationControl::STATUS_INSTALLED,
            SparepartRecommendationControl::STATUS_CLOSED,
            SparepartRecommendationControl::STATUS_REJECTED,
            SparepartRecommendationControl::STATUS_CANCELLED,
        ];
    }

    private function supplyStatusOptions(): array
    {
        return [
            SparepartRecommendationControl::SUPPLY_NOT_SUPPLIED,
            SparepartRecommendationControl::SUPPLY_NEED_SUPPLY,
            SparepartRecommendationControl::SUPPLY_PARTIAL_SUPPLIED,
            SparepartRecommendationControl::SUPPLY_SUPPLIED,
            SparepartRecommendationControl::SUPPLY_NOT_REQUIRED,
        ];
    }

    private function canAccess(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true) || in_array($department, ['RENTAL', 'SERVICE'], true);
    }

    private function canManage(string $department): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $userDepartment = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($role, ['admin', 'super_admin'], true) || $userDepartment === strtoupper($department));
    }

    private function departmentFilter(Request $request): string
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $userDepartment = strtoupper(trim((string) ($user->department ?? '')));
        $requested = strtoupper(trim((string) $request->input('department', $userDepartment ?: 'RENTAL')));

        if (in_array($role, ['admin', 'super_admin'], true) && in_array($requested, ['RENTAL', 'SERVICE'], true)) {
            return $requested;
        }

        abort_unless(in_array($userDepartment, ['RENTAL', 'SERVICE'], true), 403);

        return $userDepartment;
    }
}
