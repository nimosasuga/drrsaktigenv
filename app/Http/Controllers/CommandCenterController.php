<?php
// PATH FILE: app/Http/Controllers/CommandCenterController.php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommandCenterController extends Controller
{
    private function roleText(): string
    {
        $user = Auth::user();

        return strtolower(trim(implode(' ', array_filter([
            (string) ($user->role ?? ''),
            (string) ($user->status_user ?? ''),
        ]))));
    }

    private function canAccessCommandCenter(): bool
    {
        $roleText = str_replace(['-', '_'], ' ', $this->roleText());

        return str_contains($roleText, 'koordinator')
            || str_contains($roleText, 'coordinator')
            || str_contains($roleText, 'sect head')
            || str_contains($roleText, 'secthead')
            || str_contains($roleText, 'admin')
            || str_contains($roleText, 'super admin')
            || str_contains($roleText, 'superadmin');
    }

    private function authorizeCommandCenter(): void
    {
        abort_unless($this->canAccessCommandCenter(), 403, 'Akses Command Center hanya untuk Koordinator, Sect Head, Admin, dan Super Admin.');
    }

    private function statusUnitOptions(): array
    {
        return ['RFU', 'Breakdown', 'Monitoring', 'Waiting Part'];
    }

    private function jobTypeOptions(): array
    {
        return ['Preventive Maintenance', 'Install Part', 'Troubleshooting', 'Inspection', 'Repair'];
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
            'PM' => 'Preventive Maintenance',
            'BM' => 'Troubleshooting',
            'PDI' => 'Inspection',
            'PREVENTIVE MAINTENANCE' => 'Preventive Maintenance',
            'INSTALL PART' => 'Install Part',
            'TROUBLESHOOTING' => 'Troubleshooting',
            'INSPECTION' => 'Inspection',
            'REPAIR' => 'Repair',
            default => trim((string) $jobType) !== '' ? trim((string) $jobType) : 'Tanpa Tipe',
        };
    }

    private function modules(): array
    {
        return [
            'assets' => [
                'label' => 'Manajemen Aset',
                'table' => 'unit_assets',
                'date_column' => 'created_at',
                'unit_column' => 'serial_number',
                'status_column' => 'status',
                'route' => 'assets.index',
            ],
            'update-jobs' => [
                'label' => 'Update Job',
                'table' => 'update_jobs',
                'date_column' => 'work_date',
                'unit_column' => 'serial_number',
                'status_column' => 'status_unit',
                'route' => 'update-jobs.index',
            ],
            'batteries' => [
                'label' => 'Management Battery',
                'table' => 'batteries',
                'date_column' => 'date',
                'unit_column' => 'serial_number',
                'status_column' => 'status_unit',
                'route' => 'batteries.index',
            ],
            'chargers' => [
                'label' => 'Management Charger',
                'table' => 'chargers',
                'date_column' => 'date',
                'unit_column' => 'serial_number',
                'status_column' => 'status_unit',
                'route' => 'chargers.index',
            ],
            'deliveries' => [
                'label' => 'Delivery Unit',
                'table' => 'deliveries',
                'date_column' => 'date',
                'unit_column' => 'serial_number',
                'status_column' => 'status_unit',
                'route' => 'deliveries.index',
            ],
            'penarikans' => [
                'label' => 'Penarikan Unit',
                'table' => 'penarikans',
                'date_column' => 'date',
                'unit_column' => 'serial_number',
                'status_column' => 'status_unit',
                'route' => 'penarikans.index',
            ],
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeCommandCenter();

        $modules = $this->modules();
        $filters = $this->filters($request, $modules);
        $activeModules = $this->activeModules($modules, $filters['module']);

        $moduleStats = [];
        $monthlyTotals = array_fill(1, 12, 0);
        $picScores = [];
        $filteredTotalRecords = 0;

        foreach ($activeModules as $key => $module) {
            $table = $module['table'];

            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $dateColumn = $module['date_column'];
            $unitColumn = $module['unit_column'];
            $statusColumn = $module['status_column'];

            $total = DB::table($table)->count();
            $filteredQuery = DB::table($table);
            $this->applyFilters($filteredQuery, $module, $columns, $filters, true);

            $yearTotal = (clone $filteredQuery)->count();
            $filteredTotalRecords += $yearTotal;

            $uniqueUnits = in_array($unitColumn, $columns, true)
                ? (clone $filteredQuery)->whereNotNull($unitColumn)->where($unitColumn, '!=', '')->distinct()->count($unitColumn)
                : 0;

            $statusCounts = [];
            if (in_array($statusColumn, $columns, true)) {
                $statusQuery = DB::table($table);
                $this->applyFilters($statusQuery, $module, $columns, $filters, true, false);

                $rows = $statusQuery
                    ->select($statusColumn, DB::raw('COUNT(*) as total'))
                    ->whereNotNull($statusColumn)
                    ->where($statusColumn, '!=', '')
                    ->groupBy($statusColumn)
                    ->get();

                foreach ($rows as $row) {
                    $status = $this->normalizeStatus($row->{$statusColumn});
                    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + (int) $row->total;
                }

                arsort($statusCounts);
            }

            $monthly = array_fill(1, 12, 0);
            if (in_array($dateColumn, $columns, true)) {
                $monthlyQuery = DB::table($table);
                $this->applyFilters($monthlyQuery, $module, $columns, $filters, false);

                $rows = $monthlyQuery
                    ->selectRaw("MONTH({$dateColumn}) as month_number, COUNT(*) as total")
                    ->whereYear($dateColumn, $filters['year'])
                    ->whereNotNull($dateColumn)
                    ->groupBy('month_number')
                    ->pluck('total', 'month_number')
                    ->toArray();

                foreach ($rows as $month => $count) {
                    $monthNumber = (int) $month;
                    if ($monthNumber >= 1 && $monthNumber <= 12) {
                        $monthly[$monthNumber] = (int) $count;
                        $monthlyTotals[$monthNumber] += (int) $count;
                    }
                }
            }

            if (in_array('pic', $columns, true)) {
                $picQuery = DB::table($table);
                $this->applyFilters($picQuery, $module, $columns, $filters, true, true, false);

                $picRows = $picQuery
                    ->select('pic', DB::raw('COUNT(*) as total'))
                    ->whereNotNull('pic')
                    ->where('pic', '!=', '')
                    ->groupBy('pic')
                    ->get();

                foreach ($picRows as $picRow) {
                    $picName = $picRow->pic ?: 'Tanpa PIC';
                    if (!isset($picScores[$picName])) {
                        $picScores[$picName] = [
                            'name' => $picName,
                            'total' => 0,
                            'modules' => [],
                        ];
                    }

                    $picScores[$picName]['total'] += (int) $picRow->total;
                    $picScores[$picName]['modules'][$module['label']] = (int) $picRow->total;
                }
            }

            $moduleStats[$key] = [
                'key' => $key,
                'label' => $module['label'],
                'table' => $table,
                'route' => $module['route'],
                'total' => $total,
                'year_total' => $yearTotal,
                'unique_units' => $uniqueUnits,
                'status_counts' => $statusCounts,
                'monthly' => $monthly,
            ];
        }

        usort($picScores, fn ($a, $b) => $b['total'] <=> $a['total']);
        $picScores = array_slice($picScores, 0, 10);

        $summary = [
            'selected_year' => $filters['year'],
            'total_records' => $filteredTotalRecords,
            'total_modules' => count($moduleStats),
            'peak_month_total' => max($monthlyTotals ?: [0]),
            'asset_active' => Schema::hasTable('unit_assets') ? DB::table('unit_assets')->whereRaw("UPPER(TRIM(COALESCE(status, ''))) <> 'DITARIK'")->count() : 0,
            'asset_withdrawn' => Schema::hasTable('unit_assets') ? DB::table('unit_assets')->whereRaw("UPPER(TRIM(COALESCE(status, ''))) = 'DITARIK'")->count() : 0,
        ];

        $years = $this->availableYears($modules);
        $filterOptions = $this->filterOptions($modules, $filters);
        $exportQuery = $this->exportQuery($filters);
        $performanceInsights = $this->performanceInsights($activeModules, $filters);

        return view('command-center.index', compact(
            'modules',
            'activeModules',
            'moduleStats',
            'summary',
            'monthlyTotals',
            'picScores',
            'filters',
            'years',
            'filterOptions',
            'exportQuery',
            'performanceInsights'
        ));
    }

    public function export(Request $request, string $module)
    {
        $this->authorizeCommandCenter();

        $modules = $this->modules();
        $moduleConfig = $this->moduleOrFail($module);
        $filters = $this->filters($request, $modules);
        $table = $moduleConfig['table'];

        abort_unless(Schema::hasTable($table), 404, 'Tabel tidak ditemukan.');

        $columns = Schema::getColumnListing($table);
        $filename = $module . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($table, $columns, $moduleConfig, $filters) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns);

            $query = DB::table($table);
            $this->applyFilters($query, $moduleConfig, $columns, $filters, true);

            if (in_array('id', $columns, true)) {
                $query->orderBy('id');
            }

            $query->chunk(500, function ($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($columns as $column) {
                        $line[] = $row->{$column} ?? null;
                    }
                    fputcsv($handle, $line);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request, string $module)
    {
        $this->authorizeCommandCenter();
        $moduleConfig = $this->moduleOrFail($module);
        $table = $moduleConfig['table'];

        abort_unless(Schema::hasTable($table), 404, 'Tabel tidak ditemukan.');

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->withErrors(['error' => 'File import tidak bisa dibaca.']);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->withErrors(['error' => 'Header CSV tidak ditemukan.']);
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headers = array_map(fn ($header) => trim((string) $header), $headers);

        $columns = Schema::getColumnListing($table);
        $allowedColumns = array_values(array_diff(array_intersect($headers, $columns), ['id']));

        if (empty($allowedColumns)) {
            fclose($handle);
            return back()->withErrors(['error' => 'Tidak ada kolom CSV yang cocok dengan tabel tujuan.']);
        }

        $inserted = 0;
        $skipped = 0;
        $batch = [];
        $now = now();

        DB::beginTransaction();

        try {
            while (($line = fgetcsv($handle)) !== false) {
                if (count(array_filter($line, fn ($value) => $value !== null && $value !== '')) === 0) {
                    $skipped++;
                    continue;
                }

                $line = array_pad($line, count($headers), null);
                $rowAssoc = array_combine($headers, array_slice($line, 0, count($headers)));
                $row = [];

                foreach ($allowedColumns as $column) {
                    $value = $rowAssoc[$column] ?? null;
                    $row[$column] = $value === '' ? null : $value;
                }

                if (in_array('created_at', $columns, true) && empty($row['created_at'])) {
                    $row['created_at'] = $now;
                }

                if (in_array('updated_at', $columns, true) && empty($row['updated_at'])) {
                    $row['updated_at'] = $now;
                }

                $batch[] = $row;

                if (count($batch) >= 200) {
                    DB::table($table)->insert($batch);
                    $inserted += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table($table)->insert($batch);
                $inserted += count($batch);
            }

            DB::commit();
            fclose($handle);

            return back()->with('success', "Import {$moduleConfig['label']} selesai. {$inserted} baris masuk, {$skipped} baris kosong dilewati.");
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            return back()->withErrors(['error' => 'Import gagal: ' . $e->getMessage()]);
        }
    }

    private function filters(Request $request, array $modules): array
    {
        $module = (string) $request->input('module', 'all');
        if ($module !== 'all' && !isset($modules[$module])) {
            $module = 'all';
        }

        $month = $request->input('month');
        $month = is_numeric($month) ? (int) $month : null;
        if ($month !== null && ($month < 1 || $month > 12)) {
            $month = null;
        }

        return [
            'module' => $module,
            'year' => (int) $request->input('year', now()->year),
            'month' => $month,
            'pic' => trim((string) $request->input('pic', '')),
            'customer' => trim((string) $request->input('customer', '')),
            'location' => trim((string) $request->input('location', '')),
            'status' => $this->normalizeStatus($request->input('status', '')),
        ];
    }

    private function activeModules(array $modules, string $selectedModule): array
    {
        if ($selectedModule !== 'all' && isset($modules[$selectedModule])) {
            return [$selectedModule => $modules[$selectedModule]];
        }

        return $modules;
    }

    private function applyFilters(
        Builder $query,
        array $module,
        array $columns,
        array $filters,
        bool $applyMonth = true,
        bool $applyStatus = true,
        bool $applyPic = true
    ): void {
        $dateColumn = $module['date_column'];
        $statusColumn = $module['status_column'];

        if (in_array($dateColumn, $columns, true)) {
            $query->whereYear($dateColumn, $filters['year']);

            if ($applyMonth && !empty($filters['month'])) {
                $query->whereMonth($dateColumn, $filters['month']);
            }
        }

        if ($applyPic && $filters['pic'] !== '') {
            in_array('pic', $columns, true)
                ? $query->where('pic', $filters['pic'])
                : $query->whereRaw('1 = 0');
        }

        if ($filters['customer'] !== '') {
            in_array('customer', $columns, true)
                ? $query->where('customer', $filters['customer'])
                : $query->whereRaw('1 = 0');
        }

        if ($filters['location'] !== '') {
            in_array('location', $columns, true)
                ? $query->where('location', $filters['location'])
                : $query->whereRaw('1 = 0');
        }

        if ($applyStatus && $filters['status'] !== '' && $filters['status'] !== 'Tanpa Status') {
            in_array($statusColumn, $columns, true)
                ? $this->applyStatusFilter($query, $statusColumn, $filters['status'])
                : $query->whereRaw('1 = 0');
        }
    }

    private function applyStatusFilter(Builder $query, string $column, string $status, ?string $alias = null): void
    {
        $qualifiedColumn = $alias ? $alias . '.' . $column : $column;
        $status = $this->normalizeStatus($status);

        $query->where(function ($q) use ($qualifiedColumn, $status) {
            if ($status === 'RFU') {
                $q->whereRaw("UPPER(TRIM(COALESCE({$qualifiedColumn}, ''))) = 'RFU'");
                return;
            }

            if ($status === 'Breakdown') {
                $q->whereRaw("UPPER(TRIM(COALESCE({$qualifiedColumn}, ''))) LIKE '%BREAKDOWN%'")
                    ->orWhereRaw("UPPER(TRIM(COALESCE({$qualifiedColumn}, ''))) IN ('B/D', 'BD')");
                return;
            }

            if ($status === 'Monitoring') {
                $q->whereRaw("UPPER(TRIM(COALESCE({$qualifiedColumn}, ''))) IN ('MONITORING', 'STANDBY')");
                return;
            }

            if ($status === 'Waiting Part') {
                $q->whereRaw("UPPER(TRIM(COALESCE({$qualifiedColumn}, ''))) IN ('WAITING PART', 'WAITING_PART', 'WAITING-PART')");
                return;
            }

            $q->where($qualifiedColumn, $status);
        });
    }

    private function filterOptions(array $modules, array $filters): array
    {
        $activeModules = $this->activeModules($modules, $filters['module']);
        $options = [
            'pics' => [],
            'customers' => [],
            'locations' => [],
            'statuses' => $this->statusUnitOptions(),
        ];

        foreach ($activeModules as $module) {
            $table = $module['table'];
            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);

            if (in_array('pic', $columns, true)) {
                $options['pics'] = array_merge($options['pics'], DB::table($table)->whereNotNull('pic')->where('pic', '!=', '')->distinct()->pluck('pic')->toArray());
            }

            if (in_array('customer', $columns, true)) {
                $options['customers'] = array_merge($options['customers'], DB::table($table)->whereNotNull('customer')->where('customer', '!=', '')->distinct()->pluck('customer')->toArray());
            }

            if (in_array('location', $columns, true)) {
                $options['locations'] = array_merge($options['locations'], DB::table($table)->whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location')->toArray());
            }
        }

        foreach ($options as $key => $values) {
            $values = array_values(array_unique(array_filter($values, fn ($value) => $value !== null && $value !== '')));
            sort($values, SORT_NATURAL | SORT_FLAG_CASE);
            $options[$key] = $values;
        }

        return $options;
    }

    private function performanceInsights(array $activeModules, array $filters): array
    {
        return [
            'monthly_productivity' => $this->monthlyProductivity($activeModules, $filters),
            'customer_location_load' => $this->customerLocationLoad($activeModules, $filters),
            'status_distribution' => $this->statusDistribution($activeModules, $filters),
            'job_type_distribution' => $this->jobTypeDistribution($activeModules, $filters),
            'status_by_pic' => $this->statusByPic($activeModules, $filters),
            'rfu_breakdown_ratio' => $this->statusByPic($activeModules, $filters),
            'troubled_units' => $this->troubledUnits($activeModules, $filters),
            'top_recommendations' => $this->topRecommendations($activeModules, $filters),
        ];
    }

    private function monthlyProductivity(array $activeModules, array $filters): array
    {
        $scores = [];

        foreach ($activeModules as $module) {
            if (!Schema::hasTable($module['table'])) {
                continue;
            }

            $columns = Schema::getColumnListing($module['table']);
            if (!in_array('pic', $columns, true) || !in_array($module['date_column'], $columns, true)) {
                continue;
            }

            $query = DB::table($module['table']);
            $this->applyFilters($query, $module, $columns, $filters, true, true, false);

            $rows = $query
                ->select('pic', DB::raw("MONTH({$module['date_column']}) as month_number"), DB::raw('COUNT(*) as total'))
                ->whereNotNull('pic')
                ->where('pic', '!=', '')
                ->groupBy('pic', 'month_number')
                ->get();

            foreach ($rows as $row) {
                $key = $row->pic . '|' . (int) $row->month_number;
                if (!isset($scores[$key])) {
                    $scores[$key] = [
                        'pic' => $row->pic,
                        'month' => (int) $row->month_number,
                        'total' => 0,
                    ];
                }

                $scores[$key]['total'] += (int) $row->total;
            }
        }

        usort($scores, fn ($a, $b) => $b['total'] <=> $a['total']);

        return array_slice(array_values($scores), 0, 12);
    }

    private function customerLocationLoad(array $activeModules, array $filters): array
    {
        $loads = [];

        foreach ($activeModules as $module) {
            if (!Schema::hasTable($module['table'])) {
                continue;
            }

            $columns = Schema::getColumnListing($module['table']);
            if (!in_array('customer', $columns, true) || !in_array('location', $columns, true)) {
                continue;
            }

            $query = DB::table($module['table']);
            $this->applyFilters($query, $module, $columns, $filters, true);

            $rows = $query
                ->select('customer', 'location', DB::raw('COUNT(*) as total'))
                ->groupBy('customer', 'location')
                ->orderByDesc('total')
                ->limit(30)
                ->get();

            foreach ($rows as $row) {
                $customer = $row->customer ?: 'Tanpa Customer';
                $location = $row->location ?: 'Tanpa Location';
                $key = $customer . '|' . $location;

                if (!isset($loads[$key])) {
                    $loads[$key] = [
                        'customer' => $customer,
                        'location' => $location,
                        'total' => 0,
                    ];
                }

                $loads[$key]['total'] += (int) $row->total;
            }
        }

        usort($loads, fn ($a, $b) => $b['total'] <=> $a['total']);

        return array_slice(array_values($loads), 0, 10);
    }

    private function statusDistribution(array $activeModules, array $filters): array
    {
        $distribution = array_fill_keys($this->statusUnitOptions(), 0);

        foreach ($activeModules as $module) {
            if (!Schema::hasTable($module['table'])) {
                continue;
            }

            $columns = Schema::getColumnListing($module['table']);
            $statusColumn = $module['status_column'];
            if (!in_array($statusColumn, $columns, true)) {
                continue;
            }

            $query = DB::table($module['table']);
            $this->applyFilters($query, $module, $columns, $filters, true, false);

            $rows = $query
                ->select($statusColumn, DB::raw('COUNT(*) as total'))
                ->whereNotNull($statusColumn)
                ->where($statusColumn, '!=', '')
                ->groupBy($statusColumn)
                ->get();

            foreach ($rows as $row) {
                $status = $this->normalizeStatus($row->{$statusColumn});
                if (!isset($distribution[$status])) {
                    $distribution[$status] = 0;
                }
                $distribution[$status] += (int) $row->total;
            }
        }

        return collect($distribution)
            ->map(fn ($total, $status) => ['status' => $status, 'total' => (int) $total])
            ->values()
            ->all();
    }

    private function jobTypeDistribution(array $activeModules, array $filters): array
    {
        if (!isset($activeModules['update-jobs']) || !Schema::hasTable('update_jobs')) {
            return collect($this->jobTypeOptions())->map(fn ($type) => ['job_type' => $type, 'total' => 0])->all();
        }

        $module = $activeModules['update-jobs'];
        $columns = Schema::getColumnListing('update_jobs');
        if (!in_array('job_type', $columns, true)) {
            return [];
        }

        $query = DB::table('update_jobs');
        $this->applyFilters($query, $module, $columns, $filters, true);

        $rows = $query
            ->select('job_type', DB::raw('COUNT(*) as total'))
            ->whereNotNull('job_type')
            ->where('job_type', '!=', '')
            ->groupBy('job_type')
            ->get();

        $distribution = array_fill_keys($this->jobTypeOptions(), 0);
        foreach ($rows as $row) {
            $jobType = $this->normalizeJobType($row->job_type);
            if (!isset($distribution[$jobType])) {
                $distribution[$jobType] = 0;
            }
            $distribution[$jobType] += (int) $row->total;
        }

        return collect($distribution)
            ->map(fn ($total, $jobType) => ['job_type' => $jobType, 'total' => (int) $total])
            ->values()
            ->all();
    }

    private function statusByPic(array $activeModules, array $filters): array
    {
        $ratios = [];

        foreach ($activeModules as $module) {
            if (!Schema::hasTable($module['table'])) {
                continue;
            }

            $columns = Schema::getColumnListing($module['table']);
            $statusColumn = $module['status_column'];
            if (!in_array('pic', $columns, true) || !in_array($statusColumn, $columns, true)) {
                continue;
            }

            $query = DB::table($module['table']);
            $this->applyFilters($query, $module, $columns, $filters, true, false, false);

            $rows = $query
                ->select('pic', $statusColumn, DB::raw('COUNT(*) as total'))
                ->whereNotNull('pic')
                ->where('pic', '!=', '')
                ->whereNotNull($statusColumn)
                ->where($statusColumn, '!=', '')
                ->groupBy('pic', $statusColumn)
                ->get();

            foreach ($rows as $row) {
                $pic = $row->pic ?: 'Tanpa PIC';
                if (!isset($ratios[$pic])) {
                    $ratios[$pic] = [
                        'pic' => $pic,
                        'rfu' => 0,
                        'breakdown' => 0,
                        'monitoring' => 0,
                        'waiting_part' => 0,
                        'other' => 0,
                        'total' => 0,
                        'rfu_rate' => 0,
                        'risk_rate' => 0,
                    ];
                }

                $status = $this->normalizeStatus($row->{$statusColumn});
                $count = (int) $row->total;

                match ($status) {
                    'RFU' => $ratios[$pic]['rfu'] += $count,
                    'Breakdown' => $ratios[$pic]['breakdown'] += $count,
                    'Monitoring' => $ratios[$pic]['monitoring'] += $count,
                    'Waiting Part' => $ratios[$pic]['waiting_part'] += $count,
                    default => $ratios[$pic]['other'] += $count,
                };

                $ratios[$pic]['total'] += $count;
            }
        }

        foreach ($ratios as &$ratio) {
            $ratio['rfu_rate'] = $ratio['total'] > 0 ? round(($ratio['rfu'] / $ratio['total']) * 100, 1) : 0;
            $riskTotal = $ratio['breakdown'] + $ratio['waiting_part'];
            $ratio['risk_rate'] = $ratio['total'] > 0 ? round(($riskTotal / $ratio['total']) * 100, 1) : 0;
        }
        unset($ratio);

        usort($ratios, fn ($a, $b) => $b['total'] <=> $a['total']);

        return array_slice(array_values($ratios), 0, 10);
    }

    private function troubledUnits(array $activeModules, array $filters): array
    {
        $units = [];

        foreach ($activeModules as $module) {
            if ($module['table'] === 'unit_assets' || !Schema::hasTable($module['table'])) {
                continue;
            }

            $columns = Schema::getColumnListing($module['table']);
            if (!in_array('serial_number', $columns, true)) {
                continue;
            }

            $query = DB::table($module['table']);
            $this->applyFilters($query, $module, $columns, $filters, true);

            if (in_array('problem', $columns, true)) {
                $query->where(function ($q) use ($module) {
                    $q->whereNotNull('problem')
                        ->where('problem', '!=', '')
                        ->orWhereRaw("UPPER(TRIM(COALESCE({$module['status_column']}, ''))) LIKE '%BREAKDOWN%'")
                        ->orWhereRaw("UPPER(TRIM(COALESCE({$module['status_column']}, ''))) IN ('B/D', 'BD')")
                        ->orWhereRaw("UPPER(TRIM(COALESCE({$module['status_column']}, ''))) IN ('WAITING PART', 'WAITING_PART', 'WAITING-PART')");
                });
            } elseif (in_array($module['status_column'], $columns, true)) {
                $query->where(function ($q) use ($module) {
                    $q->whereRaw("UPPER(TRIM(COALESCE({$module['status_column']}, ''))) LIKE '%BREAKDOWN%'")
                        ->orWhereRaw("UPPER(TRIM(COALESCE({$module['status_column']}, ''))) IN ('B/D', 'BD')")
                        ->orWhereRaw("UPPER(TRIM(COALESCE({$module['status_column']}, ''))) IN ('WAITING PART', 'WAITING_PART', 'WAITING-PART')");
                });
            }

            $rows = $query
                ->select('serial_number', DB::raw('MAX(unit_type) as unit_type'), DB::raw('MAX(customer) as customer'), DB::raw('MAX(location) as location'), DB::raw('COUNT(*) as total'))
                ->whereNotNull('serial_number')
                ->where('serial_number', '!=', '')
                ->groupBy('serial_number')
                ->orderByDesc('total')
                ->limit(30)
                ->get();

            foreach ($rows as $row) {
                $serialNumber = $row->serial_number;
                if (!isset($units[$serialNumber])) {
                    $units[$serialNumber] = [
                        'serial_number' => $serialNumber,
                        'unit_type' => $row->unit_type ?? '-',
                        'customer' => $row->customer ?? '-',
                        'location' => $row->location ?? '-',
                        'total' => 0,
                    ];
                }

                $units[$serialNumber]['total'] += (int) $row->total;
            }
        }

        usort($units, fn ($a, $b) => $b['total'] <=> $a['total']);

        return array_slice(array_values($units), 0, 10);
    }

    private function topRecommendations(array $activeModules, array $filters): array
    {
        $configs = [
            'update-jobs' => ['recommendation_table' => 'job_recommendations', 'foreign_key' => 'job_id', 'parent_id' => 'id'],
            'batteries' => ['recommendation_table' => 'battery_recommendations', 'foreign_key' => 'battery_id', 'parent_id' => 'id'],
            'chargers' => ['recommendation_table' => 'charger_recommendations', 'foreign_key' => 'charger_id', 'parent_id' => 'id'],
        ];

        $parts = [];

        foreach ($configs as $moduleKey => $config) {
            if (!isset($activeModules[$moduleKey])) {
                continue;
            }

            $module = $activeModules[$moduleKey];
            if (!Schema::hasTable($module['table']) || !Schema::hasTable($config['recommendation_table'])) {
                continue;
            }

            $columns = Schema::getColumnListing($module['table']);
            $query = DB::table($config['recommendation_table'] . ' as r')
                ->join($module['table'] . ' as p', 'p.' . $config['parent_id'], '=', 'r.' . $config['foreign_key']);

            $this->applyAliasedFilters($query, 'p', $module, $columns, $filters, true);

            $rows = $query
                ->select('r.part_number', 'r.part_name', DB::raw('SUM(COALESCE(r.qty, 0)) as qty_total'), DB::raw('COUNT(*) as total'))
                ->whereNotNull('r.part_name')
                ->where('r.part_name', '!=', '')
                ->groupBy('r.part_number', 'r.part_name')
                ->orderByDesc('qty_total')
                ->limit(30)
                ->get();

            foreach ($rows as $row) {
                $key = ($row->part_number ?: '-') . '|' . $row->part_name;
                if (!isset($parts[$key])) {
                    $parts[$key] = [
                        'part_number' => $row->part_number ?: '-',
                        'part_name' => $row->part_name,
                        'qty_total' => 0,
                        'total' => 0,
                    ];
                }

                $parts[$key]['qty_total'] += (int) $row->qty_total;
                $parts[$key]['total'] += (int) $row->total;
            }
        }

        usort($parts, fn ($a, $b) => $b['qty_total'] <=> $a['qty_total']);

        return array_slice(array_values($parts), 0, 10);
    }

    private function applyAliasedFilters(Builder $query, string $alias, array $module, array $columns, array $filters, bool $applyMonth = true): void
    {
        $dateColumn = $module['date_column'];
        $statusColumn = $module['status_column'];

        if (in_array($dateColumn, $columns, true)) {
            $query->whereYear($alias . '.' . $dateColumn, $filters['year']);

            if ($applyMonth && !empty($filters['month'])) {
                $query->whereMonth($alias . '.' . $dateColumn, $filters['month']);
            }
        }

        if ($filters['pic'] !== '') {
            in_array('pic', $columns, true)
                ? $query->where($alias . '.pic', $filters['pic'])
                : $query->whereRaw('1 = 0');
        }

        if ($filters['customer'] !== '') {
            in_array('customer', $columns, true)
                ? $query->where($alias . '.customer', $filters['customer'])
                : $query->whereRaw('1 = 0');
        }

        if ($filters['location'] !== '') {
            in_array('location', $columns, true)
                ? $query->where($alias . '.location', $filters['location'])
                : $query->whereRaw('1 = 0');
        }

        if ($filters['status'] !== '' && $filters['status'] !== 'Tanpa Status') {
            in_array($statusColumn, $columns, true)
                ? $this->applyStatusFilter($query, $statusColumn, $filters['status'], $alias)
                : $query->whereRaw('1 = 0');
        }
    }

    private function exportQuery(array $filters): array
    {
        return array_filter([
            'year' => $filters['year'],
            'month' => $filters['month'],
            'pic' => $filters['pic'],
            'customer' => $filters['customer'],
            'location' => $filters['location'],
            'status' => $filters['status'],
        ], fn ($value) => $value !== null && $value !== '' && $value !== 'Tanpa Status');
    }

    private function moduleOrFail(string $module): array
    {
        $modules = $this->modules();
        abort_unless(isset($modules[$module]), 404, 'Modul tidak dikenal.');

        return $modules[$module];
    }

    private function availableYears(array $modules): array
    {
        $years = [now()->year];

        foreach ($modules as $module) {
            $table = $module['table'];
            $dateColumn = $module['date_column'];

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $dateColumn)) {
                continue;
            }

            $rows = DB::table($table)
                ->selectRaw("YEAR({$dateColumn}) as year")
                ->whereNotNull($dateColumn)
                ->distinct()
                ->pluck('year')
                ->filter()
                ->toArray();

            $years = array_merge($years, $rows);
        }

        $years = array_values(array_unique(array_map('intval', $years)));
        rsort($years);

        return $years;
    }
}
