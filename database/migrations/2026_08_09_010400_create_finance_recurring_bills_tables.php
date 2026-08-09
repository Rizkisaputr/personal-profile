<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_recurring_bills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('finance_categories')->nullOnDelete();
            $table->decimal('amount', 18, 2);
            $table->enum('frequency', ['monthly', 'yearly', 'custom']);
            $table->unsignedTinyInteger('due_day')->nullable();
            $table->unsignedSmallInteger('custom_interval_days')->nullable();
            $table->date('next_due_date')->index();
            $table->foreignId('default_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('finance_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('finance_recurring_bills')->cascadeOnDelete();
            $table->decimal('amount', 18, 2);
            $table->date('paid_at');
            $table->foreignId('finance_transaction_id')->constrained('finance_transactions')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_bill_payments');
        Schema::dropIfExists('finance_recurring_bills');
    }
};
