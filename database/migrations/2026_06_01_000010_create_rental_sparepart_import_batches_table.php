<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000010_create_rental_sparepart_import_batches_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_sparepart_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 80)->unique();
            $table->string('department', 50)->default('RENTAL')->index();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('imported_by_name', 150)->nullable();
            $table->string('status', 50)->default('IMPORTED')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('total_qty')->default(0);
            $table->unsignedInteger('unique_parts')->default(0);
            $table->unsignedInteger('existing_parts')->default(0);
            $table->unsignedInteger('new_parts')->default(0);
            $table->unsignedInteger('unique_locations')->default(0);
            $table->unsignedInteger('existing_locations')->default(0);
            $table->unsignedInteger('new_locations')->default(0);
            $table->unsignedInteger('merge_stock_rows')->default(0);
            $table->unsignedInteger('new_stock_rows')->default(0);
            $table->json('summary_json')->nullable();
            $table->timestamps();

            $table->index(['department', 'status'], 'rsp_import_batches_dept_status_index');
        });

        Schema::table('rental_sparepart_movements', function (Blueprint $table) {
            $table->foreignId('import_batch_id')
                ->nullable()
                ->after('department')
                ->constrained('rental_sparepart_import_batches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rental_sparepart_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_batch_id');
        });

        Schema::dropIfExists('rental_sparepart_import_batches');
    }
};
