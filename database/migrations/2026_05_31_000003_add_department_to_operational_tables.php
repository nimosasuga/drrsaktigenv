<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_05_31_000003_add_department_to_operational_tables.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->tables() as $tableName => $afterColumn) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'department')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($afterColumn) {
                $column = $table->string('department', 50)->nullable()->index();

                if ($afterColumn) {
                    $column->after($afterColumn);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->tables()) as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'department')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('department');
            });
        }
    }

    private function tables(): array
    {
        return [
            'unit_assets' => 'branch',
            'update_jobs' => 'branch',
            'batteries' => 'branch',
            'chargers' => 'branch',
            'deliveries' => 'branch',
            'penarikans' => 'branch',
        ];
    }
};
