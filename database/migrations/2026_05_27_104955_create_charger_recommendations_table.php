<?php
// database/migrations/2026_05_28_000002_create_charger_recommendations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charger_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charger_id')->constrained('chargers')->cascadeOnDelete();
            $table->string('part_number')->nullable();
            $table->string('part_name');
            $table->integer('qty')->default(1);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charger_recommendations');
    }
};
