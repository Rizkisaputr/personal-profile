<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->decimal('cost', 18, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly', 'custom']);
            $table->unsignedInteger('custom_interval_days')->nullable();
            $table->date('start_date')->nullable();
            $table->date('next_renewal_date')->index();
            $table->foreignId('payment_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 18, 2);
            $table->date('paid_at')->index();
            $table->foreignId('finance_transaction_id')->constrained('finance_transactions')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
    }
};
