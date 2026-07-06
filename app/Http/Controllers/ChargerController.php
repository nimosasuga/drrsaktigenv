<?php
// app/Http/Controllers/ChargerController.php

namespace App\Http\Controllers;

use App\Models\Charger;
use App\Models\User;
use App\Models\UnitAsset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChargerController extends Controller
{
    private function canEditCharger($charger)
    {
        $user = Auth::user();
        $role = $user->role ?? $user->status_user;
        $privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];

        return ($user->id === $charger->user_id) || in_array($role, $privilegedRoles, true);
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
        return "Serial Number {$serialNumber} tidak bisa digunakan untuk Management Charger karena status unit asset tidak aktif.";
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

    private function countRfu($chargers): int
    {
        return $chargers->filter(fn($charger) => $this->isRfuStatus($charger->status_unit))->count();
    }

    private function countBreakdown($chargers): int
    {
        return $chargers->filter(fn($charger) => $this->isBreakdownStatus($charger->status_unit))->count();
    }

    public function index(Request $request)
    {
        $query = Charger::with('user');
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
                    ->orWhere('sn_charger', 'like', "%{$search}%")
                    ->orWhere('charger_type', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('pic', 'like', "%{$search}%")
                    ->orWhere('category_job', 'like', "%{$search}%")
                    ->orWhere('problem', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $chargers = $query->orderByDesc('date')->orderByDesc('id')->get();

        $summary = [
            'total_jobs' => $chargers->count(),
            'unique_chargers' => $chargers->pluck('sn_charger')->filter()->unique()->count(),
            'unique_units' => $chargers->pluck('serial_number')->filter()->unique()->count(),
            'total_rfu' => $this->countRfu($chargers),
            'total_breakdown' => $this->countBreakdown($chargers),
            'total_categories' => $chargers->pluck('category_job')->filter()->unique()->count(),
        ];

        $groupedChargers = $chargers
            ->groupBy(fn($charger) => $charger->date ? Carbon::parse($charger->date)->translatedFormat('F Y') : 'Tanpa Tanggal')
            ->map(function ($monthChargers, $monthName) {
                return [
                    'name' => $monthName,
                    'total' => $monthChargers->count(),
                    'pic_total' => $monthChargers->pluck('pic')->filter()->unique()->count(),
                    'charger_total' => $monthChargers->pluck('sn_charger')->filter()->unique()->count(),
                    'customer_location_total' => $monthChargers->unique(fn($charger) => ($charger->customer ?: 'Tanpa Customer') . '|' . ($charger->location ?: 'Tanpa Lokasi'))->count(),
                    'rfu_total' => $this->countRfu($monthChargers),
                    'breakdown_total' => $this->countBreakdown($monthChargers),
                    'pics' => $monthChargers
                        ->groupBy(fn($charger) => $charger->pic ?: 'Tanpa PIC')
                        ->map(function ($picChargers, $picName) {
                            return [
                                'name' => $picName,
                                'total' => $picChargers->count(),
                                'charger_total' => $picChargers->pluck('sn_charger')->filter()->unique()->count(),
                                'customer_location_total' => $picChargers->unique(fn($charger) => ($charger->customer ?: 'Tanpa Customer') . '|' . ($charger->location ?: 'Tanpa Lokasi'))->count(),
                                'rfu_total' => $this->countRfu($picChargers),
                                'breakdown_total' => $this->countBreakdown($picChargers),
                                'customer_locations' => $picChargers
                                    ->groupBy(fn($charger) => ($charger->customer ?: 'Tanpa Customer') . ' / ' . ($charger->location ?: 'Tanpa Lokasi'))
                                    ->map(function ($locationChargers, $customerLocationName) {
                                        return [
                                            'name' => $customerLocationName,
                                            'total' => $locationChargers->count(),
                                            'charger_total' => $locationChargers->pluck('sn_charger')->filter()->unique()->count(),
                                            'unit_total' => $locationChargers->pluck('serial_number')->filter()->unique()->count(),
                                            'rfu_total' => $this->countRfu($locationChargers),
                                            'breakdown_total' => $this->countBreakdown($locationChargers),
                                            'chargers' => $locationChargers->values(),
                                        ];
                                    }),
                            ];
                        }),
                ];
            });

        $customers = Charger::whereNotNull('customer')->where('customer', '!=', '')->select('customer')->distinct()->orderBy('customer')->pluck('customer');
        $pics = Charger::whereNotNull('pic')->where('pic', '!=', '')->select('pic')->distinct()->orderBy('pic')->pluck('pic');
        $locations = Charger::whereNotNull('location')->where('location', '!=', '')->select('location')->distinct()->orderBy('location')->pluck('location');
        $statuses = Charger::whereNotNull('status_unit')->where('status_unit', '!=', '')->select('status_unit')->distinct()->orderBy('status_unit')->pluck('status_unit');
        $categories = Charger::whereNotNull('category_job')->where('category_job', '!=', '')->select('category_job')->distinct()->orderBy('category_job')->pluck('category_job');
        $years = Charger::whereNotNull('date')->selectRaw('YEAR(date) as year')->distinct()->orderByDesc('year')->pluck('year')->filter()->values();

        return view('chargers.index', compact('groupedChargers', 'summary', 'customers', 'pics', 'locations', 'statuses', 'categories', 'years', 'selectedYear'));
    }

    public function create()
    {
        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';
        $partners = User::where('branch', $branch)->where('id', '!=', $user->id)->get(['id', 'name']);

        return view('chargers.create', compact('user', 'branch', 'partners'));
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
            'sn_charger' => 'required|string|max:100',
            'charger_type' => 'required|string|max:100',
            'charger_year' => 'nullable|integer',
            'category_job' => 'required|string|max:100',
            'status_unit' => 'required|string|max:100',
            'problem_date' => 'nullable|date',
            'rfu_date' => 'nullable|date',
            'problem' => 'nullable|string',
            'action' => 'nullable|string',
            'partner' => 'nullable|string|max:150',
            'job_types' => 'nullable|array',
            'job_types.*' => 'nullable|string|max:100',
        ]);

        if ($blockedResponse = $this->rejectIfAssetWithdrawn($validated['serial_number'])) {
            return $blockedResponse;
        }

        DB::beginTransaction();

        try {
            $charger = new Charger($validated);
            $charger->user_id = Auth::id();
            $charger->branch = Auth::user()->branch ?? 'HO / Pusat';
            $charger->pic = Auth::user()->name;
            $charger->status_mekanik = Auth::user()->role ?? Auth::user()->status_user;

            if ($request->has('job_types') && is_array($request->job_types)) {
                $charger->job_type = implode(', ', $request->job_types);
            }

            $charger->save();

            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $charger->installParts()->create([
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
                        $charger->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name' => $part_name,
                            'qty' => $request->rec_qty[$key] ?? 1,
                            'remarks' => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('chargers.show', $charger->id)->with('success', 'Data Management Charger berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $charger = Charger::with(['user', 'installParts', 'recommendations'])->findOrFail($id);
        return view('chargers.show', compact('charger'));
    }

    public function edit($id)
    {
        $charger = Charger::with(['installParts', 'recommendations'])->findOrFail($id);

        if (!$this->canEditCharger($charger)) {
            return redirect()->route('chargers.show', $charger->id)->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit data charger ini.']);
        }

        $user = Auth::user();
        $branch = $charger->branch ?? $user->branch ?? 'HO / Pusat';
        $partners = User::where('branch', $branch)->where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('chargers.edit', compact('charger', 'user', 'branch', 'partners'));
    }

    public function update(Request $request, $id)
    {
        $charger = Charger::with(['installParts', 'recommendations'])->findOrFail($id);

        if (!$this->canEditCharger($charger)) {
            return redirect()->route('chargers.show', $charger->id)->withErrors(['error' => 'Anda tidak memiliki izin untuk mengubah data charger ini.']);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'in_time' => 'nullable|date_format:H:i',
            'out_time' => 'nullable|date_format:H:i',
            'vehicle' => 'required|string|max:150',
            'nopol' => 'required|string|max:100',
            'customer' => 'required|string|max:150',
            'location' => 'required|string|max:150',
            'unit_type' => 'required|string|max:100',
            'serial_number' => 'required|string|max:100',
            'sn_charger' => 'required|string|max:100',
            'charger_type' => 'required|string|max:100',
            'charger_year' => 'nullable|integer|min:1900|max:2100',
            'category_job' => 'required|string|max:100',
            'status_unit' => 'required|string|max:100',
            'problem_date' => 'nullable|date',
            'rfu_date' => 'nullable|date',
            'problem' => 'nullable|string',
            'action' => 'nullable|string',
            'partner' => 'nullable|string|max:150',
            'job_types' => 'required|array|min:1',
            'job_types.*' => 'required|string|max:100',
        ]);

        if ($blockedResponse = $this->rejectIfAssetWithdrawn($validated['serial_number'])) {
            return $blockedResponse;
        }

        DB::beginTransaction();

        try {
            $charger->date = $validated['date'];
            $charger->in_time = $validated['in_time'] ?? null;
            $charger->out_time = $validated['out_time'] ?? null;
            $charger->vehicle = $validated['vehicle'];
            $charger->nopol = $validated['nopol'];
            $charger->customer = $validated['customer'];
            $charger->location = $validated['location'];
            $charger->unit_type = $validated['unit_type'];
            $charger->serial_number = $validated['serial_number'];
            $charger->sn_charger = $validated['sn_charger'];
            $charger->charger_type = $validated['charger_type'];
            $charger->charger_year = $validated['charger_year'] ?? null;
            $charger->category_job = $validated['category_job'];
            $charger->job_type = implode(', ', $validated['job_types']);
            $charger->status_unit = $validated['status_unit'];
            $charger->problem_date = $validated['problem_date'] ?? null;
            $charger->rfu_date = $validated['rfu_date'] ?? null;
            $charger->problem = $validated['problem'] ?? null;
            $charger->action = $validated['action'] ?? null;
            $charger->partner = $validated['partner'] ?? null;
            $charger->save();

            $charger->installParts()->delete();
            if ($request->has('inst_part_name') && is_array($request->inst_part_name)) {
                foreach ($request->inst_part_name as $key => $partName) {
                    $partNumber = $request->inst_part_number[$key] ?? null;
                    $partName = $partName ? trim($partName) : null;

                    if (empty($partNumber) && empty($partName)) {
                        continue;
                    }

                    $charger->installParts()->create([
                        'part_number' => $partNumber,
                        'part_name' => $partName,
                        'qty' => $request->inst_qty[$key] ?? 1,
                        'remarks' => $request->inst_remarks[$key] ?? null,
                        'no_job' => $request->inst_no_job[$key] ?? null,
                        'no_pr' => $request->inst_no_pr[$key] ?? null,
                    ]);
                }
            }

            $charger->recommendations()->delete();
            if ($request->has('rec_part_name') && is_array($request->rec_part_name)) {
                foreach ($request->rec_part_name as $key => $partName) {
                    $partNumber = $request->rec_part_number[$key] ?? null;
                    $partName = $partName ? trim($partName) : null;

                    if (empty($partNumber) && empty($partName)) {
                        continue;
                    }

                    $charger->recommendations()->create([
                        'part_number' => $partNumber,
                        'part_name' => $partName,
                        'qty' => $request->rec_qty[$key] ?? 1,
                        'remarks' => $request->rec_remarks[$key] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('chargers.show', $charger->id)->with('success', 'Data Management Charger berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui data charger: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $charger = Charger::findOrFail($id);

        if (!$this->canEditCharger($charger)) {
            return redirect()->route('chargers.show', $charger->id)->withErrors(['error' => 'Anda tidak memiliki izin untuk menghapus data charger ini.']);
        }

        $charger->delete();

        return redirect()->route('chargers.index')->with('success', 'Data Management Charger berhasil dihapus.');
    }
}
