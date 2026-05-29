<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_05_30_010000_add_extra_battery_and_trolly_to_penarikans_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penarikans', function (Blueprint $table) {
            $table->string('battery_type_2', 150)->nullable()->after('battery_sn');
            $table->string('battery_sn_2', 150)->nullable()->after('battery_type_2');
            $table->string('trolly_2', 150)->nullable()->after('trolly');
            $table->string('trolly_3', 150)->nullable()->after('trolly_2');
        });
    }

    public function down(): void
    {
        Schema::table('penarikans', function (Blueprint $table) {
            $table->dropColumn([
                'battery_type_2',
                'battery_sn_2',
                'trolly_2',
                'trolly_3',
            ]);
        });
    }
};
