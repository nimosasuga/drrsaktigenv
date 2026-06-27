<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('update_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('update_jobs', 'battery_type')) {
                $table->string('battery_type', 100)->nullable()->after('hour_meter');
            }

            if (!Schema::hasColumn('update_jobs', 'battery_brand')) {
                $table->string('battery_brand', 100)->nullable()->after('battery_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('update_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('update_jobs', 'battery_brand')) {
                $table->dropColumn('battery_brand');
            }

            if (Schema::hasColumn('update_jobs', 'battery_type')) {
                $table->dropColumn('battery_type');
            }
        });
    }
};
