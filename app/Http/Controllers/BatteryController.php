<?php
// app/Http/Controllers/BatteryController.php

namespace App\Http\Controllers;

use App\Models\Battery;
use App\Models\User;
use App\Models\UnitAsset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatteryController extends Controller
{
    private function canEditBattery($battery)
    {
        $user = Auth::user();
        $role = $user->role ?? $user->status_user;
        $privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];

        return ($user->id === $battery->user_id) || in_array($role, $privilegedRoles, true);
    }

    private function isRfuStatus($status): bool
    {
        return strtoupper(trim((string) $status)) === 'RFU';
    }

    private function isBreakdownStatus($status): bool
    {
        $normalized = strtoupper(trim((string) $status));

        return in_array($normalized, ['B/D', 'BD', 'BREAKDOWN'], true)
            || str_contains($normalized, 'BREAKDOWN');
    }

    private function isWithdrawnAssetStatus($status): bool
    {
        return in_array(strtoupper(trim((string) $status)), UnitAsset::inactiveStatusValues(), true);
    }

    private function assetWithdrawnError(string $serialNumber): string
    {
        return "Serial Number {$serialNumber} tidak bisa digunakan untuk Management Battery karena status unit asset tidak aktif.";
    }

    private function rejectIfAssetWithdrawn(string $serialNumber)
    {
        $asset = UnitAsset::where('serial_number', strtoupper(trim($serialNumber)))->first();

        if ($asset && $this->isWithdrawnAssetStatus($asset->status ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['error' => $this->assetWithdrawnError($serialNumber)]);
        }

        return null;
    }

    private function countRfu($batteries): int
    {
        return $batteries->filter(fn($battery) => $this->isRfuStatus($battery->status_unit))->count();
    }

    private function countBreakdown($batteries): int
    {
        return $batteries->filter(fn($battery) => $this->isBreakdownStatus($battery->status_unit))->count();
    }

    public function index(Request $request)
    {
        $query = Battery::with('user');
        $selectedYear = (int) $request->input('year_filter', now()->year);

        if ($request->filled('month_filter')) {
            $parts = explode('-', $request->month_filter);

            if (count($parts) === 2) {
                $query->whereYear('date', $parts[0])->whereMonth('date', $parts[1]);
            }
        } else {
            $query->whereYear('date', $selectedYear);
        }

        if ($request->filled('customer_filter')) {
            $query->where('customer', $request->customer_filter);
        }

        if ($request->filled('pic_filter')) {
            $query->where('pic', $request->pic_filter);
        }

        if ($request->filled('location_filter')) {
            $query->where('location', $request->location_filter);
        }

        if ($request->filled('status_filter')) {
            $query->where('status_unit', $request->status_filter);
        }

        if ($request->filled('category_filter')) {
            $query->where('category_job', $request->category_filter);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('sn_battery', 'like', "%{$search}%")
                    ->orWhere('battery_type', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('pic', 'like', "%{$search}%")
                    ->orWhere('category_job', 'like', "%{$search}%")
                    ->orWhere('problem', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $batteries = $query->orderByDesc('date')->orderByDesc('id')->get();

        $summary = [
            'total_jobs' => $batteries->count(),
            'unique_batteries' => $batteries->pluck('sn_battery')->filter()->unique()->count(),
            'unique_units' => $batteries->pluck('serial_number')->filter()->unique()->count(),
            'total_rfu' => $this->countRfu($batteries),
            'total_breakdown' => $this->countBreakdown($batteries),
            'total_categories' => $batteries->pluck('category_job')->filter()->unique()->count(),
        ];

        $groupedBatteries = $batteries
            ->groupBy(fn($battery) => $battery->date ? Carbon::parse($battery->date)->translatedFormat('F Y') : 'Tanpa Tanggal')
            ->map(function ($monthBatteries, $monthName) {
                return [
                    'name' => $monthName,
                    'total' => $monthBatteries->count(),
                    'pic_total' => $monthBatteries->pluck('pic')->filter()->unique()->count(),
                    'battery_total' => $monthBatteries->pluck('sn_battery')->filter()->unique()->count(),
                    'customer_location_total' => $monthBatteries->unique(fn($battery) => ($battery->customer ?: 'Tanpa Customer') . '|' . ($battery->location ?: 'Tanpa Lokasi'))->count(),
                    'rfu_total' => $this->countRfu($monthBatteries),
                    'breakdown_total' => $this->countBreakdown($monthBatteries),
                    'pics' => $monthBatteries
                        ->groupBy(fn($battery) => $battery->pic ?: 'Tanpa PIC')
                        ->map(function ($picBatteries, $picName) {
                            return [
                                'name' => $picName,
                                'total' => $picBatteries->count(),
                                'battery_total' => $picBatteries->pluck('sn_battery')->filter()->unique()->count(),
                                'customer_location_total' => $picBatteries->unique(fn($battery) => ($battery->customer ?: 'Tanpa Customer') . '|' . ($battery->location ?: 'Tanpa Lokasi'))->count(),
                                'rfu_total' => $this->countRfu($picBatteries),
                                'breakdown_total' => $this->countBreakdown($picBatteries),
                                'customer_locations' => $picBatteries
                                    ->groupBy(fn($battery) => ($battery->customer ?: 'Tanpa Customer') . ' / ' . ($battery->location ?: 'Tanpa Lokasi'))
                                    ->map(function ($locationBatteries, $customerLocationName) {
                                        return [
                                            'name' => $customerLocationName,
                                            'total' => $locationBatteries->count(),
                                            'battery_total' => $locationBatteries->pluck('sn_battery')->filter()->unique()->count(),
                                            'unit_total' => $locationBatteries->pluck('serial_number')->filter()->unique()->count(),
                                            'rfu_total' => $this->countRfu($locationBatteries),
                                            'breakdown_total' => $this->countBreakdown($locationBatteries),
                                            'batteries' => $locationBatteries->values(),
                                        ];
                                    }),
                            ];
                        }),
                ];
            });

        $customers = Battery::whereNotNull('customer')->where('customer', '!=', '')->select('customer')->distinct()->orderBy('customer')->pluck('customer');
        $pics = Battery::whereNotNull('pic')->where('pic', '!=', '')->select('pic')->distinct()->orderBy('pic')->pluck('pic');
        $locations = Battery::whereNotNull('location')->where('location', '!=', '')->select('location')->distinct()->orderBy('location')->pluck('location');
        $statuses = Battery::whereNotNull('status_unit')->where('status_unit', '!=', '')->select('status_unit')->distinct()->orderBy('status_unit')->pluck('status_unit');
        $categories = Battery::whereNotNull('category_job')->where('category_job', '!=', '')->select('category_job')->distinct()->orderBy('category_job')->pluck('category_job');
        $years = Battery::whereNotNull('date')->selectRaw('YEAR(date) as year')->distinct()->orderByDesc('year')->pluck('year')->filter()->values();

        return view('batteries.index', compact('groupedBatteries', 'summary', 'customers', 'pics', 'locations', 'statuses', 'categories', 'years', 'selectedYear'));
    }

    public function create()
    {
        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';
        $partners = User::where('branch', $branch)->where('id', '!=', $user->id)->get(['id', 'name']);

        return view('batteries.create', compact('user', 'branch', 'partners'));
    }

    public function searchAssets(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $assets = UnitAsset::where(function ($query) use ($search) {
            $query->where('serial_number', 'LIKE', "%{$search}%")
                ->orWhere('unit_type', 'LIKE', "%{$search}%")
                ->orWhere('customer', 'LIKE', "%{$search}%")
                ->orWhere('location', 'LIKE', "%{$search}%");
        })
            ->whereRaw(UnitAsset::activeStatusSql())
            ->take(10)
            ->get();

        return response()->json($assets->map(fn($asset) => [
            'serial_number' => $asset->serial_number,
            'unit_type' => $asset->unit_type ?? $asset->unit_model ?? $asset->tipe_unit ?? '',
            'customer' => $asset->customer ?? $asset->nama_pelanggan ?? '',
            'location' => $asset->location ?? $asset->lokasi ?? '',
            'status' => $asset->status ?? '',
        ]));
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
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $battery->installParts()->create([
                            'part_number' => $request->inst_part_number[$key] ?? null,
                            'part_name' => $part_name,
                            'qty' => $request->inst_qty[$key] ?? 1,
                            'remarks' => $request->inst_remarks[$key] ?? null,
                            'no_job' => $request->inst_no_job[$key] ?? null,
                            'no_pr' => $request->inst_no_pr[$key] ?? null,
                        ]);
                    }
                }
            }

            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $battery->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name' => $part_name,
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

    public function edit($id)
    {
        $battery = Battery::with(['installParts', 'recommendations'])->findOrFail($id);

        if (!$this->canEditBattery($battery)) {
            return redirect()->route('batteries.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit data milik teknisi lain.']);
        }

        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';
        $partners = User::where('branch', $branch)->where('id', '!=', $user->id)->get(['id', 'name']);

        return view('batteries.edit', compact('battery', 'user', 'branch', 'partners'));
    }

    public function update(Request $request, $id)
    {
        $battery = Battery::findOrFail($id);

        if (!$this->canEditBattery($battery)) {
            return redirect()->route('batteries.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit data milik teknisi lain.']);
        }

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
            if ($request->has('job_types') && is_array($request->job_types)) {
                $validated['job_type'] = implode(', ', $request->job_types);
            } else {
                $validated['job_type'] = null;
            }

            $battery->update($validated);

            $battery->installParts()->delete();
            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $battery->installParts()->create([
                            'part_number' => $request->inst_part_number[$key] ?? null,
                            'part_name' => $part_name,
                            'qty' => $request->inst_qty[$key] ?? 1,
                            'remarks' => $request->inst_remarks[$key] ?? null,
                            'no_job' => $request->inst_no_job[$key] ?? null,
                            'no_pr' => $request->inst_no_pr[$key] ?? null,
                        ]);
                    }
                }
            }

            $battery->recommendations()->delete();
            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $battery->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name' => $part_name,
                            'qty' => $request->rec_qty[$key] ?? 1,
                            'remarks' => $request->rec_remarks[$key] ?? null,
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
