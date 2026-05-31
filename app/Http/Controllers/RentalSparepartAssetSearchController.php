<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartAssetSearchController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\UnitAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSparepartAssetSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        abort_unless(
            in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
                && (in_array($role, ['admin', 'super_admin'], true) || $department === 'RENTAL'),
            403
        );

        $serialNumber = strtoupper(trim((string) $request->query('serial_number', '')));

        if ($serialNumber === '') {
            return response()->json(['found' => false]);
        }

        $asset = UnitAsset::query()
            ->withoutGlobalScope('department')
            ->where('department', 'RENTAL')
            ->where('serial_number', $serialNumber)
            ->first();

        if (!$asset) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'serial_number' => $asset->serial_number,
            'unit_type' => $asset->unit_type,
            'customer' => $asset->customer,
            'customer_location' => $asset->location,
        ]);
    }
}
