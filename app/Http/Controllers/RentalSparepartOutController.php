<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartOutController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartOutController extends Controller
{
    private const DEPARTMENT = 'RENTAL';

    public function create(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $stocks = RentalSparepartStock::query()
            ->with(['item', 'location'])
            ->where('department', self::DEPARTMENT)
            ->whereRaw('(qty_on_hand - qty_reserved) > 0')
            ->orderByDesc('updated_at')
            ->get();

        $selectedStock = null;

        if ($request->filled('stock_id')) {
            $selectedStock = $stocks->firstWhere('id', (int) $request->input('stock_id'));
        }

        return view('rental-spareparts.out-create', compact('stocks', 'selectedStock'));
    }

    private function canManage(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT);
    }
}
