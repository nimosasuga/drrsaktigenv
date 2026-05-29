<?php
// PATH FILE: app/Http/Controllers/CommandCenterController.php

namespace App\Http\Controllers;

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
        $roleText = $this->roleText();
        $roleText = str_replace(['-', '_'], ' ', $roleText);

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

        $selectedYear = (int) $request->input('year', now()->year);
        $modules = $this->modules();
        $moduleStats = [];
        $monthlyTotals = array_fill(1, 12, 0);
        $picScores = [];

        foreach ($modules as $key => $module) {
            $table = $module['table'];

            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $dateColumn = $module['date_column'];
            $unitColumn = $module['unit_column'];
            $statusColumn = $module['status_column'];

            $baseQuery = DB::table($table);
            $yearQuery = DB::table($table);

            if (in_array($dateColumn, $columns, true)) {
                $yearQuery->whereYear($dateColumn, $selectedYear);
            }

            $total = (clone $baseQuery)->count();
            $yearTotal = (clone $yearQuery)->count();
            $uniqueUnits = in_array($unitColumn, $columns, true)
                ? (clone $yearQuery)->whereNotNull($unitColumn)->where($unitColumn, '!=', '')->distinct()->count($unitColumn)
                : 0;

            $statusCounts = [];
            if (in_array($statusColumn, $columns, true)) {
                $statusCounts = (clone $yearQuery)
                    ->select($statusColumn, DB::raw('COUNT(*) as total'))
                    ->whereNotNull($statusColumn)
                    ->where($statusColumn, '!=', '')
                    ->groupBy($statusColumn)
                    ->orderByDesc('total')
                    ->limit(8)
                    ->pluck('total', $statusColumn)
                    ->toArray();
            }

            $monthly = array_fill(1, 12, 0);
            if (in_array($dateColumn, $columns, true)) {
                $rows = DB::table($table)
                    ->selectRaw("MONTH({$dateColumn}) as month_number, COUNT(*) as total")
                    ->whereYear($dateColumn, $selectedYear)
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
                $picRows = (clone $yearQuery)
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
            'selected_year' => $selectedYear,
            'total_records' => array_sum(array_column($moduleStats, 'year_total')),
            'total_modules' => count($moduleStats),
            'peak_month_total' => max($monthlyTotals ?: [0]),
            'asset_active' => Schema::hasTable('unit_assets') ? DB::table('unit_assets')->whereRaw("UPPER(TRIM(COALESCE(status, ''))) <> 'DITARIK'")->count() : 0,
            'asset_withdrawn' => Schema::hasTable('unit_assets') ? DB::table('unit_assets')->whereRaw("UPPER(TRIM(COALESCE(status, ''))) = 'DITARIK'")->count() : 0,
        ];

        $years = $this->availableYears($modules);

        return view('command-center.index', compact('modules', 'moduleStats', 'summary', 'monthlyTotals', 'picScores', 'selectedYear', 'years'));
    }

    public function export(string $module)
    {
        $this->authorizeCommandCenter();
        $moduleConfig = $this->moduleOrFail($module);
        $table = $moduleConfig['table'];

        abort_unless(Schema::hasTable($table), 404, 'Tabel tidak ditemukan.');

        $columns = Schema::getColumnListing($table);
        $filename = $module . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($table, $columns) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns);

            $query = DB::table($table);
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
