<?php
// Buka file database/migrations/..._create_payments_table.php yang baru dibuat

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subscription_package_id')->constrained('subscription_packages')->onDelete('cascade');
            $table->foreignId('user_subscription_id')->nullable()->constrained('user_subscriptions')->onDelete('set null');

            $table->string('payment_method')->default('GOPAY');
            $table->string('receiver_name')->default('PT EXPROSA GLOBAL NUSANTARA');
            $table->string('receiver_number')->default('082177212271');
            $table->integer('amount');

            $table->enum('payment_status', ['waiting_payment', 'waiting_verification', 'paid', 'rejected', 'failed'])->default('waiting_payment');
            $table->timestamp('paid_at')->nullable();

            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();

            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
