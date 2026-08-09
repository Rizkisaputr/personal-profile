<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceDebtSchedule extends Model
{
    protected $fillable = ['debt_id', 'due_date', 'amount', 'status', 'notes'];

    protected $appends = ['is_overdue'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(FinanceDebt::class, 'debt_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FinanceDebtPayment::class, 'schedule_id');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', today())->where('status', 'upcoming');
    }

    public function scopeDueWithin(Builder $query, int $days): Builder
    {
        return $query->whereBetween('due_date', [today(), today()->addDays($days)])
            ->where('status', 'upcoming');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'upcoming' && $this->due_date->isBefore(today());
    }
}
