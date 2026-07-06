<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/CalendarController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Piket;
use App\Models\UnitAsset;
use App\Models\User;
use App\Models\WorkPlanning;
use App\Support\DepartmentScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        return view('calendar.index', $this->calendarPayload($request));
    }

    public function planning(Request $request)
    {
        return view('calendar.planning', $this->calendarPayload($request));
    }

    public function piket(Request $request)
    {
        $payload = $this->calendarPayload($request);
        abort_unless($payload['canViewPiket'], 403);

        return view('calendar.piket', $payload);
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

        $mechanic = User::query()
            ->where('status_user', 'mekanik')
            ->when(!$this->canSeeAllDepartments($user), fn ($query) => $query->where('department', $user->department))
            ->findOrFail($request->mechanic_id);

        $partner = null;
        if ($request->filled('partner_id')) {
            $partner = User::query()
                ->where('status_user', 'mekanik')
                ->when(!$this->canSeeAllDepartments($user), fn ($query) => $query->where('department', $user->department))
                ->findOrFail($request->partner_id);
        }

        $department = $this->canSeeAllDepartments($user) ? $mechanic->department : $user->department;

        if (!$this->customerLocationAllowed($request->customer, $request->location, $department)) {
            return back()->with('error', 'Customer dan lokasi tidak valid untuk departemen Anda.');
        }

        if ($partner && $partner->department !== $department) {
            return back()->with('error', 'Partner harus berada di department yang sama dengan mekanik utama.');
        }

        WorkPlanning::create([
            'planned_date' => $request->date,
            'mechanic_id' => $mechanic->id,
            'customer' => $request->customer,
            'location' => $request->location,
            'job_type' => $request->job_type,
            'partner_id' => $partner?->id,
            'note' => $request->notes,
            'status' => 'PLANNED',
            'department' => $department,
            'created_by' => $user->id,
        ]);

        return redirect()->route('calendar.planning')->with('success', 'Planning kerja berhasil ditambahkan.');
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

        $existsQuery = Piket::where('date', $request->date)
            ->where('user_id', $request->user_id)
            ->whereIn('status', ['jalan', 'berhalangan']);
        DepartmentScope::apply($existsQuery, 'pikets', $user);

        if ($existsQuery->exists()) {
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

        if ($piket->status !== 'jalan') {
            return back()->with('error', 'Hanya jadwal piket berstatus JALAN yang bisa ditandai tidak ada pekerjaan.');
        }

        $currentDate = Carbon::parse($piket->date);
        if (!$currentDate->isSaturday()) {
            return back()->with('error', 'Jadwal piket ini bukan hari Sabtu.');
        }

        $nextSaturday = $currentDate->copy()->addWeek()->toDateString();
        $alreadyExistsQuery = Piket::where('date', $nextSaturday)
            ->where('user_id', $piket->user_id)
            ->whereIn('status', ['jalan', 'berhalangan']);
        DepartmentScope::apply($alreadyExistsQuery, 'pikets', $user);

        if ($alreadyExistsQuery->exists()) {
            return back()->with('error', 'Mekanik yang sama sudah memiliki jadwal aktif pada Sabtu berikutnya.');
        }

        $piket->update([
            'status' => 'tidak_ada_kerjaan',
            'created_by' => $user->id,
        ]);

        Piket::create([
            'date' => $nextSaturday,
            'user_id' => $piket->user_id,
            'status' => 'jalan',
            'department' => 'RENTAL',
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Sabtu ditandai tidak ada pekerjaan. Jadwal baru dibuat di Sabtu berikutnya dengan mekanik yang sama.');
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

        $request->validate(['status' => 'required|in:PLANNED,DONE,CANCELLED']);
        $planning->update(['status' => $request->status]);

        return back()->with('success', 'Status planning kerja berhasil diubah.');
    }

    private function calendarPayload(Request $request): array
    {
        $user = Auth::user();
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        if ($year < 2020 || $year > 2100) {
            $year = now()->year;
        }

        $status = strtoupper((string) $request->input('status', ''));
        if (!in_array($status, ['PLANNED', 'DONE', 'CANCELLED'], true)) {
            $status = null;
        }

        $mechanicId = $request->input('mechanic_id');
        $canManagePlanning = $this->canManagePlanning($user);
        $canViewPiket = $user->department === 'RENTAL' || $this->canSeeAllDepartments($user);
        $departmentOptions = $this->departmentOptions($user);
        $selectedDepartment = $this->selectedDepartment($request, $user, $departmentOptions);

        $mechanics = User::query()
            ->where('status_user', 'mekanik')
            ->when($selectedDepartment, fn ($query) => $query->where('department', $selectedDepartment))
            ->orderBy('name')
            ->get();

        $assetOptions = $this->assetOptionsForUser($selectedDepartment);
        $customers = $assetOptions->pluck('customer')->filter()->unique()->values();
        $customerLocations = [];

        foreach ($assetOptions as $asset) {
            $customerLocations[$asset->customer][] = $asset->location;
        }

        foreach ($customerLocations as $customer => $locations) {
            $customerLocations[$customer] = array_values(array_unique($locations));
        }

        $planningsQuery = WorkPlanning::with(['mechanic', 'partner', 'creator'])
            ->whereYear('planned_date', $year)
            ->whereMonth('planned_date', $month)
            ->when($selectedDepartment, fn ($query) => $query->where('department', $selectedDepartment))
            ->when($mechanicId, function ($query, $mechanicId) {
                $query->where(function ($sub) use ($mechanicId) {
                    $sub->where('mechanic_id', $mechanicId)->orWhere('partner_id', $mechanicId);
                });
            })
            ->when($status, fn ($query, $status) => $query->where('status', $status));
        DepartmentScope::apply($planningsQuery, 'work_plannings', $user);

        $plannings = $planningsQuery->orderBy('planned_date')->get();
        $groupedPlannings = $plannings->groupBy(fn ($planning) => $planning->planned_date->format('Y-m-d'));
        $planningWeeks = $this->planningWeeks($year, $month, $groupedPlannings);
        $saturdays = $this->saturdaysInMonth($year, $month);
        $pikets = collect();
        $recommendedMechanics = collect();
        $piketMonthCards = collect();

        if ($canViewPiket) {
            $piketsQuery = Piket::with(['user', 'creator'])
                ->whereYear('date', $year)
                ->whereMonth('date', $month);
            DepartmentScope::apply($piketsQuery, 'pikets', $user);

            $pikets = $piketsQuery
                ->orderBy('date')
                ->get()
                ->groupBy(fn ($piket) => Carbon::parse($piket->date)->format('Y-m-d'));

            $recommendedMechanics = $this->recommendedPiketMechanics($user);
            $piketMonthCards = $this->piketMonthCards($user);
        }

        $planningMonthCards = $this->planningMonthCards($user, $selectedDepartment);
        $timelineMonthCards = $this->timelineMonthCards($canViewPiket, $user);

        return compact(
            'month',
            'year',
            'mechanics',
            'customers',
            'customerLocations',
            'groupedPlannings',
            'planningWeeks',
            'plannings',
            'canManagePlanning',
            'departmentOptions',
            'selectedDepartment',
            'saturdays',
            'pikets',
            'recommendedMechanics',
            'piketMonthCards',
            'planningMonthCards',
            'timelineMonthCards',
            'canViewPiket'
        );
    }

    private function saturdaysInMonth(int $year, int $month): array
    {
        $saturdays = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i);
            if ($date->isSaturday()) {
                $saturdays[] = $date->format('Y-m-d');
            }
        }

        return $saturdays;
    }

    private function planningMonthCards(?User $user = null, ?string $department = null)
    {
        $startMonth = now()->startOfMonth();

        return collect(range(0, 5))->map(function ($offset) use ($startMonth, $user, $department) {
            $date = $startMonth->copy()->addMonths($offset);
            $month = (int) $date->format('m');
            $year = (int) $date->format('Y');

            $query = WorkPlanning::whereYear('planned_date', $year)
                ->whereMonth('planned_date', $month)
                ->when($department, fn ($query) => $query->where('department', $department));
            DepartmentScope::apply($query, 'work_plannings', $user);

            $monthPlannings = $query->get();

            return [
                'month' => $month,
                'year' => $year,
                'label' => $date->translatedFormat('F Y'),
                'short_label' => $date->translatedFormat('M Y'),
                'total_count' => $monthPlannings->count(),
                'planned_count' => $monthPlannings->where('status', 'PLANNED')->count(),
                'done_count' => $monthPlannings->where('status', 'DONE')->count(),
                'cancelled_count' => $monthPlannings->where('status', 'CANCELLED')->count(),
                'is_current' => $offset === 0,
            ];
        });
    }

    private function planningWeeks(int $year, int $month, $groupedPlannings)
    {
        $start = Carbon::create($year, $month, 1)->startOfWeek(Carbon::MONDAY);
        $end = Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $weeks = collect();
        $cursor = $start->copy();
        $weekNumber = 1;

        while ($cursor <= $end) {
            $days = collect();
            $weekStart = $cursor->copy();
            $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY);

            for ($i = 0; $i < 7; $i++) {
                $date = $cursor->copy();
                $dateKey = $date->format('Y-m-d');
                $plans = collect($groupedPlannings->get($dateKey, collect()));

                $days->push([
                    'date' => $date,
                    'key' => $dateKey,
                    'is_current_month' => (int) $date->month === $month,
                    'plans' => $plans,
                    'planned_count' => $plans->where('status', 'PLANNED')->count(),
                    'done_count' => $plans->where('status', 'DONE')->count(),
                    'cancelled_count' => $plans->where('status', 'CANCELLED')->count(),
                ]);

                $cursor->addDay();
            }

            $weeks->push([
                'number' => $weekNumber,
                'label' => 'Week ' . $weekNumber,
                'range' => $weekStart->translatedFormat('d M') . ' - ' . $weekEnd->translatedFormat('d M Y'),
                'days' => $days,
                'total_count' => $days->sum(fn ($day) => $day['plans']->count()),
                'planned_count' => $days->sum('planned_count'),
                'done_count' => $days->sum('done_count'),
                'cancelled_count' => $days->sum('cancelled_count'),
            ]);

            $weekNumber++;
        }

        return $weeks;
    }

    private function piketMonthCards(?User $user = null)
    {
        $startMonth = now()->startOfMonth();

        return collect(range(0, 5))->map(function ($offset) use ($startMonth, $user) {
            $date = $startMonth->copy()->addMonths($offset);
            $month = (int) $date->format('m');
            $year = (int) $date->format('Y');
            $saturdays = $this->saturdaysInMonth($year, $month);

            $query = Piket::whereYear('date', $year)
                ->whereMonth('date', $month);
            DepartmentScope::apply($query, 'pikets', $user);

            $monthPikets = $query->get();

            return [
                'month' => $month,
                'year' => $year,
                'label' => $date->translatedFormat('F Y'),
                'short_label' => $date->translatedFormat('M Y'),
                'saturday_count' => count($saturdays),
                'active_count' => $monthPikets->whereIn('status', ['jalan', 'berhalangan'])->count(),
                'jalan_count' => $monthPikets->where('status', 'jalan')->count(),
                'debt_count' => $monthPikets->where('status', 'berhalangan')->count(),
                'no_work_count' => $monthPikets->where('status', 'tidak_ada_kerjaan')->count(),
                'is_current' => $offset === 0,
            ];
        });
    }

    private function timelineMonthCards(bool $canViewPiket, ?User $user = null)
    {
        $planningCards = $this->planningMonthCards($user)->keyBy(fn ($card) => $card['year'] . '-' . $card['month']);
        $piketCards = $canViewPiket ? $this->piketMonthCards($user)->keyBy(fn ($card) => $card['year'] . '-' . $card['month']) : collect();
        $startMonth = now()->startOfMonth();

        return collect(range(0, 5))->map(function ($offset) use ($startMonth, $planningCards, $piketCards) {
            $date = $startMonth->copy()->addMonths($offset);
            $month = (int) $date->format('m');
            $year = (int) $date->format('Y');
            $key = $year . '-' . $month;
            $planning = $planningCards->get($key, ['total_count' => 0, 'done_count' => 0]);
            $piket = $piketCards->get($key, ['active_count' => 0, 'no_work_count' => 0]);

            return [
                'month' => $month,
                'year' => $year,
                'label' => $date->translatedFormat('F Y'),
                'short_label' => $date->translatedFormat('M Y'),
                'planning_count' => $planning['total_count'],
                'planning_done_count' => $planning['done_count'],
                'piket_active_count' => $piket['active_count'],
                'piket_no_work_count' => $piket['no_work_count'],
                'total_activity_count' => $planning['total_count'] + $piket['active_count'] + $piket['no_work_count'],
                'is_current' => $offset === 0,
            ];
        });
    }

    private function recommendedPiketMechanics(?User $user = null)
    {
        $mechanics = User::where('department', 'RENTAL')
            ->where('position', 'FIELD')
            ->where('status_user', 'mekanik')
            ->orderBy('name')
            ->get();

        foreach ($mechanics as $mechanic) {
            $historyQuery = Piket::where('user_id', $mechanic->id)
                ->orderBy('date', 'desc');
            DepartmentScope::apply($historyQuery, 'pikets', $user);

            $history = $historyQuery->get();
            $activeHistory = $history->whereIn('status', ['jalan', 'berhalangan']);
            $latestActivePiket = $activeHistory->first();
            $latestJalanPiket = $history->firstWhere('status', 'jalan');

            $jalanCount = $history->where('status', 'jalan')->count();
            $berhalanganCount = $history->where('status', 'berhalangan')->count();
            $noWorkCount = $history->where('status', 'tidak_ada_kerjaan')->count();
            $hasDebt = $latestActivePiket && $latestActivePiket->status === 'berhalangan';
            $neverActivePiket = $activeHistory->isEmpty();

            $daysSinceLastActive = $latestActivePiket
                ? Carbon::parse($latestActivePiket->date)->diffInDays(now())
                : 9999;

            $fairnessScore = 0;
            $fairnessScore += $neverActivePiket ? 120 : 0;
            $fairnessScore += $hasDebt ? 100 : 0;
            $fairnessScore += min($daysSinceLastActive, 180) / 7;
            $fairnessScore -= $jalanCount * 6;
            $fairnessScore += $berhalanganCount * 8;
            $fairnessScore -= $noWorkCount * 2;

            $mechanic->piket_priority = $hasDebt ? 1 : ($neverActivePiket ? 2 : 3);
            $mechanic->jalan_count = $jalanCount;
            $mechanic->berhalangan_count = $berhalanganCount;
            $mechanic->tidak_ada_kerjaan_count = $noWorkCount;
            $mechanic->fairness_score = round($fairnessScore, 1);
            $mechanic->last_piket_date = $latestActivePiket ? $latestActivePiket->date->format('Y-m-d') : '2000-01-01';
            $mechanic->last_piket_label = $latestActivePiket ? $latestActivePiket->date->format('d M Y') : 'Belum pernah';
            $mechanic->last_jalan_label = $latestJalanPiket ? $latestJalanPiket->date->format('d M Y') : 'Belum pernah jalan';
            $mechanic->recommendation_reason = match (true) {
                $hasDebt => 'Prioritas hutang piket karena terakhir berhalangan.',
                $neverActivePiket => 'Belum pernah masuk piket aktif.',
                $daysSinceLastActive >= 28 => 'Sudah lama tidak mendapat giliran piket.',
                default => 'Skor dihitung dari riwayat dan pemerataan beban.',
            };
        }

        return $mechanics
            ->sortBy([
                ['piket_priority', 'asc'],
                ['jalan_count', 'asc'],
                ['last_piket_date', 'asc'],
                ['name', 'asc'],
            ])
            ->values()
            ->each(function ($mechanic, $index) {
                $mechanic->fairness_rank = $index + 1;
            });
    }

    private function departmentOptions(?User $user = null)
    {
        if (!$this->canSeeAllDepartments($user)) {
            $department = strtoupper(trim((string) ($user->department ?? '')));

            return $department !== '' ? collect([$department]) : collect();
        }

        return collect()
            ->merge(User::whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department'))
            ->merge(UnitAsset::withoutGlobalScope('department')->whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department'))
            ->merge(WorkPlanning::withoutGlobalScope('department')->whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department'))
            ->map(fn ($department) => strtoupper(trim((string) $department)))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function selectedDepartment(Request $request, ?User $user, $departmentOptions): ?string
    {
        if (!$this->canSeeAllDepartments($user)) {
            return strtoupper(trim((string) ($user->department ?? ''))) ?: null;
        }

        $requested = strtoupper(trim((string) $request->input('department', '')));

        if ($requested !== '' && $departmentOptions->contains($requested)) {
            return $requested;
        }

        $userDepartment = strtoupper(trim((string) ($user->department ?? '')));
        if ($userDepartment !== '' && $departmentOptions->contains($userDepartment)) {
            return $userDepartment;
        }

        if ($departmentOptions->contains('RENTAL')) {
            return 'RENTAL';
        }

        return $departmentOptions->first();
    }

    private function assetOptionsForUser(?string $department = null)
    {
        return UnitAsset::query()
            ->when($department, fn ($query) => $query->where('department', $department))
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

        return in_array($user->status_user, ['koordinator', 'sect_head', 'admin', 'super_admin'], true);
    }

    private function canSeeAllDepartments(?User $user): bool
    {
        return DepartmentScope::userCanSeeAllDepartments($user);
    }
}
