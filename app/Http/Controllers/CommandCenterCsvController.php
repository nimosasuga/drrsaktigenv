<?php
// PATH FILE: app/Http/Controllers/CommandCenterCsvController.php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommandCenterCsvController extends Controller
{
    private const EXPORT_DELIMITER = ';';

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
        $delimiter = self::EXPORT_DELIMITER;

        return response()->streamDownload(function () use ($table, $columns, $moduleConfig, $filters, $delimiter) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns, $delimiter);

            $query = DB::table($table);
            $this->applyFilters($query, $moduleConfig, $columns, $filters, true);

            if (in_array('id', $columns, true)) {
                $query->orderBy('id');
            }

            $query->chunk(500, function ($rows) use ($handle, $columns, $delimiter) {
                foreach ($rows as $row) {
                    $line = [];

                    foreach ($columns as $column) {
                        $line[] = $row->{$column} ?? null;
                    }

                    fputcsv($handle, $line, $delimiter);
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
        $delimiter = $this->detectDelimiter($path);
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->withErrors(['error' => 'File import tidak bisa dibaca.']);
        }

        $headers = fgetcsv($handle, 0, $delimiter);

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
            return back()->withErrors(['error' => 'Tidak ada kolom CSV yang cocok dengan tabel tujuan. Pastikan header CSV tidak menyatu dalam satu kolom dan memakai nama kolom database.']);
        }

        $inserted = 0;
        $skipped = 0;
        $batch = [];
        $now = now();

        DB::beginTransaction();

        try {
            while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
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

        abort_unless(isset($modules[$module]), 404, 'Modul tidak ditemukan.');

        return $modules[$module];
    }

    private function detectDelimiter(string $path): string
    {
        $sample = (string) file_get_contents($path, false, null, 0, 4096);
        $firstLine = strtok($sample, "\r\n") ?: '';

        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');
        $tabCount = substr_count($firstLine, "\t");

        if ($tabCount > $semicolonCount && $tabCount > $commaCount) {
            return "\t";
        }

        return $semicolonCount >= $commaCount ? ';' : ',';
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
