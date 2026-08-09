<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceDebtPayment extends Model
{
    protected $fillable = ['debt_id', 'schedule_id', 'amount', 'payment_date', 'finance_transaction_id', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'payment_date' => 'date'];
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(FinanceDebt::class, 'debt_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(FinanceDebtSchedule::class, 'schedule_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class);
    }
}
