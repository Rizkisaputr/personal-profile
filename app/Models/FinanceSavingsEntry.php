<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceSavingsEntry extends Model
{
    protected $fillable = ['savings_goal_id', 'type', 'amount', 'entry_date', 'account_id', 'finance_transaction_id', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'entry_date' => 'date'];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(FinanceSavingsGoal::class, 'savings_goal_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class);
    }
}
