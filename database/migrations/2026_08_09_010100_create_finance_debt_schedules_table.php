<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_debt_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('finance_debts')->cascadeOnDelete();
            $table->date('due_date')->index();
            $table->decimal('amount', 18, 2);
            $table->enum('status', ['upcoming', 'paid', 'cancelled'])->default('upcoming');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_debt_schedules');
    }
};
