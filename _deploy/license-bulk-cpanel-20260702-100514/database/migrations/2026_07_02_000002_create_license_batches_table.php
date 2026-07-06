<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_batches', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->foreignId('subscription_package_id')->nullable()->constrained('subscription_packages')->nullOnDelete();
            $table->unsignedInteger('duration_months')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('processed_users')->default(0);
            $table->string('status')->default('completed');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_batches');
    }
};
