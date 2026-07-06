<?php
// PATH FILE: app/Http/Controllers/UnitAssetController.php

namespace App\Http\Controllers;

use App\Models\UnitAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class UnitAssetController extends Controller
{
    // Fungsi bantuan untuk memblokir Mekanik dari akses CRUD
    private function canManageAsset(): bool
    {
        $user = Auth::user();

        $role = strtolower((string) ($user->role ?? ''));
        $statusUser = strtolower((string) ($user->status_user ?? ''));

        $allowedRoles = [
            'super_admin',
            'admin',
            'koordinator',
            'sect_head',
        ];

        return in_array($role, $allowedRoles, true)
            || in_array($statusUser, $allowedRoles, true);
    }

    private function blockAssetMutation(): void
    {
        if (!$this->canManageAsset()) {
            abort(403, 'Akses ditolak. Anda hanya memiliki akses Read-Only pada Manajemen Aset.');
        }
    }

    private function assetStatusOptions(): array
    {
        return UnitAsset::statusOptions();
    }

    public function index(Request $request)
    {
        // MODE 1: TAMPILAN LIST UNIT
        // Dipakai saat user klik customer card dari halaman grouping.
        if ($request->filled('location') && $request->filled('customer')) {
            $location = $request->location;
            $customer = $request->customer;

            $query = UnitAsset::where('location', $location)
                ->where('customer', $customer);

            if ($request->filled('search')) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('serial_number', 'like', "%{$search}%")
                        ->orWhere('unit_type', 'like', "%{$search}%")
                        ->orWhere('jenis_unit', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('branch', 'like', "%{$search}%")
                        ->orWhere('delivery', 'like', "%{$search}%")
                        ->orWhere('supported_by', 'like', "%{$search}%")
                        ->orWhere('qr_token', 'like', "%{$search}%");
                });
            }

            if ($request->filled('filter_status')) {
                $query->where('status', $request->filter_status);
            }

            $assets = $query
                ->orderBy('serial_number')
                ->paginate(50)
                ->withQueryString();

            $statuses = UnitAsset::whereNotNull('status')
                ->where('status', '!=', '')
                ->select('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status');

            return view('assets.list', compact('assets', 'location', 'customer', 'statuses'));
        }

        // MODE 2: TAMPILAN GROUPING DEFAULT
        $query = UnitAsset::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('jenis_unit', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('branch', 'like', "%{$search}%")
                    ->orWhere('delivery', 'like', "%{$search}%")
                    ->orWhere('supported_by', 'like', "%{$search}%")
                    ->orWhere('qr_token', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_customer')) {
            $query->where('customer', $request->filter_customer);
        }

        if ($request->filled('filter_location')) {
            $query->where('location', $request->filter_location);
        }

        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        $allAssets = $query
            ->orderBy('location')
            ->orderBy('customer')
            ->orderBy('serial_number')
            ->get();

        $groupedData = [];

        foreach ($allAssets as $asset) {
            $loc = $asset->location ?: 'Tanpa Lokasi';
            $cust = $asset->customer ?: 'Tanpa Customer';

            if (!isset($groupedData[$loc])) {
                $groupedData[$loc] = [];
            }

            if (!isset($groupedData[$loc][$cust])) {
                $groupedData[$loc][$cust] = 0;
            }

            $groupedData[$loc][$cust]++;
        }

        ksort($groupedData);

        $customers = UnitAsset::whereNotNull('customer')
            ->where('customer', '!=', '')
            ->select('customer')
            ->distinct()
            ->orderBy('customer')
            ->pluck('customer');

        $locations = UnitAsset::whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $statuses = UnitAsset::whereNotNull('status')
            ->where('status', '!=', '')
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('assets.index', compact(
            'groupedData',
            'customers',
            'locations',
            'statuses'
        ));
    }

    public function create()
    {
        $this->blockAssetMutation(); // Proteksi Mutasi Aset
        return view('assets.create');
    }

    public function store(Request $request)
    {
        $this->blockAssetMutation(); // Proteksi Mutasi Aset

        $validated = $request->validate([
            'serial_number' => 'required|string|max:255|unique:unit_assets',
            'unit_type' => 'nullable|string|max:255',
            'jenis_unit' => 'nullable|string|max:255',
            'customer' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:4',
            'status' => 'required|string|in:' . implode(',', $this->assetStatusOptions()),
            'delivery' => 'nullable|string|max:255',
            'supported_by' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $validated['status'] = strtoupper(trim((string) $validated['status']));
        $validated['qr_token'] = Str::random(32);

        UnitAsset::create($validated);

        return redirect()->route('assets.index')->with('success', 'Data aset baru berhasil ditambahkan.');
    }

    public function show($id)
    {
        $asset = UnitAsset::findOrFail($id);
        $sn = $asset->serial_number;

        // ==============================================================
        // CORE ENGINE: MENGGABUNGKAN SEMUA HISTORI BERDASARKAN S/N
        // ==============================================================
        $timeline = collect();

        // 1. Data Update Jobs (Warna Biru)
        $jobs = \App\Models\Job::with(['recommendations', 'installParts'])
            ->where('serial_number', $sn)
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->get();

        foreach ($jobs as $j) {
            $timeline->push([
                'module' => 'Update Job',
                'date' => $j->work_date,
                'title' => $j->job_type ?? 'General Job',
                'pic' => $j->pic,
                'desc' => $j->problem ?? $j->action ?? 'Pekerjaan Mekanik',
                'route' => route('update-jobs.show', $j->id),
                'color_bg' => 'bg-blue-100',
                'color_text' => 'text-blue-700',
                'color_border' => 'border-blue-200'
            ]);
        }

        $latestJob = $jobs->first();
        $pmJobs = $jobs
            ->filter(function ($job) {
                $jobType = strtoupper(trim((string) ($job->job_type ?? '')));

                return $jobType === 'PM' || str_contains($jobType, 'PREVENTIVE MAINTENANCE');
            })
            ->values();

        $recommendationHistories = $jobs
            ->flatMap(function ($job) {
                return $job->recommendations->map(function ($part) use ($job) {
                    return [
                        'type' => 'recommendation',
                        'date' => $job->work_date,
                        'job' => $job,
                        'part' => $part,
                        'title' => $part->part_name ?: 'Rekomendasi Sparepart',
                        'description' => 'PN: ' . ($part->part_number ?: '-') . ' · Qty ' . number_format((int) ($part->qty ?? 0), 0, ',', '.'),
                    ];
                });
            })
            ->sortByDesc('date')
            ->values();

        $installPartHistories = $jobs
            ->flatMap(function ($job) {
                return $job->installParts->map(function ($part) use ($job) {
                    return [
                        'type' => 'install_part',
                        'date' => $job->work_date,
                        'job' => $job,
                        'part' => $part,
                        'title' => $part->part_name ?: 'Pemasangan Sparepart',
                        'description' => 'PN: ' . ($part->part_number ?: '-') . ' · Qty ' . number_format((int) ($part->qty ?? 0), 0, ',', '.'),
                    ];
                });
            })
            ->sortByDesc('date')
            ->values();

        $unitTimeline = collect()
            ->merge($jobs->map(function ($job) {
                return [
                    'type' => str_contains(strtoupper((string) $job->job_type), 'PREVENTIVE MAINTENANCE') ? 'pm' : 'job',
                    'date' => $job->work_date,
                    'title' => $job->job_type ?: 'Update Job',
                    'subtitle' => $job->pic ?: 'Tanpa PIC',
                    'description' => $job->problem ?: ($job->action ?: 'Pekerjaan unit'),
                    'status' => $job->status_unit,
                    'route' => route('update-jobs.show', $job->id),
                ];
            }))
            ->merge($recommendationHistories->map(function ($history) {
                return [
                    'type' => 'recommendation',
                    'date' => $history['date'],
                    'title' => $history['title'],
                    'subtitle' => 'Rekomendasi Sparepart',
                    'description' => $history['description'] . (filled($history['part']->remarks ?? null) ? ' · ' . $history['part']->remarks : ''),
                    'status' => 'Belum PM',
                    'route' => route('update-jobs.show', $history['job']->id),
                ];
            }))
            ->merge($installPartHistories->map(function ($history) {
                return [
                    'type' => 'install_part',
                    'date' => $history['date'],
                    'title' => $history['title'],
                    'subtitle' => 'Pemasangan Sparepart',
                    'description' => $history['description'] . (filled($history['part']->remarks ?? null) ? ' · ' . $history['part']->remarks : ''),
                    'status' => 'Sudah PM',
                    'route' => route('update-jobs.show', $history['job']->id),
                ];
            }))
            ->sortByDesc('date')
            ->values();

        $timelineStats = [
            'job_total' => $jobs->count(),
            'pm_total' => $pmJobs->count(),
            'recommendation_total' => $recommendationHistories->count(),
            'install_part_total' => $installPartHistories->count(),
        ];

        // 2. Data Batteries (Warna Emerald)
        $batteries = \App\Models\Battery::where('serial_number', $sn)->get();
        foreach ($batteries as $b) {
            $timeline->push([
                'module' => 'Battery',
                'date' => $b->date,
                'title' => $b->category_job ?? 'Battery Job',
                'pic' => $b->pic,
                'desc' => 'S/N Baterai: ' . ($b->sn_battery ?? '-') . ' | ' . ($b->problem ?? ''),
                'route' => route('batteries.show', $b->id),
                'color_bg' => 'bg-emerald-100',
                'color_text' => 'text-emerald-700',
                'color_border' => 'border-emerald-200'
            ]);
        }

        // 3. Data Chargers (Warna Amber)
        $chargers = \App\Models\Charger::where('serial_number', $sn)->get();
        foreach ($chargers as $c) {
            $timeline->push([
                'module' => 'Charger',
                'date' => $c->date,
                'title' => $c->category_job ?? 'Charger Job',
                'pic' => $c->pic,
                'desc' => 'S/N Charger: ' . ($c->sn_charger ?? '-') . ' | ' . ($c->problem ?? ''),
                'route' => route('chargers.show', $c->id),
                'color_bg' => 'bg-amber-100',
                'color_text' => 'text-amber-700',
                'color_border' => 'border-amber-200'
            ]);
        }

        // 4. Data Deliveries (Warna Purple)
        if (class_exists(\App\Models\Delivery::class)) {
            $deliveries = \App\Models\Delivery::where('serial_number', $sn)->get();
            foreach ($deliveries as $d) {
                $timeline->push([
                    'module' => 'Delivery',
                    'date' => $d->date ?? $d->work_date, // Menyesuaikan kolom date di tabel delivery Anda
                    'title' => $d->category_job ?? $d->job_type ?? 'Delivery Unit',
                    'pic' => $d->pic ?? $d->user->name ?? 'Unknown',
                    'desc' => 'Lokasi: ' . ($d->location ?? '-'),
                    'route' => route('deliveries.show', $d->id),
                    'color_bg' => 'bg-purple-100',
                    'color_text' => 'text-purple-700',
                    'color_border' => 'border-purple-200'
                ]);
            }
        }

        // Urutkan timeline berdasarkan tanggal terbaru di atas
        $timeline = $timeline->sortByDesc('date')->values();

        return view('assets.show', compact(
            'asset',
            'timeline',
            'latestJob',
            'pmJobs',
            'recommendationHistories',
            'installPartHistories',
            'unitTimeline',
            'timelineStats'
        ));
    }

    public function edit(UnitAsset $asset)
    {
        $this->blockAssetMutation(); // Proteksi Mutasi Aset

        return view('assets.edit', compact('asset'));
    }

    public function update(Request $request, UnitAsset $asset)
    {
        $this->blockAssetMutation(); // Proteksi Mutasi Aset

        $validated = $request->validate([
            'serial_number' => 'required|string|max:255|unique:unit_assets,serial_number,' . $asset->id,
            'unit_type' => 'nullable|string|max:255',
            'jenis_unit' => 'nullable|string|max:255',
            'customer' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:4',
            'status' => 'required|string|in:' . implode(',', $this->assetStatusOptions()),
            'delivery' => 'nullable|string|max:255',
            'supported_by' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $validated['status'] = strtoupper(trim((string) $validated['status']));
        $asset->update($validated);

        return redirect()->route('assets.show', $asset->id)->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(UnitAsset $asset)
    {
        $this->blockAssetMutation(); // Proteksi Mutasi Aset

        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Data aset berhasil dihapus dari sistem.');
    }
}
