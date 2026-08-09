<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_debts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['debt', 'receivable']);
            $table->string('party_name')->nullable();
            $table->decimal('original_amount', 18, 2);
            $table->date('start_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'paid', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_debts');
    }
};
