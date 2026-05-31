<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/CalendarController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkPlanning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $canManagePlanning = $this->canManagePlanning($user);

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        if ($year < 2020 || $year > 2100) {
            $year = now()->year;
        }

        $mechanicId = $request->input('mechanic_id');
        $status = $request->input('status');

        $mechanics = User::query()
            ->where('status_user', 'mekanik')
            ->when(!$this->canSeeAllDepartments($user), function ($query) use ($user) {
                $query->where('department', $user->department);
            })
            ->orderBy('name')
            ->get();

        $planningsQuery = WorkPlanning::query()
            ->with(['mechanic', 'partner', 'creator'])
            ->whereMonth('planned_date', $month)
            ->whereYear('planned_date', $year)
            ->when($mechanicId, function ($query) use ($mechanicId) {
                $query->where(function ($subQuery) use ($mechanicId) {
                    $subQuery->where('mechanic_id', $mechanicId)
                        ->orWhere('partner_id', $mechanicId);
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            });

        if (!$this->canSeeAllDepartments($user)) {
            $planningsQuery->where('department', $user->department);
        }

        if (!$canManagePlanning) {
            $planningsQuery->where(function ($query) use ($user) {
                $query->where('mechanic_id', $user->id)
                    ->orWhere('partner_id', $user->id);
            });
        }

        $plannings = $planningsQuery
            ->orderBy('planned_date')
            ->orderBy('planned_time')
            ->latest()
            ->get();

        $groupedPlannings = $plannings->groupBy(function (WorkPlanning $planning) {
            return optional($planning->planned_date)->format('Y-m-d') ?: 'Tanpa Tanggal';
        });

        return view('calendar.index', [
            'canManagePlanning' => $canManagePlanning,
            'mechanics' => $mechanics,
            'plannings' => $plannings,
            'groupedPlannings' => $groupedPlannings,
            'month' => $month,
            'year' => $year,
            'selectedMechanicId' => $mechanicId,
            'selectedStatus' => $status,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        abort_unless($this->canManagePlanning($user), 403);

        $validated = $request->validate([
            'mechanic_id' => 'required|exists:users,id',
            'partner_id' => 'nullable|exists:users,id|different:mechanic_id',
            'planned_date' => 'required|date',
            'planned_time' => 'nullable|date_format:H:i',
            'customer' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:150',
            'serial_number' => 'nullable|string|max:100',
            'unit_type' => 'nullable|string|max:100',
            'job_type' => 'nullable|string|max:150',
            'note' => 'nullable|string',
        ]);

        $mechanic = User::findOrFail($validated['mechanic_id']);
        $partner = !empty($validated['partner_id']) ? User::findOrFail($validated['partner_id']) : null;

        if (!$this->canSeeAllDepartments($user)) {
            abort_if($mechanic->department !== $user->department, 403);

            if ($partner && $partner->department !== $user->department) {
                abort(403);
            }
        }

        WorkPlanning::create([
            'created_by' => $user->id,
            'mechanic_id' => $mechanic->id,
            'partner_id' => $partner?->id,
            'branch' => $mechanic->branch ?: $user->branch,
            'department' => $mechanic->department ?: $user->department,
            'planned_date' => $validated['planned_date'],
            'planned_time' => $validated['planned_time'] ?? null,
            'customer' => $validated['customer'] ?? null,
            'location' => $validated['location'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'unit_type' => $validated['unit_type'] ?? null,
            'job_type' => $validated['job_type'] ?? null,
            'status' => 'PLANNED',
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()
            ->route('calendar.index', [
                'month' => date('n', strtotime($validated['planned_date'])),
                'year' => date('Y', strtotime($validated['planned_date'])),
            ])
            ->with('success', 'Planning kerja berhasil dibuat.');
    }

    public function updateStatus(Request $request, WorkPlanning $planning)
    {
        $user = Auth::user();

        abort_unless($this->canManagePlanning($user), 403);

        if (!$this->canSeeAllDepartments($user)) {
            abort_if($planning->department !== $user->department, 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:PLANNED,DONE,CANCELLED',
        ]);

        $planning->status = $validated['status'];
        $planning->save();

        return back()->with('success', 'Status planning berhasil diperbarui.');
    }

    public function destroy(WorkPlanning $planning)
    {
        $user = Auth::user();

        abort_unless($this->canManagePlanning($user), 403);

        if (!$this->canSeeAllDepartments($user)) {
            abort_if($planning->department !== $user->department, 403);
        }

        $planning->delete();

        return back()->with('success', 'Planning kerja berhasil dihapus.');
    }

    private function canManagePlanning(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return in_array($user->status_user, [
            'koordinator',
            'sect_head',
            'admin',
            'super_admin',
        ], true);
    }

    private function canSeeAllDepartments(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return in_array($user->status_user, [
            'sect_head',
            'admin',
            'super_admin',
        ], true);
    }
}
