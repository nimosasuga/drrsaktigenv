<?php
// PATH FILE: app/Http/Controllers/BatterySaveController.php

namespace App\Http\Controllers;

use App\Models\Battery;
use App\Models\UnitAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatterySaveController extends Controller
{
    private function isWithdrawnAssetStatus($status): bool
    {
        return strtoupper(trim((string) $status)) === 'DITARIK';
    }

    private function rejectIfAssetWithdrawn(string $serialNumber)
    {
        $asset = UnitAsset::where('serial_number', strtoupper(trim($serialNumber)))->first();

        if ($asset && $this->isWithdrawnAssetStatus($asset->status ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['error' => "Serial Number {$serialNumber} tidak bisa digunakan untuk Management Battery karena status unit asset sudah DITARIK."]);
        }

        return null;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'in_time' => 'required|date_format:H:i',
            'out_time' => 'required|date_format:H:i',
            'vehicle' => 'required|string|max:150',
            'nopol' => 'required|string|max:100',
            'customer' => 'required|string|max:150',
            'location' => 'required|string|max:150',
            'unit_type' => 'required|string|max:100',
            'serial_number' => 'required|string|max:100',
            'sn_battery' => 'required|string|max:100',
            'battery_type' => 'required|string|max:100',
            'battery_year' => 'nullable|integer',
            'category_job' => 'required|string|max:100',
            'status_unit' => 'required|string|max:100',
            'problem_date' => 'nullable|date',
            'rfu_date' => 'nullable|date',
            'problem' => 'nullable|string',
            'action' => 'nullable|string',
            'partner' => 'nullable|string|max:150',
        ]);

        if ($blockedResponse = $this->rejectIfAssetWithdrawn($validated['serial_number'])) {
            return $blockedResponse;
        }

        DB::beginTransaction();

        try {
            $battery = new Battery($validated);
            $battery->user_id = Auth::id();
            $battery->branch = Auth::user()->branch ?? 'HO / Pusat';
            $battery->pic = Auth::user()->name;
            $battery->status_mekanik = Auth::user()->role ?? Auth::user()->status_user;

            if ($request->has('job_types') && is_array($request->job_types)) {
                $battery->job_type = implode(', ', $request->job_types);
            }

            $battery->save();

            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $partName) {
                    if (!empty($partName)) {
                        $battery->installParts()->create([
                            'part_number' => $request->inst_part_number[$key] ?? null,
                            'part_name' => $partName,
                            'qty' => $request->inst_qty[$key] ?? 1,
                            'remarks' => $request->inst_remarks[$key] ?? null,
                            'no_job' => $request->inst_no_job[$key] ?? null,
                            'no_pr' => $request->inst_no_pr[$key] ?? null,
                        ]);
                    }
                }
            }

            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $partName) {
                    if (!empty($partName)) {
                        $battery->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name' => $partName,
                            'qty' => $request->rec_qty[$key] ?? 1,
                            'remarks' => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('batteries.show', $battery->id)->with('success', 'Data Battery berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }
}
