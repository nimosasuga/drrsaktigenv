<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/DeliveryController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\UnitAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    private function canCreateDelivery(): bool
    {
        $user = Auth::user();
        $statusUser = strtoupper((string) ($user->status_user ?? $user->role ?? ''));

        return !str_contains($statusUser, 'PLANNER');
    }

    private function canEditDelivery(Delivery $delivery): bool
    {
        $user = Auth::user();
        $role = $user->role ?? $user->status_user;
        $privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];

        if (in_array($role, $privilegedRoles)) {
            return true;
        }

        return $delivery->pic === $user->name || $delivery->user_id === $user->id;
    }

    private function generateDeliveryCode(): string
    {
        $datePrefix = now()->format('Ymd');
        $prefix = 'DL-' . $datePrefix . '-';

        $lastDelivery = Delivery::where('delivery_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastDelivery) {
            $lastNumber = (int) str_replace($prefix, '', $lastDelivery->delivery_code);
            $nextNumber = $lastNumber + 1;
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

    public function index(Request $request)
    {
        $query = Delivery::with('user')->latest();

        if ($request->filled('month_filter')) {
            $parts = explode('-', $request->month_filter);

            if (count($parts) === 2) {
                $query->whereYear('date', $parts[0])
                    ->whereMonth('date', $parts[1]);
            }
        }

        if ($request->filled('customer_filter')) {
            $query->where('customer', $request->customer_filter);
        }

        if ($request->filled('status_filter')) {
            $query->where('status_unit', $request->status_filter);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('delivery_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('pic', 'like', "%{$search}%");
            });
        }

        $deliveries = $query->paginate(20)->withQueryString();

        $customers = Delivery::whereNotNull('customer')
            ->where('customer', '!=', '')
            ->select('customer')
            ->distinct()
            ->orderBy('customer')
            ->pluck('customer');

        return view('deliveries.index', compact('deliveries', 'customers'));
    }

    public function create()
    {
        if (!$this->canCreateDelivery()) {
            return redirect()
                ->route('deliveries.index')
                ->withErrors(['error' => 'Anda tidak memiliki permission untuk create delivery.']);
        }

        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';

        $partners = User::where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('deliveries.create', compact('user', 'branch', 'partners'));
    }

    public function searchAssets(Request $request)
    {
        $search = $request->get('q');

        if (empty($search)) {
            return response()->json([]);
        }

        $assets = UnitAsset::where('serial_number', 'like', "%{$search}%")
            ->orWhere('unit_type', 'like', "%{$search}%")
            ->orWhere('customer', 'like', "%{$search}%")
            ->take(10)
            ->get();

        $mapped = $assets->map(function ($asset) {
            return [
                'serial_number' => $asset->serial_number,
                'unit_type' => $asset->unit_type ?? $asset->unit_model ?? $asset->tipe_unit ?? '',
                'year' => $asset->year ?? '',
                'customer' => $asset->customer ?? $asset->nama_pelanggan ?? '',
                'location' => $asset->location ?? $asset->lokasi ?? '',
            ];
        });

        return response()->json($mapped);
    }

    public function store(Request $request)
    {
        if (!$this->canCreateDelivery()) {
            return redirect()
                ->route('deliveries.index')
                ->withErrors(['error' => 'Anda tidak memiliki permission untuk create delivery.']);
        }

        $validated = $request->validate([
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
            'hour_meter' => 'nullable|integer|min:0',

            'status_unit' => 'required|string|in:RFU,BREAKDOWN',

            'battery_type' => 'nullable|string|max:150',
            'battery_sn' => 'nullable|string|max:150',
            'charger_type' => 'nullable|string|max:150',
            'charger_sn' => 'nullable|string|max:150',
            'trolly' => 'nullable|string|max:150',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $delivery = new Delivery();
            $delivery->delivery_code = $this->generateDeliveryCode();
            $delivery->user_id = $user->id;
            $delivery->branch = $user->branch ?? 'HO / Pusat';
            $delivery->status_mekanik = $this->statusMekanikFromUser();
            $delivery->pic = $user->name;
            $delivery->partner = $validated['partner'] ?? null;

            $delivery->in_time = $validated['in_time'] ?? null;
            $delivery->out_time = $validated['out_time'] ?? null;
            $delivery->vehicle = strtoupper($validated['vehicle']);
            $delivery->nopol = strtoupper($validated['nopol']);
            $delivery->date = $validated['date'];

            $delivery->customer = strtoupper($validated['customer']);
            $delivery->location = isset($validated['location']) ? strtoupper($validated['location']) : null;
            $delivery->serial_number = strtoupper($validated['serial_number']);
            $delivery->unit_type = isset($validated['unit_type']) ? strtoupper($validated['unit_type']) : null;
            $delivery->year = $validated['year'] ?? null;
            $delivery->hour_meter = $validated['hour_meter'] ?? null;

            $delivery->job_type = 'DELIVERY UNIT';
            $delivery->status_unit = $validated['status_unit'];

            $delivery->battery_type = isset($validated['battery_type']) ? strtoupper($validated['battery_type']) : null;
            $delivery->battery_sn = isset($validated['battery_sn']) ? strtoupper($validated['battery_sn']) : null;
            $delivery->charger_type = isset($validated['charger_type']) ? strtoupper($validated['charger_type']) : null;
            $delivery->charger_sn = isset($validated['charger_sn']) ? strtoupper($validated['charger_sn']) : null;
            $delivery->trolly = isset($validated['trolly']) ? strtoupper($validated['trolly']) : null;
            $delivery->note = $validated['note'] ?? null;

            $delivery->save();

            DB::commit();

            return redirect()
                ->route('deliveries.show', $delivery->id)
                ->with('success', 'Data Delivery Unit berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan data delivery: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $delivery = Delivery::with('user')->findOrFail($id);

        return view('deliveries.show', compact('delivery'));
    }

    public function edit($id)
    {
        $delivery = Delivery::findOrFail($id);

        if (!$this->canEditDelivery($delivery)) {
            return redirect()
                ->route('deliveries.show', $delivery->id)
                ->withErrors(['error' => 'Anda hanya bisa edit record delivery yang Anda buat sebagai PIC.']);
        }

        $user = Auth::user();
        $branch = $delivery->branch ?? $user->branch ?? 'HO / Pusat';

        $partners = User::where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('deliveries.edit', compact('delivery', 'user', 'branch', 'partners'));
    }

    public function update(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);

        if (!$this->canEditDelivery($delivery)) {
            return redirect()
                ->route('deliveries.show', $delivery->id)
                ->withErrors(['error' => 'Anda hanya bisa edit record delivery yang Anda buat sebagai PIC.']);
        }

        $validated = $request->validate([
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
            'hour_meter' => 'nullable|integer|min:0',

            'status_unit' => 'required|string|in:RFU,BREAKDOWN',

            'battery_type' => 'nullable|string|max:150',
            'battery_sn' => 'nullable|string|max:150',
            'charger_type' => 'nullable|string|max:150',
            'charger_sn' => 'nullable|string|max:150',
            'trolly' => 'nullable|string|max:150',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $delivery->partner = $validated['partner'] ?? null;

            $delivery->in_time = $validated['in_time'] ?? null;
            $delivery->out_time = $validated['out_time'] ?? null;
            $delivery->vehicle = strtoupper($validated['vehicle']);
            $delivery->nopol = strtoupper($validated['nopol']);
            $delivery->date = $validated['date'];

            $delivery->customer = strtoupper($validated['customer']);
            $delivery->location = isset($validated['location']) ? strtoupper($validated['location']) : null;
            $delivery->serial_number = strtoupper($validated['serial_number']);
            $delivery->unit_type = isset($validated['unit_type']) ? strtoupper($validated['unit_type']) : null;
            $delivery->year = $validated['year'] ?? null;
            $delivery->hour_meter = $validated['hour_meter'] ?? null;

            $delivery->job_type = 'DELIVERY UNIT';
            $delivery->status_unit = $validated['status_unit'];

            $delivery->battery_type = isset($validated['battery_type']) ? strtoupper($validated['battery_type']) : null;
            $delivery->battery_sn = isset($validated['battery_sn']) ? strtoupper($validated['battery_sn']) : null;
            $delivery->charger_type = isset($validated['charger_type']) ? strtoupper($validated['charger_type']) : null;
            $delivery->charger_sn = isset($validated['charger_sn']) ? strtoupper($validated['charger_sn']) : null;
            $delivery->trolly = isset($validated['trolly']) ? strtoupper($validated['trolly']) : null;
            $delivery->note = $validated['note'] ?? null;

            $delivery->save();

            DB::commit();

            return redirect()
                ->route('deliveries.show', $delivery->id)
                ->with('success', 'Data Delivery Unit berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui data delivery: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $delivery = Delivery::findOrFail($id);

        if (!$this->canEditDelivery($delivery)) {
            return redirect()
                ->route('deliveries.show', $delivery->id)
                ->withErrors(['error' => 'Anda hanya bisa hapus record delivery yang Anda buat sebagai PIC.']);
        }

        $delivery->delete();

        return redirect()
            ->route('deliveries.index')
            ->with('success', 'Data Delivery Unit berhasil dihapus.');
    }
}
