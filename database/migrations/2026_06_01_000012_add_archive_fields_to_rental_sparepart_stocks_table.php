<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000012_add_archive_fields_to_rental_sparepart_stocks_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_sparepart_stocks', function (Blueprint $table) {
            $table->string('stock_lifecycle_status', 50)->default('ACTIVE')->after('department')->index();
            $table->unsignedInteger('archived_qty_on_hand')->default(0)->after('qty_reserved');
            $table->foreignId('archived_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            $table->string('archived_by_name', 150)->nullable()->after('archived_by');
            $table->timestamp('archived_at')->nullable()->after('archived_by_name');
            $table->text('archive_note')->nullable()->after('archived_at');
            $table->foreignId('restored_by')->nullable()->after('archive_note')->constrained('users')->nullOnDelete();
            $table->string('restored_by_name', 150)->nullable()->after('restored_by');
            $table->timestamp('restored_at')->nullable()->after('restored_by_name');
            $table->text('restore_note')->nullable()->after('restored_at');

            $table->index(['department', 'stock_lifecycle_status'], 'rsp_stocks_dept_lifecycle_index');
        });
    }

    public function down(): void
    {
        Schema::table('rental_sparepart_stocks', function (Blueprint $table) {
            $table->dropIndex('rsp_stocks_dept_lifecycle_index');
            $table->dropColumn('restore_note');
            $table->dropColumn('restored_at');
            $table->dropColumn('restored_by_name');
            $table->dropConstrainedForeignId('restored_by');
            $table->dropColumn('archive_note');
            $table->dropColumn('archived_at');
            $table->dropColumn('archived_by_name');
            $table->dropConstrainedForeignId('archived_by');
            $table->dropColumn('archived_qty_on_hand');
            $table->dropColumn('stock_lifecycle_status');
        });
    }
};
