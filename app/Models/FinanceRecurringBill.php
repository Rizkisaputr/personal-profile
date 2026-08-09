<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceRecurringBill extends Model
{
    protected $fillable = ['name', 'category_id', 'amount', 'frequency', 'due_day', 'custom_interval_days', 'next_due_date', 'default_account_id', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'next_due_date' => 'date', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class);
    }

    public function defaultAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'default_account_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FinanceBillPayment::class, 'bill_id');
    }

    public function scopeDueWithin(Builder $q, int $days): Builder
    {
        return $q->where('is_active', true)->whereBetween('next_due_date', [today(), today()->addDays($days)]);
    }

    public function scopeOverdue(Builder $q): Builder
    {
        return $q->where('is_active', true)->where('next_due_date', '<', today());
    }

    public function nextDateAfterPayment(): CarbonInterface
    {
        return match ($this->frequency) {
            'monthly' => $this->next_due_date->addMonthNoOverflow(),'yearly' => $this->next_due_date->addYear(),'custom' => $this->next_due_date->addDays($this->custom_interval_days ?: 1)
        };
    }
}
