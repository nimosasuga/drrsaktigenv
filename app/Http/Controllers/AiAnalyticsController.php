<?php

namespace App\Http\Controllers;

use App\Models\UnitAsset;
use App\Support\DepartmentScope;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiAnalyticsController extends Controller
{
    private array $monthLabels = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    public function __invoke(Request $request)
    {
        $this->authorizeAiAnalytics();

        $filters = $this->filters($request);
        $modules = $this->modules();
        $activeModules = array_filter($modules, fn ($module) => Schema::hasTable($module['table']));

        $monthlyTotals = array_fill(1, 12, 0);
        $statusDistribution = [];
        $moduleTotals = [];

        foreach ($activeModules as $key => $module) {
            $columns = Schema::getColumnListing($module['table']);
            $moduleTotals[$key] = [
                'label' => $module['label'],
                'total' => $this->filteredCount($module, $columns, $filters),
            ];

            foreach ($this->monthlyCounts($module, $columns, $filters) as $month => $count) {
                $monthlyTotals[$month] += $count;
            }

            foreach ($this->statusCounts($module, $columns, $filters) as $status => $count) {
                $statusDistribution[$status] = ($statusDistribution[$status] ?? 0) + $count;
            }
        }

        arsort($statusDistribution);

        $jobTypeDistribution = $this->jobTypeDistribution($filters);
        $topPics = $this->topPics($filters);
        $topCustomers = $this->topCustomers($filters);
        $riskUnits = $this->riskUnits($filters);
        $pmOverview = $this->pmOverview();
        $summary = $this->summary($monthlyTotals, $statusDistribution, $moduleTotals, $pmOverview);
        $forecast = $this->forecast($monthlyTotals);
        $anomalies = $this->anomalies($monthlyTotals);
        $agingAnalysis = $this->agingAnalysis($filters);
        $picCapacity = $this->picCapacity($filters);
        $customerRiskMatrix = $this->customerRiskMatrix($filters);
        $pmGapByCustomer = $this->pmGapByCustomer();
        $actionPlan = $this->actionPlan($summary, $forecast, $agingAnalysis, $riskUnits, $customerRiskMatrix, $pmGapByCustomer);
        $aiInsights = $this->aiInsights($summary, $monthlyTotals, $statusDistribution, $riskUnits, $topCustomers);
        $linePoints = $this->linePoints($monthlyTotals);
        $years = $this->availableYears($modules);

        return view('ai-analytics.index', [
            'filters' => $filters,
            'years' => $years,
            'monthLabels' => $this->monthLabels,
            'summary' => $summary,
            'monthlyTotals' => $monthlyTotals,
            'linePoints' => $linePoints,
            'statusDistribution' => $statusDistribution,
            'moduleTotals' => $moduleTotals,
            'jobTypeDistribution' => $jobTypeDistribution,
            'topPics' => $topPics,
            'topCustomers' => $topCustomers,
            'riskUnits' => $riskUnits,
            'pmOverview' => $pmOverview,
            'aiInsights' => $aiInsights,
            'forecast' => $forecast,
            'anomalies' => $anomalies,
            'agingAnalysis' => $agingAnalysis,
            'picCapacity' => $picCapacity,
            'customerRiskMatrix' => $customerRiskMatrix,
            'pmGapByCustomer' => $pmGapByCustomer,
            'actionPlan' => $actionPlan,
        ]);
    }

    private function authorizeAiAnalytics(): void
    {
        $role = strtolower(str_replace(['-', '_'], ' ', trim((string) (Auth::user()->status_user ?? Auth::user()->role ?? ''))));

        abort_if($role === 'mekanik' || str_contains($role, 'mechanic'), 403, 'Halaman AI Analytics tidak tersedia untuk mekanik.');
    }

    private function filters(Request $request): array
    {
        $month = $request->input('month');
        $month = $month !== null && $month !== '' ? max(1, min(12, (int) $month)) : null;

        return [
            'year' => (int) $request->input('year', now()->year),
            'month' => $month,
        ];
    }

    private function modules(): array
    {
        return [
            'update_jobs' => ['label' => 'Update Job', 'table' => 'update_jobs', 'date_column' => 'work_date', 'status_column' => 'status_unit'],
            'batteries' => ['label' => 'Battery', 'table' => 'batteries', 'date_column' => 'date', 'status_column' => 'status_unit'],
            'chargers' => ['label' => 'Charger', 'table' => 'chargers', 'date_column' => 'date', 'status_column' => 'status_unit'],
            'deliveries' => ['label' => 'Delivery', 'table' => 'deliveries', 'date_column' => 'date', 'status_column' => 'status_unit'],
            'penarikans' => ['label' => 'Penarikan', 'table' => 'penarikans', 'date_column' => 'date', 'status_column' => 'status_unit'],
        ];
    }

    private function baseQuery(array $module): Builder
    {
        $query = DB::table($module['table']);
        DepartmentScope::apply($query, $module['table']);

        return $query;
    }

    private function applyDateFilter(Builder $query, array $module, array $columns, array $filters, bool $applyMonth = true): void
    {
        $dateColumn = $module['date_column'];

        if (!in_array($dateColumn, $columns, true)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereYear($dateColumn, $filters['year']);

        if ($applyMonth && !empty($filters['month'])) {
            $query->whereMonth($dateColumn, $filters['month']);
        }
    }

    private function filteredCount(array $module, array $columns, array $filters): int
    {
        $query = $this->baseQuery($module);
        $this->applyDateFilter($query, $module, $columns, $filters);

        return $query->count();
    }

    private function monthlyCounts(array $module, array $columns, array $filters): array
    {
        $monthly = array_fill(1, 12, 0);
        $dateColumn = $module['date_column'];

        if (!in_array($dateColumn, $columns, true)) {
            return $monthly;
        }

        $query = $this->baseQuery($module);
        $this->applyDateFilter($query, $module, $columns, $filters, false);

        $rows = $query
            ->selectRaw("MONTH({$dateColumn}) as month_number, COUNT(*) as total")
            ->whereNotNull($dateColumn)
            ->groupBy('month_number')
            ->pluck('total', 'month_number')
            ->toArray();

        foreach ($rows as $month => $count) {
            $monthNumber = (int) $month;
            if ($monthNumber >= 1 && $monthNumber <= 12) {
                $monthly[$monthNumber] = (int) $count;
            }
        }

        return $monthly;
    }

    private function statusCounts(array $module, array $columns, array $filters): array
    {
        $statusColumn = $module['status_column'];

        if (!in_array($statusColumn, $columns, true)) {
            return [];
        }

        $query = $this->baseQuery($module);
        $this->applyDateFilter($query, $module, $columns, $filters);

        $rows = $query
            ->select($statusColumn, DB::raw('COUNT(*) as total'))
            ->whereNotNull($statusColumn)
            ->where($statusColumn, '!=', '')
            ->groupBy($statusColumn)
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $status = $this->normalizeStatus($row->{$statusColumn});
            $counts[$status] = ($counts[$status] ?? 0) + (int) $row->total;
        }

        return $counts;
    }

    private function jobTypeDistribution(array $filters): array
    {
        if (!Schema::hasTable('update_jobs') || !Schema::hasColumn('update_jobs', 'job_type')) {
            return [];
        }

        $module = $this->modules()['update_jobs'];
        $columns = Schema::getColumnListing('update_jobs');
        $query = $this->baseQuery($module);
        $this->applyDateFilter($query, $module, $columns, $filters);

        return $query
            ->select('job_type', DB::raw('COUNT(*) as total'))
            ->whereNotNull('job_type')
            ->where('job_type', '!=', '')
            ->groupBy('job_type')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->mapWithKeys(fn ($row) => [$this->normalizeJobType($row->job_type) => (int) $row->total])
            ->toArray();
    }

    private function topPics(array $filters): array
    {
        if (!Schema::hasTable('update_jobs') || !Schema::hasColumn('update_jobs', 'pic')) {
            return [];
        }

        $module = $this->modules()['update_jobs'];
        $columns = Schema::getColumnListing('update_jobs');
        $query = $this->baseQuery($module);
        $this->applyDateFilter($query, $module, $columns, $filters);

        return $query
            ->select('pic', DB::raw('COUNT(*) as total'))
            ->whereNotNull('pic')
            ->where('pic', '!=', '')
            ->groupBy('pic')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => ['name' => $row->pic, 'total' => (int) $row->total])
            ->toArray();
    }

    private function topCustomers(array $filters): array
    {
        if (!Schema::hasTable('update_jobs') || !Schema::hasColumn('update_jobs', 'customer')) {
            return [];
        }

        $module = $this->modules()['update_jobs'];
        $columns = Schema::getColumnListing('update_jobs');
        $query = $this->baseQuery($module);
        $this->applyDateFilter($query, $module, $columns, $filters);

        return $query
            ->select('customer', DB::raw('COUNT(*) as total'))
            ->whereNotNull('customer')
            ->where('customer', '!=', '')
            ->groupBy('customer')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => ['name' => $row->customer, 'total' => (int) $row->total])
            ->toArray();
    }

    private function riskUnits(array $filters): array
    {
        if (!Schema::hasTable('update_jobs') || !Schema::hasColumn('update_jobs', 'serial_number')) {
            return [];
        }

        $module = $this->modules()['update_jobs'];
        $columns = Schema::getColumnListing('update_jobs');
        $query = $this->baseQuery($module);
        $this->applyDateFilter($query, $module, $columns, $filters);

        return $query
            ->select(
                'serial_number',
                DB::raw('MAX(customer) as customer'),
                DB::raw('MAX(location) as location'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(status_unit, ''))) IN ('B/D', 'BD', 'BREAKDOWN') OR UPPER(TRIM(COALESCE(status_unit, ''))) LIKE '%BREAKDOWN%' THEN 1 ELSE 0 END) as breakdown_total"),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(status_unit, ''))) IN ('WAITING PART', 'WAITING_PART', 'WAITING-PART') THEN 1 ELSE 0 END) as waiting_part_total")
            )
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->groupBy('serial_number')
            ->orderByDesc('breakdown_total')
            ->orderByDesc('waiting_part_total')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                $riskScore = ((int) $row->breakdown_total * 3) + ((int) $row->waiting_part_total * 2) + (int) $row->total;

                return [
                    'serial_number' => $row->serial_number,
                    'customer' => $row->customer ?: '-',
                    'location' => $row->location ?: '-',
                    'total' => (int) $row->total,
                    'breakdown_total' => (int) $row->breakdown_total,
                    'waiting_part_total' => (int) $row->waiting_part_total,
                    'risk_score' => $riskScore,
                ];
            })
            ->sortByDesc('risk_score')
            ->values()
            ->toArray();
    }

    private function pmOverview(): array
    {
        if (!Schema::hasTable('unit_assets') || !Schema::hasTable('update_jobs')) {
            return ['eligible' => 0, 'done' => 0, 'pending' => 0, 'rate' => 0];
        }

        $assets = DB::table('unit_assets');
        DepartmentScope::apply($assets, 'unit_assets');
        $eligible = (clone $assets)
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->whereRaw(UnitAsset::activeStatusSql())
            ->distinct()
            ->count('serial_number');

        $pmQuery = DB::table('update_jobs');
        DepartmentScope::apply($pmQuery, 'update_jobs');
        $done = $pmQuery
            ->whereYear('work_date', now()->year)
            ->whereMonth('work_date', now()->month)
            ->where(function ($query) {
                $query->where('job_type', 'Preventive Maintenance')
                    ->orWhere('job_type', 'PM')
                    ->orWhere('job_type', 'like', '%Preventive Maintenance%');
            })
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->distinct()
            ->count('serial_number');

        $pending = max(0, $eligible - $done);

        return [
            'eligible' => $eligible,
            'done' => $done,
            'pending' => $pending,
            'rate' => $eligible > 0 ? round(($done / $eligible) * 100) : 0,
        ];
    }

    private function updateJobQuery(array $filters, bool $applyMonth = true): Builder
    {
        $query = DB::table('update_jobs');
        DepartmentScope::apply($query, 'update_jobs');

        if (!Schema::hasTable('update_jobs') || !Schema::hasColumn('update_jobs', 'work_date')) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereYear('work_date', $filters['year']);

        if ($applyMonth && !empty($filters['month'])) {
            $query->whereMonth('work_date', $filters['month']);
        }

        return $query;
    }

    private function forecast(array $monthlyTotals): array
    {
        $values = array_values($monthlyTotals);
        $nonZero = array_values(array_filter($values, fn ($value) => $value > 0));
        $lastThree = array_slice($nonZero ?: $values, -3);
        $previousThree = array_slice($nonZero ?: $values, -6, 3);

        $lastAverage = count($lastThree) > 0 ? array_sum($lastThree) / count($lastThree) : 0;
        $previousAverage = count($previousThree) > 0 ? array_sum($previousThree) / count($previousThree) : $lastAverage;
        $growthRate = $previousAverage > 0 ? (($lastAverage - $previousAverage) / $previousAverage) : 0;
        $projection = max(0, (int) round($lastAverage * (1 + max(-0.35, min(0.45, $growthRate)))));

        return [
            'last_average' => round($lastAverage, 1),
            'previous_average' => round($previousAverage, 1),
            'growth_rate' => round($growthRate * 100),
            'projection' => $projection,
            'direction' => $growthRate > 0.08 ? 'Naik' : ($growthRate < -0.08 ? 'Turun' : 'Stabil'),
        ];
    }

    private function anomalies(array $monthlyTotals): array
    {
        $values = array_values($monthlyTotals);
        $average = count($values) > 0 ? array_sum($values) / count($values) : 0;

        return collect($monthlyTotals)
            ->map(function ($total, $month) use ($average) {
                $delta = $total - $average;
                $severity = $average > 0 ? abs($delta) / $average : 0;

                return [
                    'month' => (int) $month,
                    'label' => $this->monthLabels[(int) $month] ?? $month,
                    'total' => (int) $total,
                    'delta' => round($delta, 1),
                    'severity' => round($severity * 100),
                    'direction' => $delta >= 0 ? 'Spike' : 'Drop',
                ];
            })
            ->filter(fn ($row) => $row['severity'] >= 35 && $row['total'] > 0)
            ->sortByDesc('severity')
            ->take(5)
            ->values()
            ->all();
    }

    private function agingAnalysis(array $filters): array
    {
        if (!Schema::hasTable('update_jobs')) {
            return ['buckets' => [], 'stale_jobs' => [], 'open_total' => 0, 'critical_total' => 0];
        }

        $query = $this->updateJobQuery($filters)
            ->whereNotNull('work_date')
            ->where(function ($statusQuery) {
                $statusQuery->whereNull('status_unit')
                    ->orWhereRaw("UPPER(TRIM(COALESCE(status_unit, ''))) <> 'RFU'");
            });

        $rows = $query
            ->select('id', 'serial_number', 'customer', 'location', 'pic', 'job_type', 'status_unit', 'work_date', DB::raw('DATEDIFF(CURDATE(), work_date) as age_days'))
            ->orderByDesc('age_days')
            ->limit(80)
            ->get();

        $buckets = [
            '0-3 hari' => 0,
            '4-7 hari' => 0,
            '8-14 hari' => 0,
            '15+ hari' => 0,
        ];

        foreach ($rows as $row) {
            $age = (int) $row->age_days;
            if ($age <= 3) {
                $buckets['0-3 hari']++;
            } elseif ($age <= 7) {
                $buckets['4-7 hari']++;
            } elseif ($age <= 14) {
                $buckets['8-14 hari']++;
            } else {
                $buckets['15+ hari']++;
            }
        }

        return [
            'buckets' => $buckets,
            'stale_jobs' => $rows->take(8)->map(fn ($row) => [
                'id' => $row->id,
                'serial_number' => $row->serial_number ?: '-',
                'customer' => $row->customer ?: '-',
                'location' => $row->location ?: '-',
                'pic' => $row->pic ?: '-',
                'job_type' => $this->normalizeJobType($row->job_type),
                'status' => $this->normalizeStatus($row->status_unit),
                'age_days' => (int) $row->age_days,
                'route' => route('update-jobs.show', $row->id),
            ])->values()->all(),
            'open_total' => $rows->count(),
            'critical_total' => $rows->filter(fn ($row) => (int) $row->age_days >= 8)->count(),
        ];
    }

    private function picCapacity(array $filters): array
    {
        if (!Schema::hasTable('update_jobs')) {
            return [];
        }

        return $this->updateJobQuery($filters)
            ->select(
                'pic',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(status_unit, ''))) = 'RFU' THEN 1 ELSE 0 END) as rfu_total"),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(status_unit, ''))) IN ('B/D', 'BD', 'BREAKDOWN') OR UPPER(TRIM(COALESCE(status_unit, ''))) LIKE '%BREAKDOWN%' THEN 1 ELSE 0 END) as breakdown_total"),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(status_unit, ''))) IN ('WAITING PART', 'WAITING_PART', 'WAITING-PART') THEN 1 ELSE 0 END) as waiting_part_total")
            )
            ->whereNotNull('pic')
            ->where('pic', '!=', '')
            ->groupBy('pic')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $risk = (int) $row->breakdown_total + (int) $row->waiting_part_total;
                $riskRate = (int) $row->total > 0 ? round(($risk / (int) $row->total) * 100) : 0;

                return [
                    'name' => $row->pic,
                    'total' => (int) $row->total,
                    'rfu_total' => (int) $row->rfu_total,
                    'breakdown_total' => (int) $row->breakdown_total,
                    'waiting_part_total' => (int) $row->waiting_part_total,
                    'risk_rate' => $riskRate,
                    'load_label' => $riskRate >= 45 ? 'Banyak masalah' : ((int) $row->total >= 20 ? 'Beban tinggi' : 'Aman'),
                ];
            })
            ->toArray();
    }

    private function customerRiskMatrix(array $filters): array
    {
        if (!Schema::hasTable('update_jobs')) {
            return [];
        }

        return $this->updateJobQuery($filters)
            ->select(
                'customer',
                DB::raw('MAX(location) as location'),
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT serial_number) as unit_total'),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(status_unit, ''))) IN ('B/D', 'BD', 'BREAKDOWN') OR UPPER(TRIM(COALESCE(status_unit, ''))) LIKE '%BREAKDOWN%' THEN 1 ELSE 0 END) as breakdown_total"),
                DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(status_unit, ''))) IN ('WAITING PART', 'WAITING_PART', 'WAITING-PART') THEN 1 ELSE 0 END) as waiting_part_total")
            )
            ->whereNotNull('customer')
            ->where('customer', '!=', '')
            ->groupBy('customer')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(function ($row) {
                $risk = ((int) $row->breakdown_total * 3) + ((int) $row->waiting_part_total * 2);

                return [
                    'customer' => $row->customer,
                    'location' => $row->location ?: '-',
                    'total' => (int) $row->total,
                    'unit_total' => (int) $row->unit_total,
                    'breakdown_total' => (int) $row->breakdown_total,
                    'waiting_part_total' => (int) $row->waiting_part_total,
                    'risk_score' => $risk,
                    'priority' => $risk >= 12 ? 'P1' : ($risk >= 6 ? 'P2' : 'P3'),
                ];
            })
            ->sortByDesc('risk_score')
            ->values()
            ->toArray();
    }

    private function pmGapByCustomer(): array
    {
        if (!Schema::hasTable('unit_assets') || !Schema::hasTable('update_jobs')) {
            return [];
        }

        $assets = DB::table('unit_assets')
            ->select('customer', DB::raw('COUNT(DISTINCT serial_number) as eligible'))
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->whereRaw(UnitAsset::activeStatusSql());
        DepartmentScope::apply($assets, 'unit_assets');

        $eligibleRows = $assets
            ->whereNotNull('customer')
            ->where('customer', '!=', '')
            ->groupBy('customer')
            ->pluck('eligible', 'customer');

        $pm = DB::table('update_jobs')
            ->select('customer', DB::raw('COUNT(DISTINCT serial_number) as done'))
            ->whereYear('work_date', now()->year)
            ->whereMonth('work_date', now()->month)
            ->where(function ($query) {
                $query->where('job_type', 'Preventive Maintenance')
                    ->orWhere('job_type', 'PM')
                    ->orWhere('job_type', 'like', '%Preventive Maintenance%');
            });
        DepartmentScope::apply($pm, 'update_jobs');

        $doneRows = $pm
            ->whereNotNull('customer')
            ->where('customer', '!=', '')
            ->groupBy('customer')
            ->pluck('done', 'customer');

        return $eligibleRows
            ->map(function ($eligible, $customer) use ($doneRows) {
                $done = (int) ($doneRows[$customer] ?? 0);
                $pending = max(0, (int) $eligible - $done);

                return [
                    'customer' => $customer,
                    'eligible' => (int) $eligible,
                    'done' => $done,
                    'pending' => $pending,
                    'rate' => (int) $eligible > 0 ? round(($done / (int) $eligible) * 100) : 0,
                ];
            })
            ->sortByDesc('pending')
            ->take(8)
            ->values()
            ->all();
    }

    private function actionPlan(array $summary, array $forecast, array $agingAnalysis, array $riskUnits, array $customerRiskMatrix, array $pmGapByCustomer): array
    {
        $actions = [];

        if (($agingAnalysis['critical_total'] ?? 0) > 0) {
            $actions[] = [
                'priority' => 'P1',
                'title' => 'Kejar job yang belum selesai',
                'body' => ($agingAnalysis['critical_total'] ?? 0) . ' job belum RFU lebih dari 8 hari. Cek penyebabnya dan tentukan target selesai.',
            ];
        }

        if (!empty($riskUnits)) {
            $actions[] = [
                'priority' => 'P1',
                'title' => 'Cek unit yang paling sering bermasalah',
                'body' => $riskUnits[0]['serial_number'] . ' paling sering masuk masalah. Buka histori unit dan cek part yang pernah direkomendasikan.',
            ];
        }

        if (!empty($pmGapByCustomer) && $pmGapByCustomer[0]['pending'] > 0) {
            $actions[] = [
                'priority' => 'P2',
                'title' => 'Kejar PM yang belum dikerjakan',
                'body' => $pmGapByCustomer[0]['customer'] . ' masih ada ' . $pmGapByCustomer[0]['pending'] . ' unit belum PM bulan ini.',
            ];
        }

        if (!empty($customerRiskMatrix) && $customerRiskMatrix[0]['priority'] !== 'P3') {
            $actions[] = [
                'priority' => $customerRiskMatrix[0]['priority'],
                'title' => 'Fokus ke customer yang paling banyak masalah',
                'body' => $customerRiskMatrix[0]['customer'] . ' jadi area yang perlu dipantau paling dulu pada filter ini.',
            ];
        }

        if ($forecast['direction'] === 'Naik') {
            $actions[] = [
                'priority' => 'P2',
                'title' => 'Siapkan orang untuk bulan depan',
                'body' => 'Perkiraan kerja naik ke sekitar ' . $forecast['projection'] . ' data. Bagi beban PIC sebelum pekerjaan menumpuk.',
            ];
        }

        if ($summary['health_score'] < 60) {
            $actions[] = [
                'priority' => 'P1',
                'title' => 'Kondisi perlu perhatian',
                'body' => 'Nilai kondisi di bawah 60. Utamakan breakdown, waiting part, dan PM yang belum dikerjakan.',
            ];
        }

        return array_slice($actions, 0, 6);
    }

    private function summary(array $monthlyTotals, array $statusDistribution, array $moduleTotals, array $pmOverview): array
    {
        $totalRecords = array_sum(array_column($moduleTotals, 'total'));
        $riskTotal = ($statusDistribution['Breakdown'] ?? 0) + ($statusDistribution['Waiting Part'] ?? 0);
        $riskRate = $totalRecords > 0 ? round(($riskTotal / $totalRecords) * 100) : 0;

        return [
            'total_records' => $totalRecords,
            'risk_total' => $riskTotal,
            'risk_rate' => $riskRate,
            'peak_month_total' => max($monthlyTotals ?: [0]),
            'pm_rate' => $pmOverview['rate'],
            'health_score' => max(0, min(100, 100 - $riskRate - max(0, 80 - $pmOverview['rate']) / 2)),
        ];
    }

    private function aiInsights(array $summary, array $monthlyTotals, array $statusDistribution, array $riskUnits, array $topCustomers): array
    {
        $insights = [];
        $lastThree = array_slice(array_values($monthlyTotals), -3);
        $trendDelta = count($lastThree) === 3 ? ($lastThree[2] - $lastThree[0]) : 0;

        $insights[] = [
            'tone' => $summary['health_score'] >= 75 ? 'emerald' : ($summary['health_score'] >= 55 ? 'amber' : 'red'),
            'title' => 'AI Health Score ' . $summary['health_score'] . '/100',
            'body' => $summary['health_score'] >= 75
                ? 'Kondisi operasional relatif sehat. Fokuskan monitoring pada unit dengan risiko tertinggi.'
                : 'Skor kesehatan turun karena kombinasi risk status dan progres PM. Prioritaskan follow-up unit merah.',
        ];

        if ($summary['pm_rate'] < 80) {
            $insights[] = [
                'tone' => 'cyan',
                'title' => 'PM bulan ini belum optimal',
                'body' => 'Progress PM berada di ' . $summary['pm_rate'] . '%. AI menyarankan jadwal ulang unit pending berdasarkan customer dengan beban tertinggi.',
            ];
        }

        if (($statusDistribution['Waiting Part'] ?? 0) > 0) {
            $insights[] = [
                'tone' => 'amber',
                'title' => 'Waiting Part perlu dipercepat',
                'body' => number_format($statusDistribution['Waiting Part'], 0, ',', '.') . ' record menunggu part. Cocok dipadankan dengan Recommendation Control agar bottleneck stok cepat terlihat.',
            ];
        }

        if (!empty($riskUnits)) {
            $unit = $riskUnits[0];
            $insights[] = [
                'tone' => 'red',
                'title' => 'Unit risiko tertinggi: ' . $unit['serial_number'],
                'body' => 'Tercatat ' . $unit['breakdown_total'] . ' breakdown dan ' . $unit['waiting_part_total'] . ' waiting part. Periksa histori dan sparepart unit ini lebih dulu.',
            ];
        }

        if ($trendDelta > 0) {
            $insights[] = [
                'tone' => 'blue',
                'title' => 'Aktivitas naik dalam 3 bulan terakhir',
                'body' => 'Volume naik ' . number_format($trendDelta, 0, ',', '.') . ' record. AI menyarankan cek kapasitas PIC dan customer dengan request tertinggi.',
            ];
        }

        if (!empty($topCustomers)) {
            $customer = $topCustomers[0];
            $insights[] = [
                'tone' => 'slate',
                'title' => 'Customer paling aktif: ' . $customer['name'],
                'body' => number_format($customer['total'], 0, ',', '.') . ' aktivitas pada filter ini. Gunakan sebagai prioritas review SLA.',
            ];
        }

        return array_slice($insights, 0, 6);
    }

    private function linePoints(array $monthlyTotals): string
    {
        $max = max($monthlyTotals ?: [1]);
        $points = [];
        $index = 0;

        foreach ($monthlyTotals as $value) {
            $x = 20 + ($index * 58);
            $y = 130 - ($max > 0 ? (($value / $max) * 100) : 0);
            $points[] = round($x, 1) . ',' . round($y, 1);
            $index++;
        }

        return implode(' ', $points);
    }

    private function availableYears(array $modules): array
    {
        $years = collect();

        foreach ($modules as $module) {
            if (!Schema::hasTable($module['table']) || !Schema::hasColumn($module['table'], $module['date_column'])) {
                continue;
            }

            $query = DB::table($module['table']);
            DepartmentScope::apply($query, $module['table']);

            $years = $years->merge($query
                ->selectRaw('YEAR(' . $module['date_column'] . ') as year')
                ->whereNotNull($module['date_column'])
                ->distinct()
                ->pluck('year'));
        }

        return $years->filter()->unique()->sortDesc()->values()->all() ?: [now()->year];
    }

    private function normalizeStatus(?string $status): string
    {
        $value = strtoupper(trim((string) $status));

        return match (true) {
            $value === 'RFU' => 'RFU',
            in_array($value, ['B/D', 'BD', 'BREAKDOWN'], true) || str_contains($value, 'BREAKDOWN') => 'Breakdown',
            in_array($value, ['MONITORING', 'STANDBY'], true) => 'Monitoring',
            in_array($value, ['WAITING PART', 'WAITING_PART', 'WAITING-PART'], true) || str_contains($value, 'WAITING PART') => 'Waiting Part',
            default => trim((string) $status) !== '' ? trim((string) $status) : 'Tanpa Status',
        };
    }

    private function normalizeJobType(?string $jobType): string
    {
        $value = strtoupper(trim((string) $jobType));

        return match ($value) {
            'PM', 'PREVENTIVE MAINTENANCE' => 'Preventive Maintenance',
            'BM', 'TROUBLESHOOTING' => 'Troubleshooting',
            'PDI', 'INSPECTION' => 'Inspection',
            'INSTALL PART' => 'Install Part',
            'REPAIR' => 'Repair',
            default => trim((string) $jobType) !== '' ? trim((string) $jobType) : 'Tanpa Tipe',
        };
    }
}
