<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('drr:department-audit {--details : Tampilkan customer/lokasi yang department-nya masih kosong}', function () {
    $tables = [
        'unit_assets',
        'update_jobs',
        'batteries',
        'chargers',
        'deliveries',
        'penarikans',
        'work_plannings',
    ];

    $this->info('Audit department DRR SAKTI GEN V');
    $this->line('Mode: READ ONLY');
    $this->newLine();

    foreach ($tables as $table) {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'department')) {
            $this->warn("- {$table}: tabel/kolom department belum tersedia");
            continue;
        }

        $total = DB::table($table)->count();
        $empty = DB::table($table)
            ->where(function ($query) {
                $query->whereNull('department')->orWhere('department', '');
            })
            ->count();

        $rental = DB::table($table)->whereRaw("UPPER(TRIM(COALESCE(department, ''))) = 'RENTAL'")->count();
        $service = DB::table($table)->whereRaw("UPPER(TRIM(COALESCE(department, ''))) = 'SERVICE'")->count();

        $this->line("- {$table}: total={$total}, RENTAL={$rental}, SERVICE={$service}, KOSONG={$empty}");

        if ($this->option('details') && $empty > 0 && Schema::hasColumn($table, 'customer') && Schema::hasColumn($table, 'location')) {
            $rows = DB::table($table)
                ->select('customer', 'location', DB::raw('COUNT(*) as total'))
                ->where(function ($query) {
                    $query->whereNull('department')->orWhere('department', '');
                })
                ->groupBy('customer', 'location')
                ->orderByDesc('total')
                ->limit(15)
                ->get();

            foreach ($rows as $row) {
                $customer = $row->customer ?: 'TANPA CUSTOMER';
                $location = $row->location ?: 'TANPA LOKASI';
                $this->line("  > {$customer} / {$location}: {$row->total}");
            }
        }
    }
});

Artisan::command('drr:department-backfill-users {--commit : Jalankan update, tanpa opsi ini hanya simulasi}', function () {
    $tables = [
        'unit_assets',
        'update_jobs',
        'batteries',
        'chargers',
        'deliveries',
        'penarikans',
        'work_plannings',
    ];

    $commit = (bool) $this->option('commit');
    $this->info($commit ? 'Backfill department dari users.department' : 'DRY RUN backfill department dari users.department');
    $this->newLine();

    foreach ($tables as $table) {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'department') || !Schema::hasColumn($table, 'user_id')) {
            $this->warn("- {$table}: dilewati, tidak punya department/user_id");
            continue;
        }

        $count = DB::table($table . ' as target')
            ->join('users', 'users.id', '=', 'target.user_id')
            ->where(function ($query) {
                $query->whereNull('target.department')->orWhere('target.department', '');
            })
            ->whereNotNull('users.department')
            ->where('users.department', '!=', '')
            ->count();

        if ($commit && $count > 0) {
            DB::statement("\n                UPDATE {$table} AS target\n                INNER JOIN users ON users.id = target.user_id\n                SET target.department = UPPER(TRIM(users.department))\n                WHERE (target.department IS NULL OR target.department = '')\n                  AND users.department IS NOT NULL\n                  AND users.department != ''\n            ");
        }

        $this->line("- {$table}: " . ($commit ? 'updated' : 'akan update') . " {$count} baris");
    }
});

