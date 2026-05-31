<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartImportController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartItem;
use App\Models\RentalSparepartLocation;
use App\Models\RentalSparepartMovement;
use App\Models\RentalSparepartStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class RentalSparepartImportController extends Controller
{
    private const DEPARTMENT = 'RENTAL';
    private const SESSION_KEY = 'rental_sparepart_import_preview_rows';

    private const REQUIRED_HEADERS = [
        'tanggal',
        'part_number',
        'part_name',
        'qty_masuk',
        'location_code',
    ];

    private const TEMPLATE_HEADERS = [
        'tanggal',
        'no_job',
        'part_number',
        'part_name',
        'qty_masuk',
        'default_type_unit',
        'min_stock',
        'location_code',
        'location_name',
        'cabinet',
        'shelf',
        'box',
        'source_customer',
        'source_location',
        'source_type_unit',
        'source_sn_unit',
        'allocation_customer',
        'allocation_location',
        'allocation_type_unit',
        'allocation_sn_unit',
        'remarks',
    ];

    public function store(Request $request)
    {
        return $this->preview($request);
    }

    public function preview(Request $request)
    {
        abort_unless($this->canManage(), 403, 'Hanya koordinator/sect head RENTAL, admin, dan super admin yang bisa import sparepart.');

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $filePath = $request->file('csv_file')->getRealPath();
        $parsed = $this->parseCsv($filePath);

        if (!empty($parsed['errors'])) {
            session()->forget(self::SESSION_KEY);

            return back()->withErrors(['csv_file' => implode(' | ', array_slice($parsed['errors'], 0, 10))]);
        }

        $rows = $parsed['rows'];

        if (count($rows) === 0) {
            session()->forget(self::SESSION_KEY);

            return back()->withErrors(['csv_file' => 'CSV kosong. Minimal harus ada 1 baris data.']);
        }

        session()->put(self::SESSION_KEY, $rows);

        $summary = $this->previewSummary($rows);
        $previewRows = array_slice($rows, 0, 20);

        return view('rental-spareparts.import-preview', compact('summary', 'previewRows'));
    }

    public function confirm(Request $request)
    {
        abort_unless($this->canManage(), 403, 'Hanya koordinator/sect head RENTAL, admin, dan super admin yang bisa confirm import sparepart.');

        $rows = session(self::SESSION_KEY, []);

        if (empty($rows)) {
            return redirect()
                ->route('rental-spareparts.index')
                ->withErrors(['csv_file' => 'Preview import tidak ditemukan atau sudah kedaluwarsa. Upload ulang CSV.']);
        }

        try {
            DB::transaction(function () use ($rows) {
                $user = Auth::user();

                foreach ($rows as $row) {
                    $this->importRow($row, $user);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['csv_file' => 'Import gagal. Tidak ada data yang disimpan. Error: ' . $exception->getMessage()]);
        }

        session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('rental-spareparts.index')
            ->with('success', count($rows) . ' baris sparepart berhasil di-import sebagai Barang Masuk.');
    }

    public function cancel()
    {
        abort_unless($this->canManage(), 403);

        session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('rental-spareparts.index')
            ->with('success', 'Preview import dibatalkan. Tidak ada data yang disimpan.');
    }

    public function template()
    {
        abort_unless($this->canManage(), 403);

        $example = [
            now()->toDateString(),
            'JOB-001',
            'PN-001',
            'FILTER HYDRAULIC',
            '1',
            'FD30',
            '1',
            'LEMARI-1',
            'LEMARI 1',
            'LEMARI 1',
            'RAK A',
            'BOX 1',
            'CUSTOMER A',
            'SITE A',
            'FD30',
            'SN123456',
            'CUSTOMER A',
            'SITE A',
            'FD30',
            'SN123456',
            'STOK AWAL IMPORT',
        ];

        $rows = [self::TEMPLATE_HEADERS, $example];
        $csv = "\xEF\xBB\xBF" . collect($rows)->map(function ($row) {
            return collect($row)->map(fn ($value) => $this->csvCell($value))->implode(',');
        })->implode("\n");

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rental_sparepart_import_template.csv"',
        ]);
    }

    private function previewSummary(array $rows): array
    {
        $partNumbers = collect($rows)->pluck('part_number')->map(fn ($value) => $this->upper($value))->filter()->unique()->values();
        $locationCodes = collect($rows)->pluck('location_code')->map(fn ($value) => $this->upper($value))->filter()->unique()->values();

        $existingParts = RentalSparepartItem::query()
            ->where('department', self::DEPARTMENT)
            ->whereIn('part_number', $partNumbers)
            ->pluck('part_number')
            ->map(fn ($value) => $this->upper($value))
            ->unique()
            ->values();

        $existingLocations = RentalSparepartLocation::query()
            ->where('department', self::DEPARTMENT)
            ->whereIn('location_code', $locationCodes)
            ->pluck('location_code')
            ->map(fn ($value) => $this->upper($value))
            ->unique()
            ->values();

        $mergeStockRows = collect($rows)->filter(function ($row) {
            $item = RentalSparepartItem::query()
                ->where('department', self::DEPARTMENT)
                ->where('part_number', $this->upper($row['part_number']))
                ->first();

            $location = RentalSparepartLocation::query()
                ->where('department', self::DEPARTMENT)
                ->where('location_code', $this->upper($row['location_code']))
                ->first();

            if (!$item || !$location) {
                return false;
            }

            return RentalSparepartStock::query()
                ->where('department', self::DEPARTMENT)
                ->where('sparepart_item_id', $item->id)
                ->where('location_id', $location->id)
                ->where('source_no_job', $this->nullableUpper($row['no_job'] ?? null))
                ->where('source_customer', $this->nullableUpper($row['source_customer'] ?? null))
                ->where('source_location', $this->nullableUpper($row['source_location'] ?? null))
                ->where('source_type_unit', $this->nullableUpper($row['source_type_unit'] ?? null))
                ->where('source_sn_unit', $this->nullableUpper($row['source_sn_unit'] ?? null))
                ->where('allocation_customer', $this->nullableUpper($row['allocation_customer'] ?? null))
                ->where('allocation_location', $this->nullableUpper($row['allocation_location'] ?? null))
                ->where('allocation_type_unit', $this->nullableUpper($row['allocation_type_unit'] ?? null))
                ->where('allocation_sn_unit', $this->nullableUpper($row['allocation_sn_unit'] ?? null))
                ->exists();
        })->count();

        return [
            'total_rows' => count($rows),
            'total_qty' => collect($rows)->sum('qty_masuk'),
            'unique_parts' => $partNumbers->count(),
            'existing_parts' => $existingParts->count(),
            'new_parts' => $partNumbers->diff($existingParts)->count(),
            'unique_locations' => $locationCodes->count(),
            'existing_locations' => $existingLocations->count(),
            'new_locations' => $locationCodes->diff($existingLocations)->count(),
            'merge_stock_rows' => $mergeStockRows,
            'new_stock_rows' => count($rows) - $mergeStockRows,
        ];
    }

    private function importRow(array $row, $user): void
    {
        $partNumber = $this->upper($row['part_number']);
        $partName = trim((string) $row['part_name']);
        $locationCode = $this->upper($row['location_code']);
        $locationName = trim((string) ($row['location_name'] ?? '')) ?: $locationCode;
        $qtyMasuk = (int) $row['qty_masuk'];

        $item = RentalSparepartItem::query()->firstOrNew([
            'department' => self::DEPARTMENT,
            'part_number' => $partNumber,
        ]);

        $item->part_name = $partName;
        $item->default_type_unit = $this->nullableUpper($row['default_type_unit'] ?? null);
        $item->min_stock = (int) ($row['min_stock'] ?? $item->min_stock ?? 0);
        $item->save();

        $location = RentalSparepartLocation::query()->firstOrNew([
            'department' => self::DEPARTMENT,
            'location_code' => $locationCode,
        ]);

        $location->location_name = $locationName;
        $location->cabinet = $this->nullableUpper($row['cabinet'] ?? null);
        $location->shelf = $this->nullableUpper($row['shelf'] ?? null);
        $location->box = $this->nullableUpper($row['box'] ?? null);
        $location->save();

        $stock = RentalSparepartStock::query()->firstOrNew([
            'department' => self::DEPARTMENT,
            'sparepart_item_id' => $item->id,
            'location_id' => $location->id,
            'source_no_job' => $this->nullableUpper($row['no_job'] ?? null),
            'source_customer' => $this->nullableUpper($row['source_customer'] ?? null),
            'source_location' => $this->nullableUpper($row['source_location'] ?? null),
            'source_type_unit' => $this->nullableUpper($row['source_type_unit'] ?? null),
            'source_sn_unit' => $this->nullableUpper($row['source_sn_unit'] ?? null),
            'allocation_customer' => $this->nullableUpper($row['allocation_customer'] ?? null),
            'allocation_location' => $this->nullableUpper($row['allocation_location'] ?? null),
            'allocation_type_unit' => $this->nullableUpper($row['allocation_type_unit'] ?? null),
            'allocation_sn_unit' => $this->nullableUpper($row['allocation_sn_unit'] ?? null),
        ]);

        $stock->qty_on_hand = (int) ($stock->qty_on_hand ?? 0) + $qtyMasuk;
        $stock->qty_reserved = (int) ($stock->qty_reserved ?? 0);
        $stock->remarks = trim((string) ($row['remarks'] ?? '')) ?: $stock->remarks;
        $stock->save();

        RentalSparepartMovement::create([
            'department' => self::DEPARTMENT,
            'movement_type' => RentalSparepartMovement::TYPE_IN,
            'movement_date' => $row['tanggal'],
            'sparepart_item_id' => $item->id,
            'sparepart_stock_id' => $stock->id,
            'from_location_id' => null,
            'to_location_id' => $location->id,
            'part_number_snapshot' => $item->part_number,
            'part_name_snapshot' => $item->part_name,
            'qty' => $qtyMasuk,
            'no_job' => $this->nullableUpper($row['no_job'] ?? null),
            'source_customer' => $stock->source_customer,
            'source_location' => $stock->source_location,
            'source_type_unit' => $stock->source_type_unit,
            'source_sn_unit' => $stock->source_sn_unit,
            'allocation_customer' => $stock->allocation_customer,
            'allocation_location' => $stock->allocation_location,
            'allocation_type_unit' => $stock->allocation_type_unit,
            'allocation_sn_unit' => $stock->allocation_sn_unit,
            'pic_user_id' => $user->id,
            'pic_name' => $user->name,
            'remarks' => trim((string) ($row['remarks'] ?? '')) ?: null,
        ]);
    }

    private function parseCsv(string $filePath): array
    {
        $errors = [];
        $rows = [];
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            return ['rows' => [], 'errors' => ['File CSV tidak bisa dibaca.']];
        }

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            return ['rows' => [], 'errors' => ['Header CSV tidak ditemukan.']];
        }

        $header = array_map(fn ($value) => $this->normalizeHeader($value), $header);
        $missingHeaders = array_diff(self::REQUIRED_HEADERS, $header);

        if (!empty($missingHeaders)) {
            fclose($handle);
            return ['rows' => [], 'errors' => ['Header wajib tidak ada: ' . implode(', ', $missingHeaders)]];
        }

        $lineNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $row = [];

            foreach ($header as $index => $key) {
                $row[$key] = trim((string) ($data[$index] ?? ''));
            }

            $rowErrors = $this->validateRow($row, $lineNumber);

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
                continue;
            }

            $rows[] = $this->normalizeRow($row);
        }

        fclose($handle);

        return ['rows' => $rows, 'errors' => $errors];
    }

    private function validateRow(array $row, int $lineNumber): array
    {
        $errors = [];

        if (blank($row['tanggal'] ?? null) || !strtotime((string) $row['tanggal'])) {
            $errors[] = "Baris {$lineNumber}: tanggal wajib valid.";
        }

        if (blank($row['part_number'] ?? null)) {
            $errors[] = "Baris {$lineNumber}: part_number wajib diisi.";
        }

        if (blank($row['part_name'] ?? null)) {
            $errors[] = "Baris {$lineNumber}: part_name wajib diisi.";
        }

        if (blank($row['qty_masuk'] ?? null) || !is_numeric($row['qty_masuk']) || (int) $row['qty_masuk'] <= 0) {
            $errors[] = "Baris {$lineNumber}: qty_masuk wajib angka lebih dari 0.";
        }

        if (blank($row['location_code'] ?? null)) {
            $errors[] = "Baris {$lineNumber}: location_code wajib diisi.";
        }

        if (isset($row['min_stock']) && $row['min_stock'] !== '' && (!is_numeric($row['min_stock']) || (int) $row['min_stock'] < 0)) {
            $errors[] = "Baris {$lineNumber}: min_stock harus angka minimal 0.";
        }

        return $errors;
    }

    private function normalizeRow(array $row): array
    {
        $row['tanggal'] = date('Y-m-d', strtotime($row['tanggal']));
        $row['qty_masuk'] = (int) $row['qty_masuk'];
        $row['min_stock'] = isset($row['min_stock']) && $row['min_stock'] !== '' ? (int) $row['min_stock'] : 0;

        return $row;
    }

    private function isEmptyRow(array $row): bool
    {
        return collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function normalizeHeader(?string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
        $value = strtolower(trim($value));
        $value = str_replace([' ', '-', '.'], '_', $value);

        return $value;
    }

    private function upper(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function nullableUpper(?string $value): ?string
    {
        $value = $this->upper($value);

        return $value !== '' ? $value : null;
    }

    private function csvCell($value): string
    {
        $value = (string) $value;
        $value = str_replace('"', '""', $value);

        return '"' . $value . '"';
    }

    private function canManage(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->status_user ?? $user->role ?? ''));
        $department = strtoupper(trim((string) ($user->department ?? '')));

        return in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)
            && (in_array($role, ['admin', 'super_admin'], true) || $department === self::DEPARTMENT);
    }
}
