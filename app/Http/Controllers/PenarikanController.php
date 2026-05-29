<?php
// PATH FILE: app/Http/Controllers/PenarikanController.php

namespace App\Http\Controllers;

use App\Models\UnitAsset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenarikanController extends Controller
{
    private function canCreatePenarikan(): bool
    {
        $user = Auth::user();
        $statusUser = strtoupper((string) ($user->status_user ?? $user->role ?? ''));

        return !str_contains($statusUser, 'PLANNER');
    }

    private function canEditPenarikan(object $penarikan): bool
    {
        $user = Auth::user();
        $role = $user->role ?? $user->status_user;
        $privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];

        if (in_array($role, $privilegedRoles, true)) {
            return true;
        }

        return ($penarikan->pic === $user->name) || ((int) $penarikan->user_id === (int) $user->id);
    }

    private function isRfuStatus($status): bool
    {
        return strtoupper(trim((string) $status)) === 'RFU';
    }

    private function isBreakdownStatus($status): bool
    {
        $normalized = strtoupper(trim((string) $status));

        return in_array($normalized, ['B/D', 'BD', 'BREAKDOWN'], true) || str_contains($normalized, 'BREAKDOWN');
    }

    private function countRfu($items): int
    {
        return $items->filter(fn($item) => $this->isRfuStatus($item->status_unit))->count();
    }

    private function countBreakdown($items): int
    {
        return $items->filter(fn($item) => $this->isBreakdownStatus($item->status_unit))->count();
    }

    private function generatePenarikanCode(): string
    {
        $datePrefix = now()->format('Ymd');
        $prefix = 'TK-' . $datePrefix . '-';

        $lastCode = DB::table('penarikans')
            ->where('penarikan_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('penarikan_code');

        $nextNumber = 1;

        if ($lastCode) {
            $nextNumber = ((int) str_replace($prefix, '', $lastCode)) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function statusMekanikFromUser(): string
    {
        $user = Auth::user();
        $statusUser = strtoupper((string) ($user->status_user ?? $user->role ?? ''));

        if (str_contains($statusUser, 'FIELD SERVICE')) {
            return 'Field Service';
        }

        if (str_contains($statusUser, 'FMC')) {
            return 'FMC';
        }

        return $user->status_user ?? $user->role ?? 'Field Service';
    }

    private function markAssetAsDitarik(?string $serialNumber): void
    {
        $serialNumber = strtoupper(trim((string) $serialNumber));

        if ($serialNumber === '') {
            return;
        }

        UnitAsset::where('serial_number', $serialNumber)->update([
            'status' => 'DITARIK',
            'updated_at' => now(),
        ]);
    }

    public function index(Request $request)
    {
        $query = DB::table('penarikans');
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

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('penarikan_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('pic', 'like', "%{$search}%")
                    ->orWhere('vehicle', 'like', "%{$search}%")
                    ->orWhere('nopol', 'like', "%{$search}%")
                    ->orWhere('battery_sn', 'like', "%{$search}%")
                    ->orWhere('battery_sn_2', 'like', "%{$search}%")
                    ->orWhere('charger_sn', 'like', "%{$search}%");
            });
        }

        $penarikans = $query->orderByDesc('date')->orderByDesc('id')->get();

        $summary = [
            'total_penarikans' => $penarikans->count(),
            'unique_units' => $penarikans->pluck('serial_number')->filter()->unique()->count(),
            'total_rfu' => $this->countRfu($penarikans),
            'total_breakdown' => $this->countBreakdown($penarikans),
            'total_customers' => $penarikans->pluck('customer')->filter()->unique()->count(),
        ];

        $groupedPenarikans = $penarikans
            ->groupBy(fn($item) => $item->date ? Carbon::parse($item->date)->translatedFormat('F Y') : 'Tanpa Tanggal')
            ->map(function ($monthItems, $monthName) {
                return [
                    'name' => $monthName,
                    'total' => $monthItems->count(),
                    'pic_total' => $monthItems->pluck('pic')->filter()->unique()->count(),
                    'unit_total' => $monthItems->pluck('serial_number')->filter()->unique()->count(),
                    'customer_location_total' => $monthItems->unique(fn($item) => ($item->customer ?: 'Tanpa Customer') . '|' . ($item->location ?: 'Tanpa Lokasi'))->count(),
                    'rfu_total' => $this->countRfu($monthItems),
                    'breakdown_total' => $this->countBreakdown($monthItems),
                    'pics' => $monthItems
                        ->groupBy(fn($item) => $item->pic ?: 'Tanpa PIC')
                        ->map(function ($picItems, $picName) {
                            return [
                                'name' => $picName,
                                'total' => $picItems->count(),
                                'unit_total' => $picItems->pluck('serial_number')->filter()->unique()->count(),
                                'customer_location_total' => $picItems->unique(fn($item) => ($item->customer ?: 'Tanpa Customer') . '|' . ($item->location ?: 'Tanpa Lokasi'))->count(),
                                'rfu_total' => $this->countRfu($picItems),
                                'breakdown_total' => $this->countBreakdown($picItems),
                                'customer_locations' => $picItems
                                    ->groupBy(fn($item) => ($item->customer ?: 'Tanpa Customer') . ' / ' . ($item->location ?: 'Tanpa Lokasi'))
                                    ->map(function ($locationItems, $customerLocationName) {
                                        return [
                                            'name' => $customerLocationName,
                                            'total' => $locationItems->count(),
                                            'unit_total' => $locationItems->pluck('serial_number')->filter()->unique()->count(),
                                            'rfu_total' => $this->countRfu($locationItems),
                                            'breakdown_total' => $this->countBreakdown($locationItems),
                                            'penarikans' => $locationItems->values(),
                                        ];
                                    }),
                            ];
                        }),
                ];
            });

        $customers = DB::table('penarikans')->whereNotNull('customer')->where('customer', '!=', '')->distinct()->orderBy('customer')->pluck('customer');
        $pics = DB::table('penarikans')->whereNotNull('pic')->where('pic', '!=', '')->distinct()->orderBy('pic')->pluck('pic');
        $locations = DB::table('penarikans')->whereNotNull('location')->where('location', '!=', '')->distinct()->orderBy('location')->pluck('location');
        $statuses = DB::table('penarikans')->whereNotNull('status_unit')->where('status_unit', '!=', '')->distinct()->orderBy('status_unit')->pluck('status_unit');
        $years = DB::table('penarikans')->whereNotNull('date')->selectRaw('YEAR(date) as year')->distinct()->orderByDesc('year')->pluck('year')->filter()->values();

        return view('penarikans.index', compact('groupedPenarikans', 'summary', 'customers', 'pics', 'locations', 'statuses', 'years', 'selectedYear'));
    }

    public function create()
    {
        if (!$this->canCreatePenarikan()) {
            return redirect()->route('penarikans.index')->withErrors(['error' => 'Anda tidak memiliki permission untuk create penarikan.']);
        }

        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';
        $statusMekanik = $this->statusMekanikFromUser();
        $partners = User::where('branch', $branch)->where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('penarikans.create', compact('user', 'branch', 'statusMekanik', 'partners'));
    }

    public function searchAssets(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $assets = UnitAsset::where('serial_number', 'like', "%{$search}%")
            ->orWhere('unit_type', 'like', "%{$search}%")
            ->orWhere('customer', 'like', "%{$search}%")
            ->orWhere('location', 'like', "%{$search}%")
            ->limit(12)
            ->get();

        return response()->json($assets->map(fn($asset) => [
            'serial_number' => $asset->serial_number,
            'unit_type' => $asset->unit_type ?? $asset->unit_model ?? $asset->tipe_unit ?? '',
            'year' => $asset->year ?? '',
            'hour_meter' => $asset->hour_meter ?? '',
            'customer' => $asset->customer ?? $asset->nama_pelanggan ?? '',
            'location' => $asset->location ?? $asset->lokasi ?? '',
        ]));
    }

    public function store(Request $request)
    {
        if (!$this->canCreatePenarikan()) {
            return redirect()->route('penarikans.index')->withErrors(['error' => 'Anda tidak memiliki permission untuk create penarikan.']);
        }

        $validated = $this->validatePenarikan($request);
        $user = Auth::user();

        $validated['penarikan_code'] = $this->generatePenarikanCode();
        $validated['user_id'] = $user->id;
        $validated['branch'] = $user->branch ?? 'HO / Pusat';
        $validated['status_mekanik'] = $this->statusMekanikFromUser();
        $validated['pic'] = $user->name;
        $validated['job_type'] = 'TARIK UNIT';
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        $penarikanData = $this->normalizePenarikanData($validated);
        $id = DB::table('penarikans')->insertGetId($penarikanData);

        $this->markAssetAsDitarik($penarikanData['serial_number'] ?? null);

        return redirect()->route('penarikans.show', $id)->with('success', 'Data Penarikan Unit berhasil disimpan.');
    }

    public function show($id)
    {
        $penarikan = DB::table('penarikans')->where('id', $id)->first();
        abort_if(!$penarikan, 404);

        return view('penarikans.show', compact('penarikan'));
    }

    public function edit($id)
    {
        $penarikan = DB::table('penarikans')->where('id', $id)->first();
        abort_if(!$penarikan, 404);

        if (!$this->canEditPenarikan($penarikan)) {
            return redirect()->route('penarikans.show', $id)->withErrors(['error' => 'Anda hanya bisa edit record yang Anda buat sebagai PIC.']);
        }

        $user = Auth::user();
        $branch = $penarikan->branch ?? $user->branch ?? 'HO / Pusat';
        $statusMekanik = $penarikan->status_mekanik ?? $this->statusMekanikFromUser();
        $partners = User::where('branch', $branch)->where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('penarikans.edit', compact('penarikan', 'user', 'branch', 'statusMekanik', 'partners'));
    }

    public function update(Request $request, $id)
    {
        $penarikan = DB::table('penarikans')->where('id', $id)->first();
        abort_if(!$penarikan, 404);

        if (!$this->canEditPenarikan($penarikan)) {
            return redirect()->route('penarikans.show', $id)->withErrors(['error' => 'Anda hanya bisa edit record yang Anda buat sebagai PIC.']);
        }

        $validated = $this->validatePenarikan($request);
        $validated['job_type'] = 'TARIK UNIT';
        $validated['updated_at'] = now();

        $penarikanData = $this->normalizePenarikanData($validated);
        DB::table('penarikans')->where('id', $id)->update($penarikanData);

        $this->markAssetAsDitarik($penarikanData['serial_number'] ?? null);

        return redirect()->route('penarikans.show', $id)->with('success', 'Data Penarikan Unit berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $penarikan = DB::table('penarikans')->where('id', $id)->first();
        abort_if(!$penarikan, 404);

        if (!$this->canEditPenarikan($penarikan)) {
            return redirect()->route('penarikans.show', $id)->withErrors(['error' => 'Anda hanya bisa hapus record yang Anda buat sebagai PIC.']);
        }

        DB::table('penarikans')->where('id', $id)->delete();

        return redirect()->route('penarikans.index')->with('success', 'Data Penarikan Unit berhasil dihapus.');
    }

    private function validatePenarikan(Request $request): array
    {
        return $request->validate([
            'partner' => 'nullable|string|max:150',
            'in_time' => 'nullable|date_format:H:i',
            'out_time' => 'nullable|date_format:H:i',
            'vehicle' => 'required|string|max:150',
            'nopol' => 'required|string|max:100',
            'date' => 'required|date',
            'customer' => 'required|string|max:150',
            'location' => 'nullable|string|max:150',
            'serial_number' => 'required|string|max:100',
            'unit_type' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:2100',
            'hour_meter' => 'nullable|string|max:100',
            'status_unit' => 'required|string|in:RFU,BREAKDOWN',
            'battery_type' => 'nullable|string|max:150',
            'battery_sn' => 'nullable|string|max:150',
            'battery_type_2' => 'nullable|string|max:150',
            'battery_sn_2' => 'nullable|string|max:150',
            'charger_type' => 'nullable|string|max:150',
            'charger_sn' => 'nullable|string|max:150',
            'trolly' => 'nullable|string|max:150',
            'trolly_2' => 'nullable|string|max:150',
            'trolly_3' => 'nullable|string|max:150',
            'note' => 'nullable|string',
        ]);
    }

    private function normalizePenarikanData(array $data): array
    {
        foreach (
            [
                'vehicle',
                'nopol',
                'customer',
                'location',
                'serial_number',
                'unit_type',
                'battery_type',
                'battery_sn',
                'battery_type_2',
                'battery_sn_2',
                'charger_type',
                'charger_sn',
                'trolly',
                'trolly_2',
                'trolly_3'
            ] as $field
        ) {
            if (isset($data[$field]) && $data[$field] !== null) {
                $data[$field] = strtoupper((string) $data[$field]);
            }
        }

        return $data;
    }
}
