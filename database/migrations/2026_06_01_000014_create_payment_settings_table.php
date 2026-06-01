<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| database/migrations/2026_06_01_000014_create_payment_settings_table.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method', 100)->default('Transfer Manual');
            $table->string('receiver_name', 180)->nullable();
            $table->string('receiver_number', 180)->nullable();
            $table->string('admin_whatsapp', 30)->nullable();
            $table->string('qris_image_path')->nullable();
            $table->boolean('is_qris_active')->default(false);
            $table->text('payment_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
