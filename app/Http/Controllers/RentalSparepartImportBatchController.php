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
use Illuminate\Support\Facades\DB;
use Throwable;

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
            'rolled_back' => RentalSparepartImportBatch::where('department', self::DEPARTMENT)->where('status', RentalSparepartImportBatch::STATUS_ROLLED_BACK)->count(),
            'total_rows' => RentalSparepartImportBatch::where('department', self::DEPARTMENT)->sum('total_rows'),
            'total_qty' => RentalSparepartImportBatch::where('department', self::DEPARTMENT)->sum('total_qty'),
        ];

        return view('rental-spareparts.import-batches.index', compact('batches', 'filters', 'summary'));
    }

    public function rollback(Request $request, RentalSparepartImportBatch $batch)
    {
        abort_unless($this->canRollback(), 403, 'Hanya koordinator/sect head RENTAL, admin, dan super admin yang bisa rollback import.');
        abort_if($batch->department !== self::DEPARTMENT, 404);

        $validated = $request->validate([
            'rollback_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($batch->status !== RentalSparepartImportBatch::STATUS_IMPORTED) {
            return back()->withErrors(['rollback' => 'Batch ini sudah tidak berstatus IMPORTED. Rollback dibatalkan.']);
        }

        $batch->load(['movements.stock']);

        if ($batch->movements->isEmpty()) {
            return back()->withErrors(['rollback' => 'Batch ini tidak memiliki movement. Rollback dibatalkan.']);
        }

        foreach ($batch->movements as $movement) {
            $stock = $movement->stock;
            $qty = (int) $movement->qty;

            if (!$stock) {
                return back()->withErrors(['rollback' => 'Rollback gagal: stock ID movement #' . $movement->id . ' tidak ditemukan.']);
            }

            if ((int) $stock->qty_on_hand < $qty) {
                return back()->withErrors(['rollback' => 'Rollback gagal: stok tidak cukup untuk movement #' . $movement->id . '. Kemungkinan stok sudah terpakai.']);
            }

            if ((int) $stock->qty_available < $qty) {
                return back()->withErrors(['rollback' => 'Rollback gagal: available stock tidak cukup untuk movement #' . $movement->id . '. Ada stok yang sudah reserved/terpakai.']);
            }
        }

        try {
            DB::transaction(function () use ($batch, $validated) {
                $user = Auth::user();

                foreach ($batch->movements as $movement) {
                    $stock = $movement->stock()->lockForUpdate()->first();
                    $stock->qty_on_hand = max(0, (int) $stock->qty_on_hand - (int) $movement->qty);
                    $stock->save();
                }

                $batch->status = RentalSparepartImportBatch::STATUS_ROLLED_BACK;
                $batch->rolled_back_by = $user->id;
                $batch->rolled_back_by_name = $user->name;
                $batch->rolled_back_at = now();
                $batch->rollback_note = $validated['rollback_note'] ?? null;
                $batch->save();
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['rollback' => 'Rollback gagal: ' . $exception->getMessage()]);
        }

        return back()->with('success', 'Batch ' . $batch->batch_code . ' berhasil di-rollback.');
    }

    private function canAccess(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT;
    }

    private function canRollback(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT);
    }
}
