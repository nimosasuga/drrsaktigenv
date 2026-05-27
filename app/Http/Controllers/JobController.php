<?php
// app/Http/Controllers/JobController.php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\UnitAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    /**
     * Helper untuk mengecek Hak Akses (Otorisasi)
     */
    private function canEditJob($job)
    {
        $user = Auth::user();
        $role = $user->role ?? $user->status_user;

        // Daftar role yang bisa bebas edit/hapus semua job
        $privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];

        // True JIKA dia pembuatnya ATAU dia punya role level atas
        return ($user->id === $job->user_id) || in_array($role, $privilegedRoles);
    }

    /**
     * Menampilkan daftar job dengan Filter & Pagination.
     */
    public function index(Request $request)
    {
        $query = Job::with('user')->latest();

        // 1. Filter Berdasarkan Bulan (Format dari input type="month" adalah YYYY-MM)
        // Note: Kolom tanggal di tabel jobs adalah 'work_date'
        if ($request->filled('month_filter')) {
            $parts = explode('-', $request->month_filter);
            if (count($parts) == 2) {
                $query->whereYear('work_date', $parts[0])
                    ->whereMonth('work_date', $parts[1]);
            }
        }

        // 2. Filter Berdasarkan Customer
        if ($request->filled('customer_filter')) {
            $query->where('customer', $request->customer_filter);
        }

        // Ambil data dengan pagination, append query string agar pagination tidak mereset filter
        $jobs = $query->paginate(20)->withQueryString();

        // Ambil daftar customer unik yang ada di tabel update_jobs untuk dropdown filter
        $customers = Job::select('customer')->distinct()->orderBy('customer')->pluck('customer');

        return view('update-jobs.index', compact('jobs', 'customers'));
    }

    public function create()
    {
        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';

        $partners = User::where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->get(['id', 'name']);

        return view('update-jobs.create', compact('user', 'branch', 'partners'));
    }

    public function searchAssets(Request $request)
    {
        $search = $request->get('q');

        if (empty($search)) {
            return response()->json([]);
        }

        $assets = UnitAsset::where('serial_number', 'LIKE', "%{$search}%")
            ->take(10)
            ->get();

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
            'work_date'     => 'required|date',
            'in_time'       => 'nullable|date_format:H:i',
            'out_time'      => 'nullable|date_format:H:i',
            'serial_number' => 'required|string|max:100',
            'unit_type'     => 'required|string|max:100',
            'hour_meter'    => 'required|numeric|min:0',
            'customer'      => 'required|string|max:150',
            'location'      => 'required|string|max:150',
            'problem'       => 'required|string',
            'action'        => 'required|string',
            'job_type'      => 'nullable|string|max:100',
            'status_unit'   => 'nullable|string|max:100',
            'partner'       => 'nullable|string|max:150',
            'vehicle_type'  => 'nullable|string|max:100',
            'nopol'         => 'nullable|string|max:100',
            'problem_date'  => 'nullable|date',
            'rfu_date'      => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $job = new Job($validated);
            $job->user_id = Auth::id();
            $job->branch = Auth::user()->branch ?? 'HO / Pusat';
            $job->pic = Auth::user()->name;
            $job->status_mekanik = Auth::user()->role ?? Auth::user()->status_user;
            $job->save();

            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $job->installParts()->create([
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
                        $job->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->rec_qty[$key] ?? 1,
                            'remarks'     => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('update-jobs.index')->with('success', 'Update Job berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $job = Job::with(['user', 'installParts', 'recommendations'])->findOrFail($id);
        return view('update-jobs.show', compact('job'));
    }

    public function edit($id)
    {
        $job = Job::with(['installParts', 'recommendations'])->findOrFail($id);

        // Cek Hak Akses
        if (!$this->canEditJob($job)) {
            return redirect()->route('update-jobs.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit Pekerjaan mekanik lain.']);
        }

        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';

        $partners = User::where('branch', $branch)
            ->where('id', '!=', $user->id)
            ->get(['id', 'name']);

        return view('update-jobs.edit', compact('job', 'user', 'branch', 'partners'));
    }

    public function update(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        // Cek Hak Akses
        if (!$this->canEditJob($job)) {
            return redirect()->route('update-jobs.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit Pekerjaan mekanik lain.']);
        }

        $validated = $request->validate([
            'work_date'     => 'required|date',
            'in_time'       => 'nullable|date_format:H:i',
            'out_time'      => 'nullable|date_format:H:i',
            'serial_number' => 'required|string|max:100',
            'unit_type'     => 'required|string|max:100',
            'hour_meter'    => 'required|numeric|min:0',
            'customer'      => 'required|string|max:150',
            'location'      => 'required|string|max:150',
            'problem'       => 'required|string',
            'action'        => 'required|string',
            'job_type'      => 'nullable|string|max:100',
            'status_unit'   => 'nullable|string|max:100',
            'partner'       => 'nullable|string|max:150',
            'vehicle_type'  => 'nullable|string|max:100',
            'nopol'         => 'nullable|string|max:100',
            'problem_date'  => 'nullable|date',
            'rfu_date'      => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $job->update($validated);

            $job->installParts()->delete();
            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $job->installParts()->create([
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

            $job->recommendations()->delete();
            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $job->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->rec_qty[$key] ?? 1,
                            'remarks'     => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('update-jobs.show', $job->id)->with('success', 'Update Job berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui data: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghapus Job.
     */
    public function destroy($id)
    {
        $job = Job::findOrFail($id);

        // Cek Hak Akses
        if (!$this->canEditJob($job)) {
            return redirect()->route('update-jobs.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk menghapus Pekerjaan mekanik lain.']);
        }

        // Penghapusan akan otomatis menghapus installParts & recommendations (karena CascadeOnDelete di database)
        $job->delete();

        return redirect()->route('update-jobs.index')->with('success', 'Data Update Job berhasil dihapus permanen.');
    }
}
