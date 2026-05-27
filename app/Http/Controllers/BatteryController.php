<?php
// app/Http/Controllers/BatteryController.php

namespace App\Http\Controllers;

use App\Models\Battery;
use App\Models\User;
use App\Models\UnitAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatteryController extends Controller
{
    /**
     * Helper untuk mengecek Hak Akses (Otorisasi)
     */
    private function canEditBattery($battery)
    {
        $user = Auth::user();
        $role = $user->role ?? $user->status_user;
        $privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];
        return ($user->id === $battery->user_id) || in_array($role, $privilegedRoles);
    }

    /**
     * Menampilkan daftar Battery Job dengan Filter & Pagination.
     */
    public function index(Request $request)
    {
        $query = Battery::with('user')->latest();

        // 1. Filter Berdasarkan Bulan (Format dari input type="month" adalah YYYY-MM)
        if ($request->filled('month_filter')) {
            $parts = explode('-', $request->month_filter);
            if (count($parts) == 2) {
                $query->whereYear('date', $parts[0])
                    ->whereMonth('date', $parts[1]);
            }
        }

        // 2. Filter Berdasarkan Customer
        if ($request->filled('customer_filter')) {
            $query->where('customer', $request->customer_filter);
        }

        // Ambil data dengan pagination, append query string agar pagination tidak mereset filter
        $batteries = $query->paginate(20)->withQueryString();

        // Ambil daftar customer unik yang ada di tabel batteries untuk dropdown filter
        $customers = Battery::select('customer')->distinct()->orderBy('customer')->pluck('customer');

        return view('batteries.index', compact('batteries', 'customers'));
    }

    public function create()
    {
        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';

        $partners = User::where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->get(['id', 'name']);

        return view('batteries.create', compact('user', 'branch', 'partners'));
    }

    public function searchAssets(Request $request)
    {
        $search = $request->get('q');
        if (empty($search)) return response()->json([]);

        $assets = UnitAsset::where('serial_number', 'LIKE', "%{$search}%")->take(10)->get();

        $mapped = $assets->map(function ($asset) {
            return [
                'serial_number' => $asset->serial_number,
                'unit_type'     => $asset->unit_type ?? $asset->unit_model ?? $asset->tipe_unit ?? '',
                'customer'      => $asset->customer ?? $asset->nama_pelanggan ?? '',
                'location'      => $asset->location ?? $asset->lokasi ?? ''
            ];
        });

        return response()->json($mapped);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'          => 'required|date',
            'in_time'       => 'required|date_format:H:i',
            'out_time'      => 'required|date_format:H:i',
            'vehicle'       => 'required|string|max:150',
            'nopol'         => 'required|string|max:100',

            'customer'      => 'required|string|max:150',
            'location'      => 'required|string|max:150',
            'unit_type'     => 'required|string|max:100',
            'serial_number' => 'required|string|max:100',

            'sn_battery'    => 'required|string|max:100',
            'battery_type'  => 'required|string|max:100',
            'battery_year'  => 'nullable|integer',

            'category_job'  => 'required|string|max:100',
            'status_unit'   => 'required|string|max:100',

            'problem_date'  => 'nullable|date',
            'rfu_date'      => 'nullable|date',
            'problem'       => 'nullable|string',
            'action'        => 'nullable|string',
            'partner'       => 'nullable|string|max:150',
        ]);

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
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $battery->installParts()->create([
                            'part_number' => $request->inst_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->inst_qty[$key] ?? 1,
                            'remarks'     => $request->inst_remarks[$key] ?? null,
                            'no_job'      => $request->inst_no_job[$key] ?? null,
                            'no_pr'       => $request->inst_no_pr[$key] ?? null,
                        ]);
                    }
                }
            }

            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $battery->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->rec_qty[$key] ?? 1,
                            'remarks'     => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('batteries.index')->with('success', 'Data Battery berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $battery = Battery::with(['user', 'installParts', 'recommendations'])->findOrFail($id);
        return view('batteries.show', compact('battery'));
    }

    public function destroy($id)
    {
        $battery = Battery::findOrFail($id);

        if (!$this->canEditBattery($battery)) {
            return redirect()->route('batteries.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk menghapus data teknisi lain.']);
        }

        $battery->delete();
        return redirect()->route('batteries.index')->with('success', 'Data Battery berhasil dihapus permanen.');
    }

    /**
     * Menampilkan form edit data Battery.
     */
    public function edit($id)
    {
        $battery = Battery::with(['installParts', 'recommendations'])->findOrFail($id);

        if (!$this->canEditBattery($battery)) {
            return redirect()->route('batteries.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit data milik teknisi lain.']);
        }

        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';

        $partners = User::where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->get(['id', 'name']);

        return view('batteries.edit', compact('battery', 'user', 'branch', 'partners'));
    }

    /**
     * Memperbarui data Battery di database.
     */
    public function update(Request $request, $id)
    {
        $battery = Battery::findOrFail($id);

        if (!$this->canEditBattery($battery)) {
            return redirect()->route('batteries.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit data milik teknisi lain.']);
        }

        $validated = $request->validate([
            'date'          => 'required|date',
            'in_time'       => 'required|date_format:H:i',
            'out_time'      => 'required|date_format:H:i',
            'vehicle'       => 'required|string|max:150',
            'nopol'         => 'required|string|max:100',

            'customer'      => 'required|string|max:150',
            'location'      => 'required|string|max:150',
            'unit_type'     => 'required|string|max:100',
            'serial_number' => 'required|string|max:100',

            'sn_battery'    => 'required|string|max:100',
            'battery_type'  => 'required|string|max:100',
            'battery_year'  => 'nullable|integer',

            'category_job'  => 'required|string|max:100',
            'status_unit'   => 'required|string|max:100',

            'problem_date'  => 'nullable|date',
            'rfu_date'      => 'nullable|date',
            'problem'       => 'nullable|string',
            'action'        => 'nullable|string',
            'partner'       => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();

        try {
            // Update Array Checkbox ke String
            if ($request->has('job_types') && is_array($request->job_types)) {
                $validated['job_type'] = implode(', ', $request->job_types);
            } else {
                $validated['job_type'] = null;
            }

            $battery->update($validated);

            // Sync Install Parts
            $battery->installParts()->delete();
            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $battery->installParts()->create([
                            'part_number' => $request->inst_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->inst_qty[$key] ?? 1,
                            'remarks'     => $request->inst_remarks[$key] ?? null,
                            'no_job'      => $request->inst_no_job[$key] ?? null,
                            'no_pr'       => $request->inst_no_pr[$key] ?? null,
                        ]);
                    }
                }
            }

            // Sync Recommendations
            $battery->recommendations()->delete();
            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $battery->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->rec_qty[$key] ?? 1,
                            'remarks'     => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('batteries.show', $battery->id)->with('success', 'Data Battery berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui data: ' . $e->getMessage()]);
        }
    }
}
