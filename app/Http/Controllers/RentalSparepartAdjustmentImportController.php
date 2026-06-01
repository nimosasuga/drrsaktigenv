<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/RentalSparepartAdjustmentImportController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\RentalSparepartMovement;
use App\Models\RentalSparepartStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class RentalSparepartAdjustmentImportController extends Controller
{
    private const DEPARTMENT = 'RENTAL';
    private const SESSION_KEY = 'rental_sparepart_adjustment_import_rows';

    private const REQUIRED_HEADERS = [
        'tanggal',
        'stock_id',
        'adjustment_type',
        'qty',
    ];

    public function create()
    {
        abort_unless($this->canManage(), 403);

        return view('rental-spareparts.adjustments.create');
    }

    public function template()
    {
        abort_unless($this->canManage(), 403);

        $rows = [
            ['tanggal', 'stock_id', 'adjustment_type', 'qty', 'expected_qty_on_hand', 'remarks'],
            [now()->toDateString(), '1', 'SET', '10', '8', 'KOREKSI HASIL STOCK OPNAME'],
            [now()->toDateString(), '2', 'ADD', '3', '', 'TAMBAH HASIL STOCK OPNAME'],
            [now()->toDateString(), '3', 'SUBTRACT', '1', '', 'KURANGI HASIL STOCK OPNAME'],
        ];

        $csv = "\xEF\xBB\xBF" . collect($rows)->map(function ($row) {
            return collect($row)->map(fn ($value) => $this->csvCell($value))->implode(',');
        })->implode("\n");

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rental_sparepart_adjustment_template.csv"',
        ]);
    }

    public function preview(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $parsed = $this->parseCsv($request->file('csv_file')->getRealPath());

        if (!empty($parsed['errors'])) {
            session()->forget(self::SESSION_KEY);

            return back()->withErrors(['csv_file' => implode(' | ', array_slice($parsed['errors'], 0, 10))]);
        }

        $rows = $parsed['rows'];

        if (count($rows) === 0) {
            session()->forget(self::SESSION_KEY);

            return back()->withErrors(['csv_file' => 'CSV adjustment kosong. Minimal harus ada 1 baris data.']);
        }

        session()->put(self::SESSION_KEY, $rows);

        $summary = $this->summary($rows);
        $previewRows = array_slice($rows, 0, 30);

        return view('rental-spareparts.adjustments.preview', compact('summary', 'previewRows'));
    }

    public function confirm()
    {
        abort_unless($this->canManage(), 403);

        $rows = session(self::SESSION_KEY, []);

        if (empty($rows)) {
            return redirect()
                ->route('rental-spareparts.adjustments.create')
                ->withErrors(['csv_file' => 'Preview adjustment tidak ditemukan atau sudah kedaluwarsa. Upload ulang CSV.']);
        }

        $recheck = $this->validateParsedRows($rows, true);

        if (!empty($recheck)) {
            return back()->withErrors(['csv_file' => implode(' | ', array_slice($recheck, 0, 10))]);
        }

        try {
            DB::transaction(function () use ($rows) {
                $user = Auth::user();

                foreach ($rows as $row) {
                    $stock = RentalSparepartStock::query()
                        ->with(['item', 'location'])
                        ->where('department', self::DEPARTMENT)
                        ->whereKey((int) $row['stock_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $oldQty = (int) $stock->qty_on_hand;
                    $newQty = $this->newQty($oldQty, (string) $row['adjustment_type'], (int) $row['qty']);
                    $difference = $newQty - $oldQty;

                    if ($difference === 0) {
                        continue;
                    }

                    $stock->qty_on_hand = $newQty;
                    $stock->save();

                    RentalSparepartMovement::create([
                        'department' => self::DEPARTMENT,
                        'movement_type' => RentalSparepartMovement::TYPE_ADJUSTMENT,
                        'movement_date' => $row['tanggal'],
                        'sparepart_item_id' => $stock->sparepart_item_id,
                        'sparepart_stock_id' => $stock->id,
                        'from_location_id' => $difference < 0 ? $stock->location_id : null,
                        'to_location_id' => $difference > 0 ? $stock->location_id : null,
                        'part_number_snapshot' => $stock->item?->part_number,
                        'part_name_snapshot' => $stock->item?->part_name,
                        'qty' => abs($difference),
                        'no_job' => $stock->source_no_job,
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
                        'remarks' => 'IMPORT CORRECTION ADJUSTMENT: ' . $oldQty . ' -> ' . $newQty . '. ' . ($row['remarks'] ?? ''),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['csv_file' => 'Adjustment gagal. Tidak ada data yang disimpan. Error: ' . $exception->getMessage()]);
        }

        session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('rental-spareparts.index')
            ->with('success', count($rows) . ' baris correction/adjustment berhasil diproses.');
    }

    public function cancel()
    {
        abort_unless($this->canManage(), 403);

        session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('rental-spareparts.adjustments.create')
            ->with('success', 'Preview adjustment dibatalkan. Tidak ada data yang disimpan.');
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

            $row['line_number'] = $lineNumber;
            $rows[] = $this->normalizeRow($row);
        }

        fclose($handle);

        $errors = $this->validateParsedRows($rows);

        return ['rows' => $rows, 'errors' => $errors];
    }

    private function validateParsedRows(array $rows, bool $strictExpectedQty = false): array
    {
        $errors = [];

        foreach ($rows as $row) {
            $lineNumber = (int) ($row['line_number'] ?? 0);
            $prefix = $lineNumber > 0 ? "Baris {$lineNumber}: " : '';

            if (blank($row['tanggal'] ?? null) || !strtotime((string) $row['tanggal'])) {
                $errors[] = $prefix . 'tanggal wajib valid.';
                continue;
            }

            if (blank($row['stock_id'] ?? null) || !is_numeric($row['stock_id'])) {
                $errors[] = $prefix . 'stock_id wajib angka.';
                continue;
            }

            $stock = RentalSparepartStock::query()
                ->where('department', self::DEPARTMENT)
                ->whereKey((int) $row['stock_id'])
                ->first();

            if (!$stock) {
                $errors[] = $prefix . 'stock_id tidak ditemukan di department RENTAL.';
                continue;
            }

            $type = strtoupper(trim((string) ($row['adjustment_type'] ?? '')));

            if (!in_array($type, ['SET', 'ADD', 'SUBTRACT'], true)) {
                $errors[] = $prefix . 'adjustment_type hanya boleh SET, ADD, atau SUBTRACT.';
                continue;
            }

            if (!is_numeric($row['qty'] ?? null) || (int) $row['qty'] < 0) {
                $errors[] = $prefix . 'qty wajib angka minimal 0.';
                continue;
            }

            if (in_array($type, ['ADD', 'SUBTRACT'], true) && (int) $row['qty'] <= 0) {
                $errors[] = $prefix . 'qty untuk ADD/SUBTRACT wajib lebih dari 0.';
                continue;
            }

            if (($row['expected_qty_on_hand'] ?? '') !== '' && (!is_numeric($row['expected_qty_on_hand']) || (int) $row['expected_qty_on_hand'] < 0)) {
                $errors[] = $prefix . 'expected_qty_on_hand harus angka minimal 0.';
                continue;
            }

            if (($row['expected_qty_on_hand'] ?? '') !== '' && (int) $row['expected_qty_on_hand'] !== (int) $stock->qty_on_hand) {
                $errors[] = $prefix . 'expected_qty_on_hand tidak sama dengan qty_on_hand saat ini. File kemungkinan sudah basi.';
                continue;
            }

            if ($strictExpectedQty && ($row['expected_qty_on_hand'] ?? '') === '') {
                // tetap boleh kosong; strict hanya memaksa validasi ulang bila kolom diisi
            }

            $newQty = $this->newQty((int) $stock->qty_on_hand, $type, (int) $row['qty']);

            if ($newQty < 0) {
                $errors[] = $prefix . 'hasil adjustment membuat qty minus.';
                continue;
            }

            if ($newQty < (int) $stock->qty_reserved) {
                $errors[] = $prefix . 'hasil adjustment lebih kecil dari qty_reserved.';
                continue;
            }
        }

        return $errors;
    }

    private function summary(array $rows): array
    {
        $increase = 0;
        $decrease = 0;
        $set = 0;
        $add = 0;
        $subtract = 0;

        foreach ($rows as $row) {
            $stock = RentalSparepartStock::find((int) $row['stock_id']);

            if (!$stock) {
                continue;
            }

            $oldQty = (int) $stock->qty_on_hand;
            $newQty = $this->newQty($oldQty, (string) $row['adjustment_type'], (int) $row['qty']);
            $diff = $newQty - $oldQty;

            if ($diff > 0) {
                $increase += $diff;
            }

            if ($diff < 0) {
                $decrease += abs($diff);
            }

            match ((string) $row['adjustment_type']) {
                'SET' => $set++,
                'ADD' => $add++,
                'SUBTRACT' => $subtract++,
                default => null,
            };
        }

        return [
            'total_rows' => count($rows),
            'set' => $set,
            'add' => $add,
            'subtract' => $subtract,
            'total_increase' => $increase,
            'total_decrease' => $decrease,
        ];
    }

    private function newQty(int $oldQty, string $type, int $qty): int
    {
        return match (strtoupper(trim($type))) {
            'SET' => $qty,
            'ADD' => $oldQty + $qty,
            'SUBTRACT' => $oldQty - $qty,
            default => $oldQty,
        };
    }

    private function normalizeRow(array $row): array
    {
        $row['tanggal'] = date('Y-m-d', strtotime((string) $row['tanggal']));
        $row['stock_id'] = (int) $row['stock_id'];
        $row['adjustment_type'] = strtoupper(trim((string) $row['adjustment_type']));
        $row['qty'] = (int) $row['qty'];
        $row['expected_qty_on_hand'] = trim((string) ($row['expected_qty_on_hand'] ?? ''));
        $row['remarks'] = trim((string) ($row['remarks'] ?? ''));

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
