<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000000_create_pikets_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pikets', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // Tanggal piket (nantinya divalidasi harus Sabtu)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Mekanik yang piket
            $table->enum('status', ['jalan', 'berhalangan'])->default('jalan');
            $table->string('department')->default('RENTAL'); // Untuk filter isolasi data
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Pembuat jadwal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pikets');
    }
};
