<?php
// Buka file database/migrations/..._create_unit_assets_table.php yang sudah ada
// Lalu timpa isinya dengan ini

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_assets', function (Blueprint $table) {
            $table->id();
            $table->string('supported_by')->nullable();
            $table->string('customer')->nullable();
            $table->string('location')->nullable();
            $table->string('branch')->nullable();
            $table->string('serial_number')->unique();
            $table->string('unit_type')->nullable();
            $table->string('year')->nullable();
            $table->string('status')->nullable();
            $table->string('delivery')->nullable();
            $table->string('jenis_unit')->nullable();
            $table->text('note')->nullable();
            $table->string('qr_token')->unique()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_assets');
    }
};
