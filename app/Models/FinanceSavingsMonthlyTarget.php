<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceSavingsMonthlyTarget extends Model
{
    protected $fillable = ['savings_goal_id', 'year', 'month', 'target_amount'];

    protected function casts(): array
    {
        return ['target_amount' => 'decimal:2'];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(FinanceSavingsGoal::class, 'savings_goal_id');
    }
}
