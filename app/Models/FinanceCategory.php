<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceCategory extends Model
{
    protected $fillable = ['name', 'type', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'category_id');
    }

    public function recurringBills(): HasMany
    {
        return $this->hasMany(FinanceRecurringBill::class, 'category_id');
    }

    public function isInUse(): bool
    {
        return $this->transactions()->exists() || $this->recurringBills()->exists();
    }
}
