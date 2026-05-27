<?php
// database/migrations/2026_05_27_100001_create_batteries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batteries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Informasi Teknisi
            $table->string('branch')->nullable();
            $table->string('status_mekanik')->nullable();
            $table->string('pic')->nullable();
            $table->string('partner')->nullable();

            // Kendaraan & Waktu
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->string('vehicle')->nullable();
            $table->string('nopol')->nullable();
            $table->date('date')->nullable(); // Tanggal Pekerjaan

            // Unit Info
            $table->string('customer')->index();
            $table->string('location')->index();
            $table->string('unit_type')->nullable();
            $table->string('serial_number')->index();

            // Battery Info (SPESIFIK MODUL INI)
            $table->string('sn_battery')->nullable();
            $table->string('battery_type')->nullable();
            $table->integer('battery_year')->nullable();

            // Job Type & Status
            $table->string('category_job')->nullable(); // Tarik, Cek, Kirim
            $table->string('job_type')->nullable(); // Multi string (comma separated)
            $table->string('status_unit')->nullable(); // RFU, BREAKDOWN, MONITORING

            // Problem & Action
            $table->date('problem_date')->nullable();
            $table->date('rfu_date')->nullable();
            $table->text('problem')->nullable();
            $table->text('action')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batteries');
    }
};
