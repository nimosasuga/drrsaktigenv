<?php
// database/migrations/2026_05_28_000003_create_charger_install_parts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charger_install_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charger_id')->constrained('chargers')->cascadeOnDelete();
            $table->string('part_number')->nullable();
            $table->string('part_name');
            $table->integer('qty')->default(1);
            $table->text('remarks')->nullable();
            $table->string('no_job')->nullable();
            $table->string('no_pr')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charger_install_parts');
    }
};
