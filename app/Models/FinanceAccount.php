<?php

namespace App\Models;

use App\Services\FinanceBalanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceAccount extends Model
{
    protected $fillable = ['name', 'type', 'opening_balance', 'currency', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['opening_balance' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'account_id');
    }

    public function currentBalance(): string
    {
        return app(FinanceBalanceService::class)->balance($this);
    }

    public function hasTransactions(): bool
    {
        return FinanceTransaction::query()->where('account_id', $this->id)
            ->orWhere('from_account_id', $this->id)->orWhere('to_account_id', $this->id)->exists();
    }
}
