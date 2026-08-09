<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense', 'transfer', 'adjustment']);
            $table->date('transaction_date')->index();
            $table->decimal('amount', 18, 2);
            $table->foreignId('category_id')->nullable()->constrained('finance_categories')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('finance_accounts')->restrictOnDelete();
            $table->foreignId('from_account_id')->nullable()->constrained('finance_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('finance_accounts')->restrictOnDelete();
            $table->enum('adjustment_direction', ['increase', 'decrease'])->nullable();
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
