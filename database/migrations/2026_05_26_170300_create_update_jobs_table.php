<?php
// database/migrations/2026_05_26_170300_create_update_jobs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('update_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('branch')->nullable();
            $table->string('status_mekanik')->nullable();
            $table->string('pic')->nullable();
            $table->string('partner')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('nopol')->nullable();
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->date('work_date')->nullable();

            $table->string('serial_number')->index();
            $table->string('unit_type')->nullable();
            $table->string('year', 4)->nullable();
            $table->integer('hour_meter')->nullable();
            $table->string('nomor_lambung')->nullable();

            $table->string('customer')->index();
            $table->string('location')->index();

            $table->string('job_type')->nullable();
            $table->string('status_unit')->nullable();

            $table->date('problem_date')->nullable();
            $table->date('rfu_date')->nullable();
            $table->integer('lead_time_rfu')->nullable();

            $table->string('pm')->nullable();
            $table->string('rm')->nullable();

            $table->text('problem')->nullable();
            $table->text('action')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('update_jobs');
    }
};
