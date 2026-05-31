<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000002_add_customer_locations_to_rental_spareparts.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rental_sparepart_stocks')) {
            Schema::table('rental_sparepart_stocks', function (Blueprint $table) {
                if (!Schema::hasColumn('rental_sparepart_stocks', 'source_location')) {
                    $table->string('source_location', 150)->nullable()->after('source_customer')->index();
                }

                if (!Schema::hasColumn('rental_sparepart_stocks', 'allocation_location')) {
                    $table->string('allocation_location', 150)->nullable()->after('allocation_customer')->index();
                }
            });
        }

        if (Schema::hasTable('rental_sparepart_movements')) {
            Schema::table('rental_sparepart_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('rental_sparepart_movements', 'source_location')) {
                    $table->string('source_location', 150)->nullable()->after('source_customer');
                }

                if (!Schema::hasColumn('rental_sparepart_movements', 'allocation_location')) {
                    $table->string('allocation_location', 150)->nullable()->after('allocation_customer');
                }

                if (!Schema::hasColumn('rental_sparepart_movements', 'actual_location')) {
                    $table->string('actual_location', 150)->nullable()->after('actual_customer');
                }
            });
        }

        if (Schema::hasTable('rental_sparepart_usage_reviews')) {
            Schema::table('rental_sparepart_usage_reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('rental_sparepart_usage_reviews', 'original_allocation_location')) {
                    $table->string('original_allocation_location', 150)->nullable()->after('original_allocation_customer');
                }

                if (!Schema::hasColumn('rental_sparepart_usage_reviews', 'actual_location')) {
                    $table->string('actual_location', 150)->nullable()->after('actual_customer');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rental_sparepart_usage_reviews')) {
            Schema::table('rental_sparepart_usage_reviews', function (Blueprint $table) {
                if (Schema::hasColumn('rental_sparepart_usage_reviews', 'actual_location')) {
                    $table->dropColumn('actual_location');
                }

                if (Schema::hasColumn('rental_sparepart_usage_reviews', 'original_allocation_location')) {
                    $table->dropColumn('original_allocation_location');
                }
            });
        }

        if (Schema::hasTable('rental_sparepart_movements')) {
            Schema::table('rental_sparepart_movements', function (Blueprint $table) {
                foreach (['actual_location', 'allocation_location', 'source_location'] as $column) {
                    if (Schema::hasColumn('rental_sparepart_movements', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('rental_sparepart_stocks')) {
            Schema::table('rental_sparepart_stocks', function (Blueprint $table) {
                foreach (['allocation_location', 'source_location'] as $column) {
                    if (Schema::hasColumn('rental_sparepart_stocks', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
