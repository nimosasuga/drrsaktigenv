<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartImportBatchController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartImportBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartImportBatchController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function index(Request $request)
    {
        abort_unless($this->canAccess(), 403);

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => strtoupper(trim((string) $request->input('status', ''))),
        ];

        $query = RentalSparepartImportBatch::query()
            ->withCount('movements')
            ->where('department', self::DEPARTMENT);

        if ($filters['search'] !== '') {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('batch_code', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('imported_by_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        $batches = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total_batch' => RentalSparepartImportBatch::where('department', self::DEPARTMENT)->count(),
            'imported' => RentalSparepartImportBatch::where('department', self::DEPARTMENT)->where('status', RentalSparepartImportBatch::STATUS_IMPORTED)->count(),
            'total_rows' => RentalSparepartImportBatch::where('department', self::DEPARTMENT)->sum('total_rows'),
            'total_qty' => RentalSparepartImportBatch::where('department', self::DEPARTMENT)->sum('total_qty'),
        ];

        return view('rental-spareparts.import-batches.index', compact('batches', 'filters', 'summary'));
    }

    private function canAccess(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT;
    }
}
