<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceBillPayment extends Model
{
    protected $fillable = ['bill_id', 'amount', 'paid_at', 'finance_transaction_id', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'date'];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(FinanceRecurringBill::class, 'bill_id');
    }
}
