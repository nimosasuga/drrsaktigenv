<?php
// database/migrations/2026_05_27_100002_create_battery_recommendations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battery_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battery_id')->constrained('batteries')->cascadeOnDelete();
            $table->string('part_number')->nullable();
            $table->string('part_name');
            $table->integer('qty')->default(1);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battery_recommendations');
    }
};
