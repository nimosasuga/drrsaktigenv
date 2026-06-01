<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000011_add_rollback_fields_to_rental_sparepart_import_batches_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_sparepart_import_batches', function (Blueprint $table) {
            $table->foreignId('rolled_back_by')->nullable()->after('imported_by_name')->constrained('users')->nullOnDelete();
            $table->string('rolled_back_by_name', 150)->nullable()->after('rolled_back_by');
            $table->timestamp('rolled_back_at')->nullable()->after('rolled_back_by_name');
            $table->text('rollback_note')->nullable()->after('rolled_back_at');
        });
    }

    public function down(): void
    {
        Schema::table('rental_sparepart_import_batches', function (Blueprint $table) {
            $table->dropColumn('rollback_note');
            $table->dropColumn('rolled_back_at');
            $table->dropColumn('rolled_back_by_name');
            $table->dropConstrainedForeignId('rolled_back_by');
        });
    }
};
