<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = ['service_name', 'cost', 'billing_cycle', 'custom_interval_days', 'start_date', 'next_renewal_date', 'payment_account_id', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['cost' => 'decimal:2', 'start_date' => 'date', 'next_renewal_date' => 'date', 'is_active' => 'boolean'];
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'payment_account_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function nextDateAfterPayment(): CarbonInterface
    {
        return match ($this->billing_cycle) {
            'monthly' => $this->next_renewal_date->addMonthNoOverflow(),
            'yearly' => $this->next_renewal_date->addYear(),
            'custom' => $this->next_renewal_date->addDays($this->custom_interval_days ?: 1),
        };
    }
}
