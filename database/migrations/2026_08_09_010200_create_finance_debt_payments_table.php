<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('finance_debts')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('finance_debt_schedules')->nullOnDelete();
            $table->decimal('amount', 18, 2);
            $table->date('payment_date');
            $table->foreignId('finance_transaction_id')->constrained('finance_transactions')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_debt_payments');
    }
};
