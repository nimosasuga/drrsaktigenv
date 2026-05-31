<?php
// PATH FILE: app/Http/Controllers/CommandCenterCsvController.php

namespace App\Http\Controllers;

use App\Support\DepartmentScope;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommandCenterCsvController extends Controller
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
            'assets' => ['label' => 'Manajemen Aset', 'table' => 'unit_assets', 'date_column' => 'created_at', 'status_column' => 'status'],
            'update-jobs' => ['label' => 'Update Job', 'table' => 'update_jobs', 'date_column' => 'work_date', 'status_column' => 'status_unit'],
            'batteries' => ['label' => 'Management Battery', 'table' => 'batteries', 'date_column' => 'date', 'status_column' => 'status_unit'],
            'chargers' => ['label' => 'Management Charger', 'table' => 'chargers', 'date_column' => 'date', 'status_column' => 'status_unit'],
            'deliveries' => ['label' => 'Delivery Unit', 'table' => 'deliveries', 'date_column' => 'date', 'status_column' => 'status_unit'],
            'penarikans' => ['label' => 'Penarikan Unit', 'table' => 'penarikans', 'date_column' => 'date', 'status_column' => 'status_unit'],
        ];
    }

    public function export(Request $request, string $module)
    {
        $this->authorizeCommandCenter();

        $moduleConfig = $this->moduleOrFail($module);
        $table = $moduleConfig['table'];

        abort_unless(Schema::hasTable($table), 404, 'Tabel tidak ditemukan.');

        $columns = Schema::getColumnListing($table);
        $filters = $this->filters($request);
        $filename = $module . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($table, $columns, $moduleConfig, $filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns, ';');

            $query = DB::table($table);
            $this->applyFilters($query, $moduleConfig, $columns, $filters);

            if (in_array('id', $columns, true)) {
                $query->orderBy('id');
            }

            $query->chunk(500, function ($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($columns as $column) {
                        $line[] = $row->{$column} ?? null;
                    }
                    fputcsv($handle, $line, ';');
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, string $module)
    {
        $this->authorizeCommandCenter();

        $moduleConfig = $this->moduleOrFail($module);
        $table = $moduleConfig['table'];

        abort_unless(Schema::hasTable($table), 404, 'Tabel tidak ditemukan.');

        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->withErrors(['error' => 'File import tidak bisa dibaca.']);
        }

        $headers = fgetcsv($handle, 0, ';');

        if (!$headers) {
            fclose($handle);
            return back()->withErrors(['error' => 'Header CSV tidak ditemukan.']);
        }

        $headers = array_map(fn ($header) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)), $headers);
        $columns = Schema::getColumnListing($table);
        $allowedColumns = array_values(array_diff(array_intersect($headers, $columns), ['id']));

        if (!DepartmentScope::userCanSeeAllDepartments() && in_array('department', $columns, true) && !in_array('department', $allowedColumns, true)) {
            $allowedColumns[] = 'department';
        }

        if (empty($allowedColumns)) {
            fclose($handle);
            return back()->withErrors(['error' => 'Tidak ada kolom CSV yang cocok dengan tabel tujuan.']);
        }

        $inserted = 0;
        $skipped = 0;
        $batch = [];
        $now = now();
        $forcedDepartment = DepartmentScope::userCanSeeAllDepartments() ? null : DepartmentScope::currentDepartment();

        DB::beginTransaction();

        try {
            while (($line = fgetcsv($handle, 0, ';')) !== false) {
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

                if ($forcedDepartment && in_array('department', $columns, true)) {
                    $row['department'] = $forcedDepartment;
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
        abort_unless(isset($modules[$module]), 404, 'Modul tidak ditemukan.');
        return $modules[$module];
    }

    private function filters(Request $request): array
    {
        $month = $request->input('month');
        $month = is_numeric($month) ? (int) $month : null;

        return [
            'year' => (int) $request->input('year', now()->year),
            'month' => $month,
            'pic' => trim((string) $request->input('pic', '')),
            'customer' => trim((string) $request->input('customer', '')),
            'location' => trim((string) $request->input('location', '')),
            'status' => $this->normalizeStatus($request->input('status', '')),
        ];
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

    private function applyFilters(Builder $query, array $module, array $columns, array $filters): void
    {
        DepartmentScope::apply($query, $module['table']);

        $dateColumn = $module['date_column'];
        $statusColumn = $module['status_column'];

        if (in_array($dateColumn, $columns, true)) {
            $query->whereYear($dateColumn, $filters['year']);

            if (!empty($filters['month'])) {
                $query->whereMonth($dateColumn, $filters['month']);
            }
        }

        foreach (['pic', 'customer', 'location'] as $column) {
            if (($filters[$column] ?? '') !== '') {
                in_array($column, $columns, true)
                    ? $query->where($column, $filters[$column])
                    : $query->whereRaw('1 = 0');
            }
        }

        if ($filters['status'] !== '' && $filters['status'] !== 'Tanpa Status') {
            in_array($statusColumn, $columns, true)
                ? $this->applyStatusFilter($query, $statusColumn, $filters['status'])
                : $query->whereRaw('1 = 0');
        }
    }

    private function applyStatusFilter(Builder $query, string $column, string $status): void
    {
        $status = $this->normalizeStatus($status);

        $query->where(function ($q) use ($column, $status) {
            if ($status === 'RFU') {
                $q->whereRaw("UPPER(TRIM(COALESCE({$column}, ''))) = 'RFU'");
                return;
            }

            if ($status === 'Breakdown') {
                $q->whereRaw("UPPER(TRIM(COALESCE({$column}, ''))) LIKE '%BREAKDOWN%'")
                    ->orWhereRaw("UPPER(TRIM(COALESCE({$column}, ''))) IN ('B/D', 'BD')");
                return;
            }

            if ($status === 'Monitoring') {
                $q->whereRaw("UPPER(TRIM(COALESCE({$column}, ''))) IN ('MONITORING', 'STANDBY')");
                return;
            }

            if ($status === 'Waiting Part') {
                $q->whereRaw("UPPER(TRIM(COALESCE({$column}, ''))) IN ('WAITING PART', 'WAITING_PART', 'WAITING-PART')");
                return;
            }

            $q->where($column, $status);
        });
    }
}
