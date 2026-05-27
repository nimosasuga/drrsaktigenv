<?php
// app/Http/Controllers/ChargerController.php

namespace App\Http\Controllers;

use App\Models\Charger;
use App\Models\User;
use App\Models\UnitAsset;
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
        return ($user->id === $charger->user_id) || in_array($role, $privilegedRoles);
    }

    public function index(Request $request)
    {
        $query = Charger::with('user')->latest();

        if ($request->filled('month_filter')) {
            $parts = explode('-', $request->month_filter);
            if (count($parts) == 2) {
                $query->whereYear('date', $parts[0])
                    ->whereMonth('date', $parts[1]);
            }
        }

        if ($request->filled('customer_filter')) {
            $query->where('customer', $request->customer_filter);
        }

        $chargers = $query->paginate(20)->withQueryString();
        $customers = Charger::select('customer')->distinct()->orderBy('customer')->pluck('customer');

        return view('chargers.index', compact('chargers', 'customers'));
    }

    public function create()
    {
        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';

        $partners = User::where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->get(['id', 'name']);

        return view('chargers.create', compact('user', 'branch', 'partners'));
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

            'sn_charger'    => 'required|string|max:100',
            'charger_type'  => 'required|string|max:100',
            'charger_year'  => 'nullable|integer',

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
            $charger = new Charger($validated);
            $charger->user_id = Auth::id();
            $charger->branch = Auth::user()->branch ?? 'HO / Pusat';
            $charger->pic = Auth::user()->name;
            $charger->status_mekanik = Auth::user()->role ?? Auth::user()->status_user;

            // Konversi Job Type Checkbox ke String
            if ($request->has('job_types') && is_array($request->job_types)) {
                $charger->job_type = implode(', ', $request->job_types);
            }

            $charger->save();

            // Simpan Install Parts
            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $charger->installParts()->create([
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

            // Simpan Recommendations Parts
            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $charger->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->rec_qty[$key] ?? 1,
                            'remarks'     => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('chargers.index')->with('success', 'Data Management Charger berhasil disimpan.');
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
            return redirect()
                ->route('chargers.show', $charger->id)
                ->withErrors(['error' => 'Anda tidak memiliki izin untuk mengedit data charger ini.']);
        }

        $user = Auth::user();
        $branch = $charger->branch ?? $user->branch ?? 'HO / Pusat';

        $partners = User::where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('chargers.edit', compact('charger', 'user', 'branch', 'partners'));
    }


    public function update(Request $request, $id)
    {
        $charger = Charger::with(['installParts', 'recommendations'])->findOrFail($id);

        if (!$this->canEditCharger($charger)) {
            return redirect()
                ->route('chargers.show', $charger->id)
                ->withErrors(['error' => 'Anda tidak memiliki izin untuk mengubah data charger ini.']);
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

            'sn_charger'    => 'required|string|max:100',
            'charger_type'  => 'required|string|max:100',
            'charger_year'  => 'nullable|integer|min:1900|max:2100',

            'category_job'  => 'required|string|max:100',
            'status_unit'   => 'required|string|max:100',

            'problem_date'  => 'nullable|date',
            'rfu_date'      => 'nullable|date',
            'problem'       => 'nullable|string',
            'action'        => 'nullable|string',
            'partner'       => 'nullable|string|max:150',

            'job_types'     => 'required|array|min:1',
            'job_types.*'   => 'required|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $charger->date = $validated['date'];
            $charger->in_time = $validated['in_time'];
            $charger->out_time = $validated['out_time'];
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

            // Reset install parts lama, lalu insert ulang sesuai form terbaru.
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
                        'part_name'   => $partName,
                        'qty'         => $request->inst_qty[$key] ?? 1,
                        'remarks'     => $request->inst_remarks[$key] ?? null,
                        'no_job'      => $request->inst_no_job[$key] ?? null,
                        'no_pr'       => $request->inst_no_pr[$key] ?? null,
                    ]);
                }
            }

            // Reset recommendations lama, lalu insert ulang sesuai form terbaru.
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
                        'part_name'   => $partName,
                        'qty'         => $request->rec_qty[$key] ?? 1,
                        'remarks'     => $request->rec_remarks[$key] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('chargers.show', $charger->id)
                ->with('success', 'Data Management Charger berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui data charger: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $charger = Charger::with(['installParts', 'recommendations'])->findOrFail($id);

        if (!$this->canEditCharger($charger)) {
            return redirect()
                ->route('chargers.show', $charger->id)
                ->withErrors(['error' => 'Anda tidak memiliki izin untuk menghapus data charger ini.']);
        }

        DB::beginTransaction();

        try {
            $charger->installParts()->delete();
            $charger->recommendations()->delete();
            $charger->delete();

            DB::commit();

            return redirect()
                ->route('chargers.index')
                ->with('success', 'Data Management Charger berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Gagal menghapus data charger: ' . $e->getMessage()]);
        }
    }
}
