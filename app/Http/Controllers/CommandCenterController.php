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

                $statusCounts = $statusQuery
                    ->select($statusColumn, DB::raw('COUNT(*) as total'))
                    ->whereNotNull($statusColumn)
                    ->where($statusColumn, '!=', '')
                    ->groupBy($statusColumn)
                    ->orderByDesc('total')
                    ->limit(10)
                    ->pluck('total', $statusColumn)
                    ->toArray();
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

        return view('command-center.index', compact('modules', 'activeModules', 'moduleStats', 'summary', 'monthlyTotals', 'picScores', 'filters', 'years', 'filterOptions', 'exportQuery'));
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
            'status' => trim((string) $request->input('status', '')),
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

        if ($applyStatus && $filters['status'] !== '') {
            in_array($statusColumn, $columns, true)
                ? $query->where($statusColumn, $filters['status'])
                : $query->whereRaw('1 = 0');
        }
    }

    private function filterOptions(array $modules, array $filters): array
    {
        $activeModules = $this->activeModules($modules, $filters['module']);
        $options = [
            'pics' => [],
            'customers' => [],
            'locations' => [],
            'statuses' => [],
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

            $statusColumn = $module['status_column'];
            if (in_array($statusColumn, $columns, true)) {
                $options['statuses'] = array_merge($options['statuses'], DB::table($table)->whereNotNull($statusColumn)->where($statusColumn, '!=', '')->distinct()->pluck($statusColumn)->toArray());
            }
        }

        foreach ($options as $key => $values) {
            $values = array_values(array_unique(array_filter($values, fn ($value) => $value !== null && $value !== '')));
            sort($values, SORT_NATURAL | SORT_FLAG_CASE);
            $options[$key] = $values;
        }

        return $options;
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
        ], fn ($value) => $value !== null && $value !== '');
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
