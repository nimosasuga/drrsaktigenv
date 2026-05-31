<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000001_create_rental_sparepart_tables.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_sparepart_items', function (Blueprint $table) {
            $table->id();
            $table->string('department', 50)->default('RENTAL')->index();
            $table->string('part_number', 150)->index();
            $table->string('part_name', 255);
            $table->string('default_type_unit', 150)->nullable();
            $table->unsignedInteger('min_stock')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['department', 'part_number'], 'rsp_items_department_part_unique');
        });

        Schema::create('rental_sparepart_locations', function (Blueprint $table) {
            $table->id();
            $table->string('department', 50)->default('RENTAL')->index();
            $table->string('location_code', 100)->index();
            $table->string('location_name', 150);
            $table->string('cabinet', 100)->nullable();
            $table->string('shelf', 100)->nullable();
            $table->string('box', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['department', 'location_code'], 'rsp_locations_department_code_unique');
        });

        Schema::create('rental_sparepart_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('department', 50)->default('RENTAL')->index();
            $table->foreignId('sparepart_item_id')->constrained('rental_sparepart_items')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('rental_sparepart_locations')->nullOnDelete();
            $table->unsignedInteger('qty_on_hand')->default(0);
            $table->unsignedInteger('qty_reserved')->default(0);

            $table->string('source_no_job', 150)->nullable()->index();
            $table->string('source_customer', 150)->nullable()->index();
            $table->string('source_type_unit', 150)->nullable();
            $table->string('source_sn_unit', 150)->nullable()->index();

            $table->string('allocation_customer', 150)->nullable()->index();
            $table->string('allocation_type_unit', 150)->nullable();
            $table->string('allocation_sn_unit', 150)->nullable()->index();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['department', 'sparepart_item_id'], 'rsp_stocks_department_item_index');
            $table->index(['department', 'source_no_job', 'allocation_sn_unit'], 'rsp_stocks_job_sn_index');
        });

        Schema::create('rental_sparepart_movements', function (Blueprint $table) {
            $table->id();
            $table->string('department', 50)->default('RENTAL')->index();
            $table->string('movement_type', 50)->index();
            $table->date('movement_date')->index();

            $table->foreignId('sparepart_item_id')->nullable()->constrained('rental_sparepart_items')->nullOnDelete();
            $table->foreignId('sparepart_stock_id')->nullable()->constrained('rental_sparepart_stocks')->nullOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('rental_sparepart_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('rental_sparepart_locations')->nullOnDelete();

            $table->string('part_number_snapshot', 150)->nullable()->index();
            $table->string('part_name_snapshot', 255)->nullable();
            $table->unsignedInteger('qty');

            $table->string('no_job', 150)->nullable()->index();

            $table->string('source_customer', 150)->nullable();
            $table->string('source_type_unit', 150)->nullable();
            $table->string('source_sn_unit', 150)->nullable()->index();

            $table->string('allocation_customer', 150)->nullable();
            $table->string('allocation_type_unit', 150)->nullable();
            $table->string('allocation_sn_unit', 150)->nullable()->index();

            $table->string('actual_customer', 150)->nullable();
            $table->string('actual_type_unit', 150)->nullable();
            $table->string('actual_sn_unit', 150)->nullable()->index();

            $table->boolean('is_cross_allocation')->default(false)->index();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pic_name', 150)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['department', 'movement_type', 'movement_date'], 'rsp_movements_dept_type_date_index');
        });

        Schema::create('rental_sparepart_usage_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('department', 50)->default('RENTAL')->index();

            $table->foreignId('job_id')->nullable()->constrained('update_jobs')->nullOnDelete();
            $table->foreignId('job_install_part_id')->nullable()->constrained('job_install_parts')->nullOnDelete();
            $table->foreignId('sparepart_stock_id')->nullable()->constrained('rental_sparepart_stocks')->nullOnDelete();
            $table->foreignId('sparepart_item_id')->nullable()->constrained('rental_sparepart_items')->nullOnDelete();
            $table->foreignId('movement_id')->nullable()->constrained('rental_sparepart_movements')->nullOnDelete();

            $table->date('work_date')->nullable()->index();
            $table->string('job_serial_number', 150)->nullable()->index();
            $table->string('job_customer', 150)->nullable();
            $table->string('job_location', 150)->nullable();

            $table->string('no_job', 150)->nullable()->index();
            $table->string('part_number', 150)->nullable()->index();
            $table->string('part_name', 255)->nullable();
            $table->unsignedInteger('qty_requested')->default(1);

            $table->string('match_type', 50)->default('NOT_FOUND')->index();
            $table->string('review_status', 50)->default('PENDING_REVIEW')->index();
            $table->boolean('is_borrowed')->default(false)->index();
            $table->string('borrow_reason', 255)->nullable();

            $table->string('original_allocation_customer', 150)->nullable();
            $table->string('original_allocation_type_unit', 150)->nullable();
            $table->string('original_allocation_sn_unit', 150)->nullable()->index();

            $table->string('actual_customer', 150)->nullable();
            $table->string('actual_type_unit', 150)->nullable();
            $table->string('actual_sn_unit', 150)->nullable()->index();

            $table->foreignId('mechanic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mechanic_name', 150)->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['department', 'review_status'], 'rsp_reviews_department_status_index');
            $table->index(['department', 'part_number', 'job_serial_number'], 'rsp_reviews_part_sn_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_sparepart_usage_reviews');
        Schema::dropIfExists('rental_sparepart_movements');
        Schema::dropIfExists('rental_sparepart_stocks');
        Schema::dropIfExists('rental_sparepart_locations');
        Schema::dropIfExists('rental_sparepart_items');
    }
};
