<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_05_31_000002_create_work_plannings_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('mechanic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('branch', 150)->nullable();
            $table->string('department', 50)->nullable();
            $table->date('planned_date');
            $table->time('planned_time')->nullable();
            $table->string('customer', 150)->nullable();
            $table->string('location', 150)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('unit_type', 100)->nullable();
            $table->string('job_type', 150)->nullable();
            $table->string('status', 50)->default('PLANNED');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('planned_date');
            $table->index('department');
            $table->index('branch');
            $table->index('status');
            $table->index(['mechanic_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_plannings');
    }
};
