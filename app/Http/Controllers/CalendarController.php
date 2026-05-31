<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/CalendarController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\UnitAsset;
use App\Models\User;
use App\Models\WorkPlanning;
use App\Models\Piket;
use App\Support\DepartmentScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        $status = strtoupper((string) $request->input('status', ''));

        if (!in_array($status, ['PLANNED', 'DONE', 'CANCELLED'], true)) {
            $status = null;
        }

        $mechanics = User::query()
            ->where('status_user', 'mekanik')
            ->when(!$this->canSeeAllDepartments($user), function ($query) use ($user) {
                $query->where('department', $user->department);
            })
            ->orderBy('name')
            ->get();

        $assetOptions = $this->assetOptionsForUser();

        $customers = $assetOptions
            ->pluck('customer')
            ->filter()
            ->unique()
            ->values();

        $customerLocations = [];
        foreach ($assetOptions as $asset) {
            $customerLocations[$asset->customer][] = $asset->location;
        }

        foreach ($customerLocations as $cust => $locs) {
            $customerLocations[$cust] = array_values(array_unique($locs));
        }

        $plannings = WorkPlanning::with(['mechanic', 'partner', 'creator'])
            ->whereYear('planned_date', $year)
            ->whereMonth('planned_date', $month)
            ->when($mechanicId, function ($query, $mechanicId) {
                $query->where(function ($sub) use ($mechanicId) {
                    $sub->where('mechanic_id', $mechanicId)
                        ->orWhere('partner_id', $mechanicId);
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('planned_date', 'asc')
            ->get();

        $groupedPlannings = $plannings->groupBy(function ($planning) {
            return $planning->planned_date->format('Y-m-d');
        });

        $saturdays = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateObj = Carbon::create($year, $month, $i);
            if ($dateObj->isSaturday()) {
                $saturdays[] = $dateObj->format('Y-m-d');
            }
        }

        $canViewPiket = $user->department === 'RENTAL' || $this->canSeeAllDepartments($user);
        $pikets = collect();
        $recommendedMechanics = collect();

        if ($canViewPiket) {
            $pikets = Piket::with(['user', 'creator'])
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date', 'asc')
                ->get()
                ->groupBy(function ($piket) {
                    return Carbon::parse($piket->date)->format('Y-m-d');
                });

            $rentalFieldMechanics = User::where('department', 'RENTAL')
                ->where('position', 'FIELD')
                ->where('status_user', 'mekanik')
                ->orderBy('name')
                ->get();

            foreach ($rentalFieldMechanics as $mechanic) {
                $latestPiket = Piket::where('user_id', $mechanic->id)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($latestPiket && $latestPiket->status === 'berhalangan') {
                    $mechanic->piket_priority = 1;
                    $mechanic->last_piket_date = $latestPiket->date->format('Y-m-d');
                } else {
                    $mechanic->piket_priority = 2;
                    $mechanic->last_piket_date = $latestPiket ? $latestPiket->date->format('Y-m-d') : '2000-01-01';
                }
            }

            $recommendedMechanics = $rentalFieldMechanics->sortBy(function ($m) {
                return $m->piket_priority . '_' . $m->last_piket_date;
            })->values();
        }

        return view('calendar.index', compact(
            'month',
            'year',
            'mechanics',
            'customers',
            'customerLocations',
            'groupedPlannings',
            'plannings',
            'canManagePlanning',
            'saturdays',
            'pikets',
            'recommendedMechanics',
            'canViewPiket'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        abort_unless($this->canManagePlanning($user), 403);

        $request->validate([
            'date' => 'required|date',
            'mechanic_id' => 'required|exists:users,id',
            'customer' => 'required|string',
            'location' => 'required|string',
            'job_type' => 'required|in:PM,BS,SCHEDULE',
            'partner_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $department = $user->department;

        if ($this->canSeeAllDepartments($user)) {
            $mechanic = User::findOrFail($request->mechanic_id);
            $department = $mechanic->department;
        }

        if (!$this->customerLocationAllowed($request->customer, $request->location, $department)) {
            return back()->with('error', 'Customer dan lokasi tidak valid untuk departemen Anda.');
        }

        WorkPlanning::create([
            'planned_date' => $request->date,
            'mechanic_id' => $request->mechanic_id,
            'customer' => $request->customer,
            'location' => $request->location,
            'job_type' => $request->job_type,
            'partner_id' => $request->partner_id,
            'note' => $request->notes,
            'status' => 'PLANNED',
            'department' => $department,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Planning kerja berhasil ditambahkan.');
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

    public function storePiket(Request $request)
    {
        $user = Auth::user();

        abort_unless($this->canManagePlanning($user), 403);
        if (!$this->canSeeAllDepartments($user)) {
            abort_if($user->department !== 'RENTAL', 403);
        }

        $request->validate([
            'date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:jalan,berhalangan',
        ]);

        $dateObj = Carbon::parse($request->date);
        if (!$dateObj->isSaturday()) {
            return back()->with('error', 'Piket hanya bisa dijadwalkan pada hari Sabtu.');
        }

        $mechanic = User::findOrFail($request->user_id);
        if ($mechanic->department !== 'RENTAL' || $mechanic->position !== 'FIELD') {
            return back()->with('error', 'Piket hanya untuk mekanik RENTAL FIELD.');
        }

        $exists = Piket::where('date', $request->date)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Mekanik ini sudah dijadwalkan piket pada tanggal tersebut.');
        }

        Piket::create([
            'date' => $request->date,
            'user_id' => $request->user_id,
            'status' => $request->status,
            'department' => 'RENTAL',
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Jadwal piket berhasil ditambahkan.');
    }

    public function deferPiket(Piket $piket)
    {
        $user = Auth::user();

        abort_unless($this->canManagePlanning($user), 403);
        if (!$this->canSeeAllDepartments($user)) {
            abort_if($user->department !== 'RENTAL', 403);
        }

        abort_if($piket->department !== 'RENTAL', 403);

        $currentDate = Carbon::parse($piket->date);
        if (!$currentDate->isSaturday()) {
            return back()->with('error', 'Jadwal piket ini bukan hari Sabtu.');
        }

        $nextSaturday = $currentDate->copy()->addWeek()->toDateString();

        $alreadyExists = Piket::where('date', $nextSaturday)
            ->where('user_id', $piket->user_id)
            ->whereKeyNot($piket->id)
            ->exists();

        if ($alreadyExists) {
            return back()->with('error', 'Mekanik yang sama sudah memiliki jadwal piket pada Sabtu berikutnya.');
        }

        $piket->update([
            'date' => $nextSaturday,
            'status' => 'jalan',
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Sabtu ditandai tidak ada pekerjaan. Jadwal piket digeser ke Sabtu berikutnya dengan mekanik yang sama.');
    }

    public function destroyPiket(Piket $piket)
    {
        $user = Auth::user();

        abort_unless($this->canManagePlanning($user), 403);
        if (!$this->canSeeAllDepartments($user)) {
            abort_if($user->department !== 'RENTAL', 403);
        }

        abort_if($piket->department !== 'RENTAL', 403);

        $piket->delete();

        return back()->with('success', 'Jadwal piket berhasil dihapus.');
    }

    public function updateStatus(Request $request, WorkPlanning $planning)
    {
        $user = Auth::user();

        abort_unless($this->canManagePlanning($user), 403);

        if (!$this->canSeeAllDepartments($user)) {
            abort_if($planning->department !== $user->department, 403);
        }

        $request->validate([
            'status' => 'required|in:PLANNED,DONE,CANCELLED',
        ]);

        $planning->update(['status' => $request->status]);

        return back()->with('success', 'Status planning kerja berhasil diubah.');
    }

    private function assetOptionsForUser()
    {
        return UnitAsset::query()
            ->whereNotNull('customer')
            ->where('customer', '!=', '')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->select('customer', 'location')
            ->distinct()
            ->orderBy('customer')
            ->orderBy('location')
            ->get();
    }

    private function customerLocationAllowed(string $customer, string $location, string $department): bool
    {
        return UnitAsset::query()
            ->withoutGlobalScope('department')
            ->where('department', $department)
            ->where('customer', $customer)
            ->where('location', $location)
            ->exists();
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
        return DepartmentScope::userCanSeeAllDepartments($user);
    }
}
