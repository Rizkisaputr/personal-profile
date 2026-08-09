<?php

namespace App\Models;

use App\Services\FinanceBalanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceDebt extends Model
{
    protected $fillable = ['name', 'type', 'party_name', 'original_amount', 'start_date', 'notes', 'status'];

    protected function casts(): array
    {
        return ['original_amount' => 'decimal:2', 'start_date' => 'date'];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(FinanceDebtSchedule::class, 'debt_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FinanceDebtPayment::class, 'debt_id');
    }

    public function paidAmount(): string
    {
        return app(FinanceBalanceService::class)->add(...$this->payments()->pluck('amount')->map(fn ($v) => (string) $v)->all());
    }

    public function remainingAmount(): string
    {
        $s = app(FinanceBalanceService::class);

        return $s->fromCents(max(0, $s->toCents((string) $this->original_amount) - $s->toCents($this->paidAmount())));
    }

    public function progress(): int
    {
        $s = app(FinanceBalanceService::class);
        $total = $s->toCents((string) $this->original_amount);

        return $total ? min(100, (int) intdiv($s->toCents($this->paidAmount()) * 100, $total)) : 0;
    }
}
