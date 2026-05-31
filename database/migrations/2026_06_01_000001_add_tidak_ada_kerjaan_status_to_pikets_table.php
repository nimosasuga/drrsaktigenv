<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000001_add_tidak_ada_kerjaan_status_to_pikets_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE pikets MODIFY status ENUM('jalan', 'berhalangan', 'tidak_ada_kerjaan') NOT NULL DEFAULT 'jalan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('pikets')
            ->where('status', 'tidak_ada_kerjaan')
            ->update(['status' => 'berhalangan']);

        DB::statement("ALTER TABLE pikets MODIFY status ENUM('jalan', 'berhalangan') NOT NULL DEFAULT 'jalan'");
    }
};