Artisan::command('drr:department-backfill-assets {--commit : Jalankan update, tanpa opsi ini hanya simulasi}', function () {
    $tables = [
        'update_jobs',
        'batteries',
        'chargers',
        'deliveries',
        'penarikans',
        'work_plannings',
    ];

    $commit = (bool) $this->option('commit');
    $this->info($commit ? 'Backfill department dari unit_assets.department' : 'DRY RUN backfill department dari unit_assets.department');
    $this->newLine();

    if (!Schema::hasTable('unit_assets') || !Schema::hasColumn('unit_assets', 'department')) {
        $this->error('unit_assets.department belum tersedia.');
        return self::FAILURE;
    }

    foreach ($tables as $table) {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'department')) {
            $this->warn("- {$table}: dilewati, tidak punya department");
            continue;
        }

        $updatedBySerial = 0;
        $updatedByCustomerLocation = 0;

        if (Schema::hasColumn($table, 'serial_number') && Schema::hasColumn('unit_assets', 'serial_number')) {
            $updatedBySerial = DB::table($table . ' as target')
                ->join('unit_assets as assets', 'assets.serial_number', '=', 'target.serial_number')
                ->where(function ($query) {
                    $query->whereNull('target.department')->orWhere('target.department', '');
                })
                ->whereNotNull('assets.department')
                ->where('assets.department', '!=', '')
                ->count();

            if ($commit && $updatedBySerial > 0) {
                DB::statement("\n                    UPDATE {$table} AS target\n                    INNER JOIN unit_assets AS assets ON assets.serial_number = target.serial_number\n                    SET target.department = UPPER(TRIM(assets.department))\n                    WHERE (target.department IS NULL OR target.department = '')\n                      AND assets.department IS NOT NULL\n                      AND assets.department != ''\n                ");
            }
        }

        if (Schema::hasColumn($table, 'customer') && Schema::hasColumn($table, 'location')) {
            $updatedByCustomerLocation = DB::table($table . ' as target')
                ->join('unit_assets as assets', function ($join) {
                    $join->on('assets.customer', '=', 'target.customer')
                        ->on('assets.location', '=', 'target.location');
                })
                ->where(function ($query) {
                    $query->whereNull('target.department')->orWhere('target.department', '');
                })
                ->whereNotNull('assets.department')
                ->where('assets.department', '!=', '')
                ->count();

            if ($commit && $updatedByCustomerLocation > 0) {
                DB::statement("\n                    UPDATE {$table} AS target\n                    INNER JOIN unit_assets AS assets ON assets.customer = target.customer AND assets.location = target.location\n                    SET target.department = UPPER(TRIM(assets.department))\n                    WHERE (target.department IS NULL OR target.department = '')\n                      AND assets.department IS NOT NULL\n                      AND assets.department != ''\n                ");
            }
        }

        $this->line("- {$table}: serial=" . $updatedBySerial . ', customer/lokasi=' . $updatedByCustomerLocation);
    }
});

Artisan::command('drr:department-map {department : RENTAL atau SERVICE} {--customer=} {--location=} {--serial=} {--table=* : Batasi tabel tertentu} {--commit : Jalankan update, tanpa opsi ini hanya simulasi}', function () {
    $department = strtoupper(trim((string) $this->argument('department')));

    if (!in_array($department, ['RENTAL', 'SERVICE'], true)) {
        $this->error('Department harus RENTAL atau SERVICE.');
        return self::FAILURE;
    }

    $customer = trim((string) $this->option('customer'));
    $location = trim((string) $this->option('location'));
    $serial = trim((string) $this->option('serial'));
    $selectedTables = $this->option('table') ?: [];
    $commit = (bool) $this->option('commit');

    if ($customer === '' && $location === '' && $serial === '') {
        $this->error('Isi minimal salah satu: --customer, --location, atau --serial.');
        return self::FAILURE;
    }

    $tables = $selectedTables ?: [
        'unit_assets',
        'update_jobs',
        'batteries',
        'chargers',
        'deliveries',
        'penarikans',
        'work_plannings',
    ];

    $this->info(($commit ? 'Mapping' : 'DRY RUN mapping') . " ke department {$department}");
    $this->newLine();

    foreach ($tables as $table) {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'department')) {
            $this->warn("- {$table}: dilewati, tidak punya department");
            continue;
        }

        $query = DB::table($table)
            ->where(function ($query) {
                $query->whereNull('department')->orWhere('department', '');
            });

        if ($customer !== '') {
            if (!Schema::hasColumn($table, 'customer')) {
                $this->warn("- {$table}: dilewati, tidak punya customer");
                continue;
            }
            $query->where('customer', $customer);
        }

        if ($location !== '') {
            if (!Schema::hasColumn($table, 'location')) {
                $this->warn("- {$table}: dilewati, tidak punya location");
                continue;
            }
            $query->where('location', $location);
        }

        if ($serial !== '') {
            if (!Schema::hasColumn($table, 'serial_number')) {
                $this->warn("- {$table}: dilewati, tidak punya serial_number");
                continue;
            }
            $query->where('serial_number', $serial);
        }

        $count = (clone $query)->count();

        if ($commit && $count > 0) {
            $query->update([
                'department' => $department,
                'updated_at' => now(),
            ]);
        }

        $this->line("- {$table}: " . ($commit ? 'updated' : 'akan update') . " {$count} baris");
    }
});
