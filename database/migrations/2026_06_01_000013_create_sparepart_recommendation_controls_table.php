<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000013_create_sparepart_recommendation_controls_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sparepart_recommendation_controls', function (Blueprint $table) {
            $table->id();
            $table->string('department', 50)->index();

            $table->foreignId('job_id')->nullable()->constrained('update_jobs')->nullOnDelete();
            $table->foreignId('job_recommendation_id')->nullable()->constrained('job_recommendations')->nullOnDelete();
            $table->foreignId('source_stock_id')->nullable()->constrained('rental_sparepart_stocks')->nullOnDelete();
            $table->foreignId('installed_job_id')->nullable()->constrained('update_jobs')->nullOnDelete();

            $table->date('work_date')->nullable()->index();
            $table->string('serial_number', 150)->nullable()->index();
            $table->string('customer', 180)->nullable()->index();
            $table->string('location', 180)->nullable()->index();
            $table->string('unit_type', 150)->nullable();

            $table->string('part_number', 180)->nullable()->index();
            $table->string('part_name', 255)->nullable();
            $table->unsignedInteger('qty_recommended')->default(1);
            $table->unsignedInteger('qty_supplied')->default(0);
            $table->unsignedInteger('qty_installed')->default(0);

            $table->string('recommendation_status', 50)->default('RECOMMENDED')->index();
            $table->string('supply_status', 50)->default('NOT_SUPPLIED')->index();
            $table->string('source_type', 80)->nullable()->index();
            $table->boolean('is_cross_allocation')->default(false)->index();

            $table->foreignId('recommended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recommended_by_name', 180)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reviewed_by_name', 180)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('supplied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('supplied_by_name', 180)->nullable();
            $table->timestamp('supplied_at')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->text('remarks')->nullable();
            $table->text('review_note')->nullable();
            $table->text('supply_note')->nullable();
            $table->timestamps();

            $table->index(['department', 'serial_number'], 'src_dept_sn_index');
            $table->index(['department', 'part_number'], 'src_dept_part_index');
            $table->index(['department', 'recommendation_status'], 'src_dept_rec_status_index');
            $table->index(['department', 'supply_status'], 'src_dept_supply_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sparepart_recommendation_controls');
    }
};
