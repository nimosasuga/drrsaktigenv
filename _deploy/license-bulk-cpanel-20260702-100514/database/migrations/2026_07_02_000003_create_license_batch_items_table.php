<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_batch_id')->constrained('license_batches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_subscription_id')->nullable()->constrained('user_subscriptions')->nullOnDelete();
            $table->foreignId('previous_subscription_package_id')->nullable()->constrained('subscription_packages')->nullOnDelete();
            $table->foreignId('new_subscription_package_id')->nullable()->constrained('subscription_packages')->nullOnDelete();
            $table->string('action');
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->timestamp('previous_started_at')->nullable();
            $table->timestamp('previous_expired_at')->nullable();
            $table->timestamp('new_started_at')->nullable();
            $table->timestamp('new_expired_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_batch_items');
    }
};
