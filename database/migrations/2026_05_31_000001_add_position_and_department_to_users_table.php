<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_05_31_000001_add_position_and_department_to_users_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position', 50)->nullable()->after('branch');
            $table->string('department', 50)->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['position', 'department']);
        });
    }
};
