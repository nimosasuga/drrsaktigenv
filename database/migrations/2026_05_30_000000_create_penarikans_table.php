<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_05_30_000000_create_penarikans_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penarikans', function (Blueprint $table) {
            $table->id();
            $table->string('penarikan_code', 50)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('branch', 150)->nullable();
            $table->string('status_mekanik', 100)->nullable();
            $table->string('pic', 150)->nullable();
            $table->string('partner', 150)->nullable();
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->string('vehicle', 150)->nullable();
            $table->string('nopol', 100)->nullable();
            $table->date('date')->nullable();
            $table->string('customer', 150);
            $table->string('location', 150)->nullable();
            $table->string('serial_number', 100);
            $table->string('unit_type', 100)->nullable();
            $table->year('year')->nullable();
            $table->string('hour_meter', 100)->nullable();
            $table->string('job_type', 100)->default('TARIK UNIT');
            $table->string('status_unit', 50)->default('RFU');
            $table->string('battery_type', 150)->nullable();
            $table->string('battery_sn', 150)->nullable();
            $table->string('charger_type', 150)->nullable();
            $table->string('charger_sn', 150)->nullable();
            $table->string('trolly', 150)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['customer', 'location']);
            $table->index('serial_number');
            $table->index('status_unit');
            $table->index('date');
            $table->index('pic');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penarikans');
    }
};
