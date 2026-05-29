<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_05_30_020000_add_penarikan_asset_status_triggers.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_penarikans_after_insert_status');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_penarikans_after_update_status');

        DB::unprepared("\n            CREATE TRIGGER trg_penarikans_after_insert_status\n            AFTER INSERT ON penarikans\n            FOR EACH ROW\n            BEGIN\n                UPDATE unit_assets\n                SET status = 'DITARIK', updated_at = NOW()\n                WHERE serial_number = NEW.serial_number;\n            END\n        ");

        DB::unprepared("\n            CREATE TRIGGER trg_penarikans_after_update_status\n            AFTER UPDATE ON penarikans\n            FOR EACH ROW\n            BEGIN\n                UPDATE unit_assets\n                SET status = 'DITARIK', updated_at = NOW()\n                WHERE serial_number = NEW.serial_number;\n            END\n        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_penarikans_after_insert_status');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_penarikans_after_update_status');
    }
};
