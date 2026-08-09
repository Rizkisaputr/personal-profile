<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_savings_goals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('target_amount', 18, 2);
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->enum('status', ['active', 'completed', 'paused', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('finance_savings_monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_goal_id')->constrained('finance_savings_goals')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('target_amount', 18, 2);
            $table->timestamps();
            $table->unique(['savings_goal_id', 'year', 'month'], 'savings_month_unique');
        });
        Schema::create('finance_savings_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_goal_id')->constrained('finance_savings_goals')->cascadeOnDelete();
            $table->enum('type', ['contribution', 'withdrawal']);
            $table->decimal('amount', 18, 2);
            $table->date('entry_date')->index();
            $table->foreignId('account_id')->constrained('finance_accounts')->restrictOnDelete();
            $table->foreignId('finance_transaction_id')->constrained('finance_transactions')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_savings_entries');
        Schema::dropIfExists('finance_savings_monthly_targets');
        Schema::dropIfExists('finance_savings_goals');
    }
};
